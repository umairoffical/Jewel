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
            <div class="reservation-overtime-policy-section" style="background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb; color: #721c24;">
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
                <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>
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
<?php }
