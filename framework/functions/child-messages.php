<?php
add_action('wp_ajax_send_overtime_policy', 'send_overtime_policy');
add_action('wp_ajax_nopriv_send_overtime_policy', 'send_overtime_policy');
function send_overtime_policy(){
    global $wpdb;

    $nonce = $_POST['start_thread_message_form_ajax'];
    if(!wp_verify_nonce($nonce, 'start-thread-message-form-nonce')){
        wp_send_json_error('Invalid nonce');
    }

    if(!empty($_POST['overtime_hours']) && !empty($_POST['price_per_hour']) && !empty($_POST['thread_id'])){

        $thread_id = intval($_POST['thread_id']); // Sanitize thread_id
        $tabel = $wpdb->prefix . 'homey_threads';
        $wpdb->update(
            $tabel,
            array(  'seen' => 0 ),
            array( 'id' => $thread_id ),
            array( '%d' ),
            array( '%d' )
        );

        $message_query = $wpdb->prepare( 
            "
            SELECT * 
            FROM $tabel 
            WHERE id = %d
            ", 
            $thread_id
        );

        $homey_thread = $wpdb->get_row( $message_query );
        if(!$homey_thread) {
            wp_send_json_error('Thread not found');
        }

        $receiver_id  = $homey_thread->receiver_id;
        $sender_id    = $homey_thread->sender_id;
        $overtime_hours = intval($_POST['overtime_hours']);
        $price_per_hour = floatval($_POST['price_per_hour']);
        
        if($overtime_hours <= 0 || $price_per_hour <= 0) {
            wp_send_json_error('Invalid overtime hours or price');
        }

        $total_amount = $price_per_hour * $overtime_hours;

        $table_name = $wpdb->prefix . 'homey_thread_messages';
        $message = "The host has submitted a fee request for additional time beyond your original booking.\n\n"
            . "Please review the charge and proceed with payment only if you agree to the extended hours. If accepted, your account will be charged the additional amount.\n\n"
            . "As a reminder, hosts are only permitted to charge up to 1.5× the original hourly rate for any additional hours beyond a 15-minute grace period. If the fee exceeds this limit or you did not approve the extra time, please contact Location Jewel before submitting payment.\n\n"
            . "If you have any questions, we’re here to help.\n\n"
            . "Best,\n"
            . "Location Jewel Team\n\n"
            . "Overtime Details:\n"
            . "    Hours: {$overtime_hours}\n"
            . "    Price per hour: $" . number_format($price_per_hour, 2) . "\n"
            . "    Total: $" . number_format($total_amount, 2);
        $created_by = $sender_id;
        $attachments = serialize(array()); // Serialize empty array for attachments

        $message_insert = $wpdb->insert(
            $table_name,
            array(
                'created_by' => $created_by,
                'thread_id' => $thread_id,
                'message' => sanitize_textarea_field($message),
                'attachments' => $attachments,
                'time' => current_time( 'mysql' )
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s',
                '%s'
            )
        );

        if(!$message_insert) {
            wp_send_json_error('Failed to insert message');
        }

        $message_id = $wpdb->insert_id;

        $data = array(
            'thread_id' => $thread_id,
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message_id' => $message_id,
            'price_per_hour' => $price_per_hour,
            'extra_hour' => $overtime_hours, // Changed to match table schema
            'total_amount' => $total_amount,
            'status' => 'pending',
            'new_reservation_id' => '',
        );

        $overtime_insert = homey_add_overtime($data); // Use the helper function instead of direct insert
        
        if(!$overtime_insert) {
            echo json_encode(array('success' => false, 'msg' => 'Failed to insert overtime record'));
            wp_die();
        }

        echo json_encode(array('success' => true, 'msg' => 'Additional Hours Sent.'));
        wp_die();

    }else{
        echo json_encode(array('success' => false, 'msg' => 'Invalid overtime hours or price per hour or thread id'));
        wp_die();
    }
}

add_action('wp_ajax_reject_overtime_message', 'reject_overtime_message');
add_action('wp_ajax_nopriv_reject_overtime_message', 'reject_overtime_message');
function reject_overtime_message(){
    global $wpdb;

    $nonce = $_POST['start_thread_message_form_ajax'];
    if(!wp_verify_nonce($nonce, 'start-thread-message-form-nonce')){
        wp_send_json_error('Invalid nonce');
    }

    if(!empty($_POST['message_id']) && !empty($_POST['thread_id'])){
        $message_id = intval($_POST['message_id']);
        $thread_id = intval($_POST['thread_id']);

        $table_name = $wpdb->prefix . 'homey_overtime_threads';
        $wpdb->update(
            $table_name,
            array('status' => 'rejected'),
            array('message_id' => $message_id),
            array('%s')
        );

        echo json_encode(array('success' => true, 'msg' => 'Additional Hours Rejected.'));
        wp_die();
    }else{
        echo json_encode(array('success' => false, 'msg' => 'Invalid message id or thread id'));
        wp_die();
    }
}

