<?php
/**
 * Reservation Action Handlers
 * Submit Review and Report a Problem AJAX handlers
 */

// ============================================================
// SUBMIT REVIEW — AJAX Handler
// ============================================================
add_action('wp_ajax_homey_child_submit_review', 'homey_child_submit_review');
function homey_child_submit_review() {
    global $current_user;
    wp_get_current_user();

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if (!wp_verify_nonce($nonce, 'homey-child-review-nonce')) {
        wp_send_json_error(['msg' => __('Security check failed. Please refresh and try again.', 'homey-child')]);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['msg' => __('You must be logged in to submit a review.', 'homey-child')]);
    }

    $current_user_id = intval($current_user->ID);
    $reservation_id  = intval($_POST['reservation_id'] ?? 0);
    $rating          = intval($_POST['rating'] ?? 0);
    $review_content  = sanitize_textarea_field($_POST['review_content'] ?? '');

    // Validation
    if (empty($reservation_id)) {
        wp_send_json_error(['msg' => __('Invalid reservation.', 'homey-child')]);
    }
    if ($rating < 1 || $rating > 5) {
        wp_send_json_error(['msg' => __('Please select a rating between 1 and 5 stars.', 'homey-child')]);
    }
    if (empty($review_content) || strlen(trim($review_content)) < 10) {
        wp_send_json_error(['msg' => __('Please write at least 10 characters for your review.', 'homey-child')]);
    }

    // Verify access
    if (!homey_give_access($reservation_id)) {
        wp_send_json_error(['msg' => __('You do not have access to this reservation.', 'homey-child')]);
    }

    $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
    $owner_id   = intval(get_post_meta($reservation_id, 'listing_owner', true));
    $renter_id  = intval(get_post_meta($reservation_id, 'listing_renter', true));
    $is_host    = ($current_user_id === $owner_id);

    // Prevent duplicate reviews
    $existing_meta_key = $is_host ? 'host_review_id_' . $reservation_id : 'review_id_' . $reservation_id;
    $existing_review   = get_post_meta($reservation_id, $existing_meta_key, true);
    if (!empty($existing_review)) {
        wp_send_json_error(['msg' => __('You have already submitted a review for this reservation.', 'homey-child')]);
    }

    // Create the review post
    $review_post = [
        'post_title'   => 'Review',
        'post_status'  => 'publish',
        'post_type'    => 'homey_review',
        'post_content' => $review_content,
        'post_author'  => $current_user_id,
    ];
    $review_id = wp_insert_post($review_post);

    if (is_wp_error($review_id) || empty($review_id)) {
        wp_send_json_error(['msg' => __('Failed to submit review. Please try again.', 'homey-child')]);
    }

    wp_update_post(['ID' => $review_id, 'post_title' => 'Review ' . $review_id]);

    update_post_meta($review_id, 'reservation_listing_id', $listing_id);
    update_post_meta($review_id, 'listing_owner_id', $owner_id);
    update_post_meta($review_id, 'reviewer_id', $current_user_id);
    update_post_meta($review_id, 'review_reservation_id', $reservation_id);
    update_post_meta($review_id, 'homey_rating', $rating);
    update_post_meta($reservation_id, $existing_meta_key, $review_id);

    if ($is_host) {
        update_post_meta($review_id, 'review_guest_id', $renter_id);
        update_post_meta($review_id, 'homey_guest_rating', $rating);
        update_post_meta($review_id, 'homey_where_to_display', 'renter_profile');
        $notify_user_id = $renter_id;
    } else {
        update_post_meta($review_id, 'homey_where_to_display', 'host_profile');
        if (function_exists('homey_add_listing_rating')) {
            homey_add_listing_rating($listing_id);
        }
        $notify_user_id = $owner_id;
    }

    if (function_exists('homey_send_review_email')) {
        homey_send_review_email($listing_id, $review_id, $rating, $review_content, $notify_user_id, $reservation_id);
    }

    // ---- Post a message in the reservation thread so both parties see the review ----
    global $wpdb;

    $reviewer_data   = get_userdata($current_user_id);
    $reviewer_name   = $reviewer_data ? $reviewer_data->display_name : 'Someone';
    $esc_reviewer    = htmlspecialchars($reviewer_name, ENT_QUOTES, 'UTF-8');
    $esc_review_text = nl2br(htmlspecialchars(trim($review_content), ENT_QUOTES, 'UTF-8'));
    $star_filled     = '&#9733;';
    $star_empty      = '&#9734;';
    $stars_html      = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars_html .= '<span style="color:' . ($i <= $rating ? '#f5a623' : '#ccc') . ';font-size:16px;">' . ($i <= $rating ? $star_filled : $star_empty) . '</span>';
    }
    $reviewed_label = $is_host
        ? __('the Renter', 'homey-child')
        : __('this Venue', 'homey-child');

    $review_message =
        '<span style="display:block;border-left:3px solid #f5a623;padding:6px 10px;margin-bottom:10px;">'
        . '<span style="display:block;font-weight:700;font-size:14px;color:#e67e22;">Review Submitted &mdash; Booking #' . intval($reservation_id) . '</span>'
        . '<span style="display:block;font-size:11px;color:#999;margin-top:2px;">'
        . $esc_reviewer . ' left a review for ' . $reviewed_label
        . '</span>'
        . '</span>'

        . '<span style="display:block;background:#fafafa;border:1px solid #eee;border-radius:6px;padding:8px 12px;margin-bottom:8px;">'
        . '<span style="display:block;margin-bottom:4px;">' . $stars_html . ' <span style="font-weight:600;color:#333;font-size:13px;margin-left:4px;">' . intval($rating) . ' / 5</span></span>'
        . '<span style="display:block;border-left:2px solid #f5a623;padding:4px 10px;color:#333;font-size:13px;line-height:1.6;">' . $esc_review_text . '</span>'
        . '</span>'

        . '<span style="display:block;font-size:11px;color:#aaa;border-top:1px solid #eee;padding-top:6px;">'
        . '&#10003; Review recorded &mdash; <em>Location Jewel</em>'
        . '</span>';

    // Find or create the thread for this reservation
    $thread_id = homey_chcek_reservation_thread($reservation_id);

    if (empty($thread_id)) {
        $receiver_id     = $is_host ? $renter_id : $owner_id;
        $threads_table   = $wpdb->prefix . 'homey_threads';
        $wpdb->insert(
            $threads_table,
            [
                'sender_id'   => $current_user_id,
                'receiver_id' => $receiver_id,
                'listing_id'  => intval($listing_id),
                'time'        => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s']
        );
        $thread_id = $wpdb->insert_id;
    }

    if (!empty($thread_id)) {
        $messages_table = $wpdb->prefix . 'homey_thread_messages';
        $wpdb->insert(
            $messages_table,
            [
                'created_by'  => $current_user_id,
                'thread_id'   => intval($thread_id),
                'message'     => $review_message,
                'attachments' => serialize([]),
                'time'        => current_time('mysql'),
            ],
            ['%d', '%d', '%s', '%s', '%s']
        );

        // Mark thread unread for the other party
        $threads_table = $wpdb->prefix . 'homey_threads';
        $wpdb->update($threads_table, ['seen' => 0], ['id' => intval($thread_id)], ['%d'], ['%d']);
    }

    wp_send_json_success(['msg' => __('Your review has been submitted successfully. Thank you!', 'homey-child')]);
}


