<?php
global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id, $hide_labels;
$blogInfo = esc_url( home_url('/') );

if(isset($_GET['reservation_no_userHash'])){
    $userID = deHashNoUserId($_GET['reservation_no_userHash']);
}else{
    wp_get_current_user();
    $userID =   $current_user->ID;
}

$messages_page = homey_get_template_link_2('template/dashboard-messages.php');
$booking_hide_fields = homey_option('booking_hide_fields');
$booking_detail_hide_fields = homey_option('booking_detail_hide_fields');

$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
$reservation_status = $notification = $status_label = $notification = '';
$upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
$smoke = $pets = $party = $children = $additional_rules = '';

$payment_link = $cancellation_policy = '';
$post = get_post($reservationID);
$post_type = isset($post->post_type) ? $post->post_type : 'homey_reservation';

if(!empty($reservationID) && $post_type == 'homey_reservation') {
    if(homey_is_renter()) {
        $back_to_list = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        if(!homey_listing_guest($reservationID)) {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations.php');
        } else {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations2.php');
        }
    }

    $current_date = date( 'Y-m-d', current_time( 'timestamp', 0 ));
    $current_date_unix = strtotime($current_date );

    $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
    $total_price = get_post_meta($reservationID, 'reservation_total', true);
    $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
    $upfront_payment = homey_formatted_price($upfront_payment);
    $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

    $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
    $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
    $guests = get_post_meta($reservationID, 'reservation_guests', true);
    $adult_guest = get_post_meta($reservationID, 'reservation_adult_guest', true);
    $child_guest = get_post_meta($reservationID, 'reservation_child_guest', true);
    $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
    $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
    $res_meta   = get_post_meta($reservationID, 'reservation_meta', true);

    $reservation_type = get_post_meta($reservationID, 'reservation_type', true) ?? '';
    $reservation_overtime_hours = get_post_meta($reservationID, 'reservation_overtime_hours', true);
    $reservation_overtime_price_per_hour = get_post_meta($reservationID, 'reservation_overtime_price_per_hour', true);

    $total_hours = get_post_meta($reservationID, 'reservation_total_hours', true);
    $guests_range = homey_get_total_guests_range($reservationID, $listing_id);

    $booking_type = homey_booking_type_by_id($listing_id);

    $extra_expenses = homey_get_extra_expenses($reservationID);
    $extra_discount = homey_get_extra_discount($reservationID);

    $site_rep_name = get_post_meta($listing_id, 'homey_rep_name', true);
    $welcome_message = get_post_meta($listing_id, 'homey_instructions', true);
    $location_rules = get_post_meta($listing_id, 'homey_additional_rules', true);
    $booking_dates = get_post_meta($reservationID, 'reservation_booking_dates', true);

    $address = get_post_meta($listing_id, 'homey_listing_full_address', true);

    if(!empty($extra_expenses)) {
        $expenses_total_price = $extra_expenses['expenses_total_price'];
        $total_price = $total_price + $expenses_total_price;
    }

    if(!empty($extra_discount)) {
        $discount_total_price = $extra_discount['discount_total_price'];
        $total_price = $total_price - $discount_total_price;
    }

    if(homey_option('reservation_payment') == 'full') {
        $upfront_payment = homey_formatted_price($total_price); 
    }

    $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

    $renter_id = get_post_meta($reservationID, 'listing_renter', true);
    $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

    $renter_nickname  = get_user_meta($renter_id, 'nickname', true);
    $renter_name_while_booking  = get_user_meta($renter_id, 'first_name', true);
    $renter_name_while_booking .= ' '.get_user_meta($renter_id, 'last_name', true);
    if(trim(empty($renter_name_while_booking))){
        $renterwhile_booking = get_userdata($renter_id);
        $renter_name_while_booking = $renterwhile_booking->display_name;
    }

    if(empty(trim($renter_name_while_booking))){
        $renter_name_while_booking = explode('@', $renter_nickname);
        $renter_name_while_booking = isset($renter_name_while_booking[0]) ? $renter_name_while_booking[0] : esc_html__('No Information', 'homey');
    }

    $renter_phone = get_user_meta($renter_id, 'phone', true);

    $owner_id = get_post_meta($reservationID, 'listing_owner', true);
    $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

    $payment_link = add_query_arg( array(
            'reservation_id' => $reservationID,
        ), $payment_link );

    $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

    if($chcek_reservation_thread != '') {
        $messages_page_link = add_query_arg( array(
            'thread_id' => $chcek_reservation_thread
        ), $messages_page );
    } else {
        $messages_page_link = add_query_arg( array(
            'reservation_id' => $reservationID,
            'message' => 'new',
        ), $messages_page );
    }

    $guests_label = homey_option('cmn_guest_label');
    if($guests > 1) {
        $guests_label = homey_option('cmn_guests_label');
    }

    $smoke            = homey_get_listing_data('smoke', $listing_id);
    $pets             = homey_get_listing_data('pets', $listing_id);
    $party            = homey_get_listing_data('party', $listing_id);
    $children         = homey_get_listing_data('children', $listing_id);
    $additional_rules = homey_get_listing_data('additional_rules', $listing_id);
    $cancellation_policy = get_post_meta($listing_id, $homey_prefix.'cancellation_policy', true);


    if($smoke != 1) {
        $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
        $smoke_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $smoke_allow = 'homey-icon homey-icon-arrow-right-1';
        $smoke_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($pets != 1) {
        $pets_allow = 'homey-icon homey-icon-arrow-right-1';
        $pets_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $pets_allow = 'homey-icon homey-icon-arrow-right-1';
        $pets_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($party != 1) {
        $party_allow = 'homey-icon homey-icon-arrow-right-1';
        $party_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $party_allow = 'homey-icon homey-icon-arrow-right-1';
        $party_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if($children != 1) {
        $children_allow = 'homey-icon homey-icon-arrow-right-1';
        $children_text = esc_html__(homey_option('sn_text_no'), 'homey');
    } else {
        $children_allow = 'homey-icon homey-icon-arrow-right-1';
        $children_text = esc_html__(homey_option('sn_text_yes'), 'homey');
    }

    if(!empty($cancellation_policy)){
        $cancellation_policy   = get_the_content( '', '',  $cancellation_policy ); // Where $cancellation_policy is the ID
    }else{
        $cancellation_policy = '';
    }

}

$cancellation_policy = homey_option('cancellation_policy_text');
$overtime_policy = homey_option('overtime_policy_text');

$reservation_type = get_post_meta($reservationID, 'reservation_type', true);
$reservation_confirm_date_time = get_post_meta($reservationID, 'reservation_confirm_date_time', true);
$hours_diff = 0;

if ($reservation_type == 'overtime_policy' && !empty($reservation_confirm_date_time)) {
    $reservation_confirm_timestamp = strtotime($reservation_confirm_date_time);
    $current_timestamp = time();

    $hours_diff = ($reservation_confirm_timestamp - $current_timestamp) / 3600;
}

if( !homey_give_access($reservationID) ) {
    echo('Are you kidding?');
    
} else {
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <?php
    if($reservation_status == 'declined') {
        get_template_part('template-parts/dashboard/reservation/declined');
    } elseif($reservation_status == 'cancelled') {
        get_template_part('template-parts/dashboard/reservation/cancelled');
    } else {
        get_template_part('template-parts/dashboard/reservation/decline-form');
    }
    ?>
    <?php if ($post_type == 'homey_reservation') { ?>

        <?php if($hours_diff > 48): ?>
            <div class="reservation-overtime-policy-section" style="padding: 10px; border-radius: 5px; color: #721c24;">
                <p style="margin-bottom: 0px;"><?php esc_html_e('This booking has expired. Please request for new reservation.','homey-child');?></p>
            </div>
        <?php else: ?>

            <?php if($reservation_type == 'overtime_policy'): ?>
                <div class="reservation-overtime-policy-section">
                    <p style="margin-bottom: 0px;"><?php esc_html_e('This booking was created as an additional hours extension. Charges reflect additional time beyond your original reservation.','homey-child');?></p>
                </div>
            <?php endif; ?>
            <div class="dashboard-reservation-content-area">
                <div class="reservation-first-content-container">
                    <div class="reservation-main-content">
                        <h2 class="title"><?php esc_html_e("Reservation Details", "homey-child"); ?></h2>
                        <div class="content-area">
                            <div class="booking-detail-package">
                                <img width="60" style="border-radius: 6px;" height="50" src="<?php echo get_the_post_thumbnail_url($listing_id); ?>" alt="<?php echo get_the_title($listing_id); ?>">
                                <div class="content-area">
                                    <h3 class="title"><a href="<?php echo get_the_permalink($listing_id); ?>"><?php echo get_the_title($listing_id); ?></a></h3>
                                    <p class="clamp-10-words" style="margin-bottom: 0px;"><?php echo get_the_excerpt($listing_id); ?></p>
                                </div>
                            </div>
                            <?php if(homey_is_renter()):?>
                            <div class="booking-detail-host">
                                <img width="60" style="border-radius:6px;" src="<?php echo get_avatar_url($owner_id); ?>" alt="<?php echo get_the_title($owner_id); ?>">
                                <div class="content-area">
                                <?php 
                                $user_data = get_userdata( $owner_id );
                                $owner_name = $user_data->display_name;
                                ?>
                                    <h3 class="title"><?php echo esc_html($owner_name); ?></h3>
                                    <a href="<?php echo esc_url($messages_page_link); ?>" class="contact-now-host-button"><i class="homey-icon homey-icon-unread-emails"></i> <?php echo esc_html('Contact Host','homey-child'); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if(homey_is_host()):?>
                            <div class="booking-detail-host">
                                <img width="60" style="border-radius:6px;" src="<?php echo get_avatar_url($owner_id); ?>" alt="<?php echo get_the_title($owner_id); ?>">
                                <div class="content-area">
                                <?php 
                                $renter_id = get_post_meta($reservationID, 'listing_renter', true);
                                $user_data = get_userdata( $renter_id );
                                $renter_name = $user_data->display_name;
                                ?>
                                    <h3 class="title"><?php echo esc_html($renter_name); ?></h3>
                                    <a href="<?php echo esc_url($messages_page_link); ?>" class="contact-now-host-button"><i class="homey-icon homey-icon-unread-emails"></i> <?php echo esc_html('Contact Renter','homey-child'); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if(homey_is_admin()){ ?>
                                <div class="booking-detail-host">
                                    <img width="60" style="border-radius:6px;" src="<?php echo get_avatar_url($owner_id); ?>" alt="<?php echo get_the_title($owner_id); ?>">
                                    <div class="content-area">
                                    <?php 
                                    $user_data = get_userdata( $owner_id );
                                    $owner_name = $user_data->display_name;
                                    ?>
                                        <h3 class="title"><?php echo esc_html($owner_name); ?></h3>
                                        <a href="<?php echo esc_url($messages_page_link); ?>" class="contact-now-host-button"><i class="homey-icon homey-icon-unread-emails"></i> <?php echo esc_html('Contact Host','homey-child'); ?></a>
                                    </div>
                                </div>
                                <?php 
                                $renter_id = get_post_meta($reservationID, 'listing_renter', true);
                                $user_data = get_userdata( $renter_id );
                                $renter_name = $user_data->display_name;
                                ?>
                                <div class="booking-detail-host">
                                    <img width="60" style="border-radius:6px;" src="<?php echo get_avatar_url($renter_id); ?>" alt="<?php echo get_the_title($renter_id); ?>">
                                    <div class="content-area">
                                        <h3 class="title"><?php echo esc_html($renter_name); ?></h3>
                                        <a href="<?php echo esc_url($messages_page_link); ?>" class="contact-now-host-button"><i class="homey-icon homey-icon-unread-emails"></i> <?php echo esc_html('Contact Renter','homey-child'); ?></a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="reservation-main-content">
                    <h2 class="title"><?php esc_html_e("Event Dates & Address", "homey-child"); ?></h2>
                    <div class="content-area" style="padding: 10px 15px;">
                            <div class="booking-time">
                                <?php
                                    if (!empty($booking_dates)) {
                                        foreach ($booking_dates as $booking_date) {
                                            if (!empty($booking_date['arrive_date'])) {
                                                $arrive_date = $booking_date['arrive_date'];
                                                $start_hour = isset($booking_date['start_hour']) ? $booking_date['start_hour'] : '';
                                                $end_hour = isset($booking_date['end_hour']) ? $booking_date['end_hour'] : '';
                                                
                                                // Convert times to PST timezone
                                                $pst_timezone = new DateTimeZone('America/Los_Angeles');
                                                $utc_timezone = new DateTimeZone('UTC');
                                                
                                                // Parse the date from MM-DD-YYYY format and convert to YYYY-MM-DD
                                                $date_parts = explode('-', $arrive_date);
                                                if (count($date_parts) == 3) {
                                                    // Assume format is MM-DD-YYYY
                                                    $standard_date = $date_parts[2] . '-' . $date_parts[0] . '-' . $date_parts[1];
                                                } else {
                                                    $standard_date = $arrive_date;
                                                }
                                                
                                                if (!empty($start_hour)) {
                                                    // Create datetime from arrive_date and start_hour, assume UTC initially
                                                    $start_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $standard_date . ' ' . $start_hour . ':00:00', $utc_timezone);
                                                    if ($start_datetime) {
                                                        $start_datetime->setTimezone($pst_timezone);
                                                        $start_hour_formatted = $start_datetime->format('g:i A T');
                                                    } else {
                                                        $start_hour_formatted = 'N/A';
                                                    }
                                                } else {
                                                    $start_hour_formatted = 'N/A';
                                                }
                                                
                                                if (!empty($end_hour)) {
                                                    // Create datetime from arrive_date and end_hour, assume UTC initially
                                                    $end_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $standard_date . ' ' . $end_hour . ':00:00', $utc_timezone);
                                                    if ($end_datetime) {
                                                        $end_datetime->setTimezone($pst_timezone);
                                                        $end_hour_formatted = $end_datetime->format('g:i A T');
                                                    } else {
                                                        $end_hour_formatted = 'N/A';
                                                    }
                                                } else {
                                                    $end_hour_formatted = 'N/A';
                                                }
                                                
                                                echo '<p style="margin-bottom: 0px;"><b>Date:</b> ' . esc_html($arrive_date) . ' (Start Time: ' . esc_html($start_hour_formatted) . ' - End Time: ' . esc_html($end_hour_formatted) . ')</p>';
                                            }
                                        }
                                    }
                                ?>
                            </div>
                            <?php if(!empty($address)){ ?>
                                <div class="booking-address">
                                    <p style="margin-bottom: 0px;"><b>Address: </b> <?php echo $address; ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="reservation-site-info-section">
                        <h2 class="title"><?php esc_html_e("Site Contact & Day of Instructions", "homey-child"); ?></h2>
                        <div class="content">
                            <b><?php echo $site_rep_name; ?></b>
                            <p style="margin-bottom: 0px;"><?php echo esc_attr($welcome_message); ?></p>
                        </div>
                    </div>

                </div>
                <div class="reservation-second-content-container">
                    <div class="reservation-price-section">
                        <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar', '', array("booking_type", $booking_type)); ?>
                    </div>
                    <div class="reservation-site-info-section">
                        <h2 class="title"><?php esc_html_e("Event Insurance Add-On", "homey-child"); ?></h2>
                        <div class="content">
                            <p style="margin-bottom: 0px;"><?php esc_html_e('You can purchase event insurance through our partner,','homey-child');?> <a target="__blank" href="https://www.theeventhelper.com/#91wv65"><?php esc_html_e('EventHelper.','homey-child');?></a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reservation-action-container">
                <div class="reveiws-reports">
                    <?php if(homey_is_renter()): ?>
                        <a href="#" data-reservation_id="<?php echo $reservationID?>" class="btn btn-primary submit-a-review-btn"><?php esc_html_e('Submit a Review','homey-child');?></a>
                    <?php endif; ?>
                    <a href="#" data-reservation_id="<?php echo $reservationID?>" class="btn btn-primary report-a-problem-btn"><?php esc_html_e('Report a Problem','homey-child');?></a>
                </div>
                <div class="reservation-default-actions">
                    <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php } ?>

    <?php if ($post_type == 'homey_e_reservation') { ?>
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div class="block">
                                <div class="block-head">
                                        <h2 class="title"><?php echo esc_html__('This reservation belongs to Experiences, please visit experiences reservation detail page.', 'homey');  ?></h2>
                                </div><!-- block-head -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
        </div><!-- .user-dashboard-right -->
    <?php get_template_part('template-parts/dashboard/reservation/message'); ?>
    <div class="modal fade" id="dialog-confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php esc_html_e('Are you sure!','homey-child');?></h4>
        </div>
        <div class="modal-body">
            <p><?php esc_html_e('Warning, you are about to cancel an existing reservation. This is frowned upon and may result in penalties to your account if consistently done. Are you sure you wish to cancel?','homey-child');?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e('Close','homey-child');?></button>
            <button type="button" class="btn btn-danger confirm-cancel" id="confirm-reservation-cancelation"><?php esc_html_e('Cancel Reservation','homey-child');?></button>
        </div>
        </div>
    </div>
</div>

<!-- ======================================================
SUBMIT A REVIEW MODAL
====================================================== -->
<div class="modal fade homey-child-modal" id="submitReviewModal" tabindex="-1" role="dialog" aria-labelledby="submitReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header hc-modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="submitReviewModalLabel">
                    <i class="homey-icon homey-icon-star"></i>
                    <?php esc_html_e('Submit a Review', 'homey-child'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <form id="submit-review-form" novalidate>
                    <input type="hidden" name="reservation_id" id="review_reservation_id" value="<?php echo esc_attr($reservationID); ?>">
                    <input type="hidden" name="security" value="<?php echo wp_create_nonce('homey-child-review-nonce'); ?>">

                    <!-- Star Rating -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Your Rating', 'homey-child'); ?> <span class="hc-required">*</span></label>
                        <div class="hc-star-rating" id="hc-star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="hc-star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="hc-star-input">
                                <label for="hc-star<?php echo $i; ?>" class="hc-star-label" title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                        <div class="hc-rating-text" id="hc-rating-text"></div>
                    </div>

                    <!-- Review Content -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label" for="hc_review_content"><?php esc_html_e('Your Review', 'homey-child'); ?> <span class="hc-required">*</span></label>
                        <textarea id="hc_review_content" name="review_content" class="form-control hc-textarea" rows="5"
                            placeholder="<?php esc_attr_e('Share your experience with this venue — what did you love, and what could be improved?', 'homey-child'); ?>"></textarea>
                        <span class="hc-char-count" id="hc-review-char-count">0 / 1000</span>
                    </div>

                    <!-- Message Area -->
                    <div id="hc-review-form-message"></div>
                </form>
            </div>
            <div class="modal-footer hc-modal-footer">
                <button type="button" class="btn btn-default hc-btn-cancel" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'homey-child'); ?>
                </button>
                <button type="button" class="btn btn-primary hc-btn-submit submit-review-ajax-btn">
                    <span class="hc-btn-text"><?php esc_html_e('Submit Review', 'homey-child'); ?></span>
                    <span class="hc-btn-loading" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i> <?php esc_html_e('Submitting...', 'homey-child'); ?>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================
REPORT A PROBLEM MODAL
====================================================== -->
<div class="modal fade homey-child-modal" id="reportProblemModal" tabindex="-1" role="dialog" aria-labelledby="reportProblemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header hc-modal-header hc-modal-header--danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="reportProblemModalLabel">
                    <i class="homey-icon homey-icon-warning-circle"></i>
                    <?php esc_html_e('Report an Issue', 'homey-child'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="hc-report-intro">
                    <p><?php esc_html_e('This form documents an issue related to your booking and notifies both parties.', 'homey-child'); ?></p>
                    <p class="hc-text-muted"><em><?php esc_html_e('Location Jewel facilitates communication between Members and does not determine legal liability or guarantee resolution.', 'homey-child'); ?></em></p>
                </div>

                <form id="report-problem-form" novalidate>
                    <input type="hidden" name="reservation_id" id="report_reservation_id" value="<?php echo esc_attr($reservationID); ?>">
                    <input type="hidden" name="security" value="<?php echo wp_create_nonce('homey-child-report-nonce'); ?>">

                    <!-- Section: Booking Details -->
                    <div class="hc-section-title"><?php esc_html_e('Booking Details', 'homey-child'); ?></div>

                    <!-- I am -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('I am', 'homey-child'); ?> <span class="hc-required">*</span></label>
                        <div class="hc-radio-group">
                            <label class="hc-radio-label">
                                <input type="radio" name="user_role" value="host" <?php echo homey_is_host() ? 'checked' : ''; ?>>
                                <span class="hc-radio-box"></span>
                                <?php esc_html_e('Host', 'homey-child'); ?>
                            </label>
                            <label class="hc-radio-label">
                                <input type="radio" name="user_role" value="renter" <?php echo homey_is_renter() ? 'checked' : ''; ?>>
                                <span class="hc-radio-box"></span>
                                <?php esc_html_e('Renter', 'homey-child'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Booking Address (read-only, pre-filled) -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Booking Address', 'homey-child'); ?></label>
                        <input type="text" class="form-control hc-input hc-input--readonly" value="<?php echo esc_attr($address); ?>" readonly>
                    </div>

                    <!-- Booking Date & Time -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Booking Date & Time', 'homey-child'); ?></label>
                        <div class="hc-input hc-input--readonly hc-booking-dates">
                            <?php
                            if (!empty($booking_dates)) {
                                foreach ($booking_dates as $bd) {
                                    if (!empty($bd['arrive_date'])) {
                                        $bd_start = isset($bd['start_hour']) ? $bd['start_hour'] : '';
                                        $bd_end   = isset($bd['end_hour'])   ? $bd['end_hour']   : '';
                                        echo '<span class="hc-date-entry">' . esc_html($bd['arrive_date']);
                                        if ($bd_start) echo ' ' . esc_html__('Start:', 'homey-child') . ' ' . esc_html($bd_start) . ':00';
                                        if ($bd_end)   echo ' ' . esc_html__('End:', 'homey-child')   . ' ' . esc_html($bd_end)   . ':00';
                                        echo '</span>';
                                    }
                                }
                            } else {
                                echo '<span class="hc-text-muted">' . esc_html__('N/A', 'homey-child') . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Host Name -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Host Name', 'homey-child'); ?></label>
                        <?php
                        $report_owner_data = get_userdata($owner_id);
                        $report_owner_name = $report_owner_data ? $report_owner_data->display_name : '';
                        ?>
                        <input type="text" class="form-control hc-input hc-input--readonly" value="<?php echo esc_attr($report_owner_name); ?>" readonly>
                    </div>

                    <!-- Renter Name -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Renter Name', 'homey-child'); ?></label>
                        <input type="text" class="form-control hc-input hc-input--readonly" value="<?php echo esc_attr($renter_name_while_booking); ?>" readonly>
                    </div>

                    <!-- Booking ID -->
                    <div class="form-group hc-form-group">
                        <label class="hc-label"><?php esc_html_e('Booking ID', 'homey-child'); ?> <span class="hc-text-muted"><?php esc_html_e('(Optional)', 'homey-child'); ?></span></label>
                        <input type="text" class="form-control hc-input hc-input--readonly" value="<?php echo esc_attr($reservationID); ?>" readonly>
                    </div>

                    <!-- Section: Issue Type -->
                    <div class="hc-section-title"><?php esc_html_e('What type of issue are you reporting?', 'homey-child'); ?> <span class="hc-required">*</span></div>
                    <div class="form-group hc-form-group">
                        <div class="hc-checkbox-group">
                            <label class="hc-checkbox-label">
                                <input type="radio" name="issue_type" value="property_damage" class="hc-issue-type">
                                <span class="hc-checkbox-box"></span>
                                <?php esc_html_e('Property Damage', 'homey-child'); ?>
                            </label>
                            <label class="hc-checkbox-label">
                                <input type="radio" name="issue_type" value="injury" class="hc-issue-type">
                                <span class="hc-checkbox-box"></span>
                                <?php esc_html_e('Injury', 'homey-child'); ?>
                            </label>
                            <label class="hc-checkbox-label">
                                <input type="radio" name="issue_type" value="other" class="hc-issue-type">
                                <span class="hc-checkbox-box"></span>
                                <?php esc_html_e('Other', 'homey-child'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Section: Description -->
                    <div class="hc-section-title"><?php esc_html_e('Please describe what happened, including when it occurred.', 'homey-child'); ?> <span class="hc-required">*</span></div>
                    <div class="form-group hc-form-group">
                        <textarea id="hc_report_description" name="description" class="form-control hc-textarea" rows="6"
                            placeholder="<?php esc_attr_e('Provide a detailed account of the incident, including the date and time it occurred...', 'homey-child'); ?>"></textarea>
                        <span class="hc-char-count" id="hc-desc-char-count">0 / 2000</span>
                    </div>

                    <!-- Section: Damage Details (optional) -->
                    <div class="hc-section-title"><?php esc_html_e('List Damage and Amount', 'homey-child'); ?> <span class="hc-text-muted"><?php esc_html_e('(if applicable)', 'homey-child'); ?></span></div>
                    <div class="form-group hc-form-group">
                        <div class="row">
                            <div class="col-sm-8">
                                <input type="text" name="damage_list" class="form-control hc-input"
                                    placeholder="<?php esc_attr_e('Describe the damage (e.g., broken chair, stained carpet)', 'homey-child'); ?>">
                            </div>
                            <div class="col-sm-4">
                                <div class="input-group">
                                    <span class="input-group-addon">$</span>
                                    <input type="number" name="damage_amount" min="0" step="0.01" class="form-control hc-input"
                                        placeholder="<?php esc_attr_e('Amount', 'homey-child'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Acknowledgment -->
                    <div class="hc-section-title"><?php esc_html_e('Acknowledgment', 'homey-child'); ?> <span class="hc-required">*</span></div>
                    <div class="form-group hc-form-group">
                        <label class="hc-acknowledgment-label">
                            <input type="checkbox" name="acknowledgment" value="1" id="hc-acknowledgment">
                            <span class="hc-acknowledgment-text">
                                <?php esc_html_e('By checking the box below and submitting this Incident Form, you certify that all of the information you provided on this Incident Form is to the best of your knowledge and belief true, correct, and complete.', 'homey-child'); ?>
                            </span>
                        </label>
                    </div>

                    <!-- Fraud Warning -->
                    <div class="hc-fraud-warning">
                        <p><strong><?php esc_html_e('FRAUD WARNING:', 'homey-child'); ?></strong> <?php esc_html_e('Any person or business entity providing false, altered, or fraudulent statements, information, photos, and/or identity verification, insurance, or claim purposes may face criminal prosecution and civil liability. Location Jewel, LLC may immediately report violators, their identity information, and any evidence of fraudulent and criminal activity to law enforcement agencies and prosecuting offices to be charged federally and under state laws. The information you submit on this Incident Form and your account information may be used for incident investigation and processing purposes and may be shared with law enforcement and any applicable Location Jewel, LLC users, insurance providers, brokers, or agents, including any assigned insurance adjusters, special investigation units, and private investigators.', 'homey-child'); ?></p>
                    </div>

                    <!-- Message Area -->
                    <div id="hc-report-form-message"></div>
                </form>
            </div>
            <div class="modal-footer hc-modal-footer">
                <button type="button" class="btn btn-default hc-btn-cancel" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'homey-child'); ?>
                </button>
                <button type="button" class="btn btn-danger hc-btn-submit submit-report-ajax-btn">
                    <span class="hc-btn-text"><?php esc_html_e('Submit Report', 'homey-child'); ?></span>
                    <span class="hc-btn-loading" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i> <?php esc_html_e('Submitting...', 'homey-child'); ?>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php }