add_action('wp_ajax_approve_overtime_message', 'approve_overtime_message');
add_action('wp_ajax_nopriv_approve_overtime_message', 'approve_overtime_message');
function approve_overtime_message(){
    global $wpdb;

    $thread_id = intval($_POST['thread_id']);
    $message_id = intval($_POST['message_id']);

    $overtime_thread = homey_get_overtime_by_message_id($message_id);
    if(!$overtime_thread){
        echo json_encode(array('success' => false, 'msg' => 'Overtime thread not found'));
        wp_die();
    }
    
    $total_price = $overtime_thread->total_amount;
    $overtime_hours = $overtime_thread->extra_hour;
    $overtime_price_per_hour = $overtime_thread->price_per_hour;

    $tabel = $wpdb->prefix . 'homey_threads';
    $sql_thread = $wpdb->prepare(
        "
        SELECT * 
        FROM $tabel 
        WHERE id = %d
        ",
        $thread_id
    );
    $homey_thread = $wpdb->get_row($sql_thread);
    $resevation_id = $homey_thread->listing_id;
    $sender_id = $homey_thread->sender_id;
    $receiver_id = $homey_thread->receiver_id;
    $listing_id = get_post_meta($resevation_id, 'reservation_listing_id', true);
    $listing_owner_id = $receiver_id;
    $userID = get_current_user_id();
    $booking_dates = get_post_meta($resevation_id, 'reservation_booking_dates', true);
    $guests = get_post_meta($resevation_id, 'reservation_guests', true);
    $reservation_meta = get_post_meta($resevation_id, 'reservation_meta', true);
    $extra_options = get_post_meta($resevation_id, 'extra_options', true);
    $total_hours = get_post_meta($resevation_id, 'reservation_total_hours', true);

    if(!empty($resevation_id) && !empty($total_price)){

        $title = "Overtime Reservation";

        $reservation = array(
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'homey_reservation',
            'post_author' => $receiver_id
        );
        $new_reservation_id = wp_insert_post($reservation);

        $reservation_update = array(
            'ID' => $new_reservation_id,
            'post_title' => $title . ' ' . $new_reservation_id
        );
        wp_update_post($reservation_update);

        update_post_meta($new_reservation_id, 'reservation_listing_id', $listing_id);
        update_post_meta($new_reservation_id, 'listing_owner', $listing_owner_id);
        update_post_meta($new_reservation_id, 'listing_renter', $userID);
        update_post_meta($new_reservation_id, 'reservation_booking_dates', $booking_dates);
        update_post_meta($new_reservation_id, 'reservation_guests', $guests);
        update_post_meta($new_reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($new_reservation_id, 'reservation_status', 'available');
        update_post_meta($new_reservation_id, 'is_hourly', 'no');
        update_post_meta($new_reservation_id, 'extra_options', $extra_options);
        update_post_meta($new_reservation_id, 'reservation_total_hours', $total_hours);

        update_post_meta($new_reservation_id, 'reservation_upfront', $total_price);
        update_post_meta($new_reservation_id, 'reservation_balance', $total_price);
        update_post_meta($new_reservation_id, 'reservation_total', $total_price);

        update_post_meta($new_reservation_id, 'reservation_type', 'overtime_policy');
        update_post_meta($new_reservation_id, 'reservation_overtime_hours', $overtime_hours);
        update_post_meta($new_reservation_id, 'reservation_overtime_price_per_hour', $overtime_price_per_hour);

        $reservation_confirm_date_time = date('Y-m-d H:i:s'); // current time
        update_post_meta($new_reservation_id, 'reservation_confirm_date_time', $reservation_confirm_date_time);

        $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
        $reservation_detail_link = add_query_arg('reservation_detail', $new_reservation_id, $reservation_page);

        $reservation_time = current_time('timestamp');
        $reservation_time = date('Y-m-d H:i:s', $reservation_time); 
        update_post_meta($new_reservation_id, 'reservation_time', $reservation_time);

        homey_update_reservation_id_by_message_id($message_id, $new_reservation_id);

        // Update overtime status to approved
        $wpdb->update(
            $wpdb->prefix . 'homey_overtime_threads',
            array('status' => 'approved'),
            array('message_id' => $message_id),
            array('%s')
        );

        echo json_encode(
            array(
                'success' => true,
                'msg' => 'Payment Approved',
                'reservation_detail' => $reservation_detail_link
            )
        );
        wp_die();

    }else{
        echo json_encode(
            array(
                'success' => false, 
                'msg' => 'Invalid reservation id or total price'
            )
        );
        wp_die();
    }
}