// ============================================================
// REPORT A PROBLEM — AJAX Handler
// ============================================================
add_action('wp_ajax_homey_child_report_problem', 'homey_child_report_problem');
function homey_child_report_problem() {
    global $wpdb, $current_user;
    wp_get_current_user();

    $nonce = isset($_POST['security']) ? sanitize_text_field($_POST['security']) : '';
    if (!wp_verify_nonce($nonce, 'homey-child-report-nonce')) {
        wp_send_json_error(['msg' => __('Security check failed. Please refresh and try again.', 'homey-child')]);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['msg' => __('You must be logged in to submit a report.', 'homey-child')]);
    }

    $current_user_id = intval($current_user->ID);
    $reservation_id  = intval($_POST['reservation_id'] ?? 0);
    $user_role       = sanitize_text_field($_POST['user_role'] ?? '');
    $issue_type      = sanitize_text_field($_POST['issue_type'] ?? '');
    $description     = sanitize_textarea_field($_POST['description'] ?? '');
    $damage_list     = sanitize_text_field($_POST['damage_list'] ?? '');
    $damage_amount   = sanitize_text_field($_POST['damage_amount'] ?? '');
    $acknowledgment  = (isset($_POST['acknowledgment']) && $_POST['acknowledgment'] === '1');

    // Validation
    if (empty($reservation_id)) {
        wp_send_json_error(['msg' => __('Invalid reservation ID.', 'homey-child')]);
    }
    if (!in_array($user_role, ['host', 'renter'], true)) {
        wp_send_json_error(['msg' => __('Please select whether you are a Host or Renter.', 'homey-child')]);
    }
    if (!in_array($issue_type, ['property_damage', 'injury', 'other'], true)) {
        wp_send_json_error(['msg' => __('Please select a valid issue type.', 'homey-child')]);
    }
    if (empty($description) || strlen(trim($description)) < 10) {
        wp_send_json_error(['msg' => __('Please provide a detailed description (at least 10 characters).', 'homey-child')]);
    }
    if (!$acknowledgment) {
        wp_send_json_error(['msg' => __('You must check the acknowledgment box to submit this report.', 'homey-child')]);
    }

    if (!homey_give_access($reservation_id)) {
        wp_send_json_error(['msg' => __('You do not have access to this reservation.', 'homey-child')]);
    }

    // Get reservation details
    $listing_id  = get_post_meta($reservation_id, 'reservation_listing_id', true);
    $owner_id    = intval(get_post_meta($reservation_id, 'listing_owner', true));
    $renter_id   = intval(get_post_meta($reservation_id, 'listing_renter', true));
    $address     = get_post_meta($listing_id, 'homey_listing_full_address', true);

    $receiver_id = ($current_user_id === $owner_id) ? $renter_id : $owner_id;

    $owner_data    = get_userdata($owner_id);
    $renter_data   = get_userdata($renter_id);
    $owner_name    = $owner_data  ? $owner_data->display_name  : 'N/A';
    $renter_name   = $renter_data ? $renter_data->display_name : 'N/A';

    $issue_labels = [
        'property_damage' => 'Property Damage',
        'injury'          => 'Injury',
        'other'           => 'Other',
    ];
    $issue_label   = $issue_labels[$issue_type] ?? ucfirst($issue_type);
    $reporter_role = ($user_role === 'host') ? 'Host' : 'Renter';

    // ---- Build HTML-formatted message (inline styles only, renders inside a <p> tag) ----
    // All user-supplied fields are escaped via htmlspecialchars to prevent XSS.
    $esc_description = nl2br(htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8'));
    $esc_address     = htmlspecialchars($address ?: 'N/A', ENT_QUOTES, 'UTF-8');
    $esc_owner_name  = htmlspecialchars($owner_name, ENT_QUOTES, 'UTF-8');
    $esc_renter_name = htmlspecialchars($renter_name, ENT_QUOTES, 'UTF-8');
    $esc_damage_list = htmlspecialchars($damage_list, ENT_QUOTES, 'UTF-8');
    $esc_damage_amt  = htmlspecialchars($damage_amount, ENT_QUOTES, 'UTF-8');

    // Compact row helper — label + value on same line with a separator
    $row = function($label, $value) {
        return '<span style="display:block;padding:3px 0;font-size:13px;color:#333;">'
             . '<span style="font-weight:600;color:#555;display:inline-block;min-width:90px;">' . $label . ':</span>'
             . '<span style="color:#222;">' . $value . '</span>'
             . '</span>';
    };

    $damage_html = '';
    if (!empty($damage_list)) {
        $damage_html = '<span style="display:block;margin-top:10px;padding-top:8px;border-top:1px dashed #e0e0e0;">'
            . '<span style="display:block;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:4px;">Damage</span>'
            . $row('Item(s)', $esc_damage_list)
            . (!empty($damage_amount) ? $row('Amount', '$' . $esc_damage_amt) : '')
            . '</span>';
    }

    $message =
        // Compact header — left border accent instead of full red bar
        '<span style="display:block;border-left:3px solid #c0392b;padding:6px 10px;margin-bottom:10px;">'
        . '<span style="display:block;font-weight:700;font-size:14px;color:#c0392b;">Incident Report &mdash; Booking #' . intval($reservation_id) . '</span>'
        . '<span style="display:block;font-size:11px;color:#999;margin-top:2px;">Reported by: '
        . '<span style="background:#c0392b;color:#fff;border-radius:10px;padding:1px 8px;font-size:11px;font-weight:600;">' . esc_html($reporter_role) . '</span>'
        . ' &nbsp;|&nbsp; Issue: <strong style="color:#333;">' . esc_html($issue_label) . '</strong>'
        . '</span>'
        . '</span>'

        // Booking details block
        . '<span style="display:block;background:#fafafa;border:1px solid #eee;border-radius:6px;padding:8px 12px;margin-bottom:8px;">'
        . $row('Host', $esc_owner_name)
        . $row('Renter', $esc_renter_name)
        . $row('Address', $esc_address)
        . '</span>'

        // Description block
        . '<span style="display:block;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:4px;">Description</span>'
        . '<span style="display:block;border-left:2px solid #ddd;padding:4px 10px;color:#333;font-size:13px;line-height:1.6;margin-bottom:8px;">'
        . $esc_description
        . '</span>'

        // Damage (if any)
        . $damage_html

        // Acknowledgment
        . '<span style="display:block;margin-top:10px;font-size:11px;color:#888;border-top:1px solid #eee;padding-top:6px;">'
        . '&#10003; Reporter certified all information is true, correct, and complete. &mdash; <em>Location Jewel Incident Reporting System</em>'
        . '</span>';

    // Find existing thread or create a new one
    $thread_id = homey_chcek_reservation_thread($reservation_id);

    if (empty($thread_id)) {
        $threads_table   = $wpdb->prefix . 'homey_threads';
        $inserted_thread = $wpdb->insert(
            $threads_table,
            [
                'sender_id'   => $current_user_id,
                'receiver_id' => $receiver_id,
                'listing_id'  => intval($listing_id),
                'time'        => current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s']
        );

        if (!$inserted_thread) {
            wp_send_json_error(['msg' => __('Failed to create message thread. Please try again.', 'homey-child')]);
        }

        $thread_id = $wpdb->insert_id;
    }

    if (empty($thread_id)) {
        wp_send_json_error(['msg' => __('Unable to find or create a message thread. Please try again.', 'homey-child')]);
    }

    // Insert the incident report as a message
    $messages_table = $wpdb->prefix . 'homey_thread_messages';
    $inserted_msg   = $wpdb->insert(
        $messages_table,
        [
            'created_by'  => $current_user_id,
            'thread_id'   => intval($thread_id),
            'message'     => $message,
            'attachments' => serialize([]),
            'time'        => current_time('mysql'),
        ],
        ['%d', '%d', '%s', '%s', '%s']
    );

    if (!$inserted_msg) {
        wp_send_json_error(['msg' => __('Failed to submit report. Please try again.', 'homey-child')]);
    }

    // Mark thread as unread for the receiver
    $threads_table = $wpdb->prefix . 'homey_threads';
    $wpdb->update(
        $threads_table,
        ['seen' => 0],
        ['id'   => intval($thread_id)],
        ['%d'],
        ['%d']
    );

    // Notify the other party via email
    if (!empty($receiver_id)) {
        $receiver_data = get_user_by('id', $receiver_id);
        if ($receiver_data && !empty($receiver_data->user_email)) {
            // Plain-text version for email
            $plain_message = "INCIDENT REPORT - Booking #{$reservation_id}\n\n"
                . "Reported by: {$reporter_role}\n"
                . "Address: " . ($address ?: 'N/A') . "\n"
                . "Host: {$owner_name}\n"
                . "Renter: {$renter_name}\n\n"
                . "Issue Type: {$issue_label}\n\n"
                . "Description:\n{$description}\n";

            if (!empty($damage_list)) {
                $plain_message .= "\nDamage: {$damage_list}";
                if (!empty($damage_amount)) {
                    $plain_message .= " | Amount: \${$damage_amount}";
                }
            }

            apply_filters(
                'homey_message_email_notification',
                $thread_id,
                $plain_message,
                $receiver_data->user_email,
                $current_user_id
            );
        }
    }

    wp_send_json_success([
        'msg' => __('Your incident report has been submitted successfully. The other party has been notified.', 'homey-child'),
    ]);
}
