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

if( !homey_give_access($reservationID) ) {
    echo('Are you kidding?');
    
} else {
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <?php if ($post_type == 'homey_reservation') { ?>
        <div class="dashboard-content-area dashboard-reservation-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <input type="hidden" id="resrv_id" value="<?php echo intval($reservationID); ?>">
                            <?php homey_reservation_notification($reservation_status, $reservationID); ?>

                            <?php //if($reservation_status == 'under_review') { ?>
                                <?php //if(homey_is_admin() || homey_is_host()) {?>
                                    <!-- <div class="alert alert-danger reservation-time-countdown-alert" role="alert">
                                        This reservation will be cancelled in
                                        <div class="reservation-time-countdown">
                                            <?php
                                            // $reservation_time = strtotime(get_post_meta($reservationID, 'reservation_time', true));
                                            // $current_time = current_time('timestamp');
                                            // $time_diff = $reservation_time + (48 * 3600) - $current_time;
                                            
                                            // if ($time_diff > 0) {
                                            //     $hours = floor($time_diff / 3600);
                                            //     $minutes = floor(($time_diff % 3600) / 60);
                                            //     $seconds = $time_diff % 60;
                                                ?>
                                                <div id="countdown-timer" 
                                                        data-end="<?php //echo $reservation_time + (48 * 3600); ?>"
                                                        data-current="<?php //echo $current_time; ?>">
                                                    <span class="counter-timer-hours"><?php //echo str_pad($hours, 2, '0', STR_PAD_LEFT); ?> hours</span>,
                                                    <span class="counter-timer-minutes"><?php //echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?> minutes</span>
                                                    <span class="counter-timer-seconds"><?php //echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?> seconds. </span>
                                                </div>
                                            <?php //} ?>
                                        </div>
                                        Please confirm availability before then.
                                    </div> -->
                                <?php //} else { ?>
                                    <!-- <div class="alert alert-danger reservation-time-countdown-alert" role="alert">
                                        Your reservation is awaiting host confirmation. Please note there are
                                        <div class="reservation-time-countdown">
                                            <?php
                                            // $reservation_time = strtotime(get_post_meta($reservationID, 'reservation_time', true));
                                            // $current_time = current_time('timestamp');
                                            // $time_diff = $reservation_time + (48 * 3600) - $current_time;
                                            
                                            // if ($time_diff > 0) {
                                            //     $hours = floor($time_diff / 3600);
                                            //     $minutes = floor(($time_diff % 3600) / 60);
                                            //     $seconds = $time_diff % 60;
                                                ?>
                                                <div id="countdown-timer" 
                                                        data-end="<?php //echo $reservation_time + (48 * 3600); ?>"
                                                        data-current="<?php //echo $current_time; ?>">
                                                    <span class="counter-timer-hours"><?php //echo str_pad($hours, 2, '0', STR_PAD_LEFT); ?> hours</span>,
                                                    <span class="counter-timer-minutes"><?php //echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?> minutes and </span>
                                                    <span class="counter-timer-seconds"><?php //echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?> seconds </span>
                                                </div>
                                            <?php //} ?>
                                        </div>
                                        remaining.
                                    </div> -->
                                <?php //} ?>
                            <?php //} ?>

                            <?php //if($reservation_status == 'available') { ?>
                                <?php //if(homey_is_admin() || homey_is_host()) {?>
                                    <!-- <div class="alert alert-danger reservation-time-countdown-alert" role="alert">
                                        Reservation Confirmed! 
                                        <div class="reservation-time-countdown">
                                            <?php
                                            // $reservation_time = strtotime(get_post_meta($reservationID, 'reservation_confirm_date_time', true));
                                            // $current_time = current_time('timestamp');
                                            // $time_diff = $reservation_time + (48 * 3600) - $current_time;
                                            
                                            // if ($time_diff > 0) {
                                            //     $hours = floor($time_diff / 3600);
                                            //     $minutes = floor(($time_diff % 3600) / 60);
                                            //     $seconds = $time_diff % 60;
                                                ?>
                                                <div id="countdown-timer" 
                                                        data-end="<?php //echo $reservation_time + (48 * 3600); ?>"
                                                        data-current="<?php //echo $current_time; ?>">
                                                    <span class="counter-timer-hours"><?php //echo str_pad($hours, 2, '0', STR_PAD_LEFT); ?> hours</span>,
                                                    <span class="counter-timer-minutes"><?php //echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?> minutes</span>
                                                    <span class="counter-timer-seconds"><?php //echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?> seconds. </span>
                                                </div>
                                            <?php //} ?>
                                        </div>
                                        left before resevation cancellation.
                                    </div> -->
                                <?php //} else { ?>
                                    <!-- <div class="alert alert-danger reservation-time-countdown-alert" role="alert">
                                        Kindly complete the payment before
                                        <div class="reservation-time-countdown">
                                            <?php
                                            // $reservation_time = strtotime(get_post_meta($reservationID, 'reservation_confirm_date_time', true));
                                            // $current_time = current_time('timestamp');
                                            // $time_diff = $reservation_time + (48 * 3600) - $current_time;
                                            
                                            // if ($time_diff > 0) {
                                            //     $hours = floor($time_diff / 3600);
                                            //     $minutes = floor(($time_diff % 3600) / 60);
                                            //     $seconds = $time_diff % 60;
                                                ?>
                                                <div id="countdown-timer" 
                                                        data-end="<?php //echo $reservation_time + (48 * 3600); ?>"
                                                        data-current="<?php //echo $current_time; ?>">
                                                    <span class="counter-timer-hours"><?php //echo str_pad($hours, 2, '0', STR_PAD_LEFT); ?> hours</span>,
                                                    <span class="counter-timer-minutes"><?php //echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?> minutes and </span>
                                                    <span class="counter-timer-seconds"><?php //echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?> seconds </span>
                                                </div>
                                            <?php //} ?>
                                        </div>
                                        to avoid cancellation of your reservation.
                                    </div> -->
                                <?php //} ?>
                            <?php //} ?>

                            <div class="block">
                                <div class="block-head">
                                    <div class="block-left block-count-down-timer-container">
                                        <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?>
                                        <?php $wc_order_id = get_wc_order_id(get_the_ID()); $wc_order_id_txt = $wc_order_id > 0 ? ', wc#'.$wc_order_id.' ' : ' '; ?>
                                        <?php echo '#'.$reservationID.$wc_order_id_txt.' '.homey_get_reservation_label($reservation_status, $reservationID); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <div class="custom-actions">

                                            <?php if($reservation_status == 'booked' && $current_date_unix >= strtotime($check_in)) { ?>
                                            <?php //if($reservation_status == 'booked') { ?>
                                            <button class="btn-action" data-toggle="collapse" data-target="#review-form" aria-expanded="false" aria-controls="collapseExample" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['review_btn']); ?>">
                                                <i class="homey-icon homey-icon-qpencil-interface-essential"></i>
                                            </button>
                                            <?php } ?>

                                            <button onclick="window.print();" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['print_btn']); ?>"><i class="homey-icon homey-icon-print-text"></i></button>

                                            <a href="<?php echo esc_url($messages_page_link); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['msg_send_btn']); ?>"><i class="homey-icon homey-icon-unread-emails"></i></a>

                                            <a href="<?php echo esc_url($back_to_list); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['back_btn']); ?>"><i class="homey-icon homey-icon-move-back-interface-essential"></i></a>
                                        </div><!-- custom-actions -->
                                    </div><!-- block-right -->
                                </div><!-- block-head -->

                                <?php
                                if($reservation_status == 'booked' && homey_listing_guest($reservationID)) {
                                    get_template_part('template-parts/dashboard/reservation/review-form');
                                } elseif($reservation_status == 'booked') {
                                    get_template_part('template-parts/dashboard/reservation/review-host');
                                }

                                get_template_part('template-parts/dashboard/reservation/add-extra-expenses');
                                get_template_part('template-parts/dashboard/reservation/discount');

                                if($reservation_status == 'declined') {
                                    get_template_part('template-parts/dashboard/reservation/declined');

                                } elseif($reservation_status == 'cancelled') {
                                    get_template_part('template-parts/dashboard/reservation/cancelled');
                                } else {


                                        get_template_part('template-parts/dashboard/reservation/cancel-form');

                                        if(!homey_listing_guest($reservationID)) {
                                            get_template_part('template-parts/dashboard/reservation/decline-form');
                                        }

                                }

                                $res_no_of_days = isset($res_meta['no_of_days']) ? $res_meta['no_of_days'] : 0;

                                if($res_no_of_days > 1) {
                                    $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_dates_label') : homey_option('glc_day_nights_label');
                                } else {
                                    $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_date_label') : homey_option('glc_day_night_label');
                                }

                                $no_of_weeks = isset($res_meta['total_weeks_count']) ? $res_meta['total_weeks_count'] : 0;
                                $no_of_months = isset($res_meta['total_months_count']) ? $res_meta['total_months_count'] : 0;

                                if($no_of_weeks > 1) {
                                    $week_label = homey_option('glc_weeks_label');
                                } else {
                                    $week_label = homey_option('glc_week_label');
                                }

                                if($no_of_months > 1) {
                                    $month_label = homey_option('glc_months_label');
                                } else {
                                    $month_label = homey_option('glc_month_label');
                                }

                                ?>

                                <?php if(!empty($welcome_message) && $reservation_status != 'under_review') { ?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e("Site Rep & Day of Instructions",'homey-child'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <b><?php echo $site_rep_name; ?></b>
                                            <p><?php echo esc_attr($welcome_message); ?></p>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <ul class="detail-list">
                                                <li><strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong></li>
                                                <li><?php echo translate_month_names(esc_attr( get_the_date( get_option( 'date_format' ), $reservationID )));?>
                                                <br>
                                                <?php echo esc_attr( get_the_date( homey_time_format(), $reservationID ));?> </li>
                                            </ul>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <?php if(!empty($renter_info['photo'])) {
                                                echo '<a href="'.esc_url($renter_info['link']).'" target="_blank">'.$renter_info['photo'].'</a>';
                                            }?>
                                            <ul class="detail-list">
                                                <li><strong><?php esc_html_e('From', 'homey'); ?>:</strong>
                                                    <a href="<?php echo esc_url($renter_info['link']); ?>" target="_blank">
                                                        <?php echo esc_html__(esc_attr($renter_info['name']), 'homey'); ?>
                                                    </a>
                                                </li>
                                                <?php if(@$booking_detail_hide_fields['renter_information_on_detail'] == 0){ ?>
                                                    <li><strong><?php esc_html_e('Renter Detail', 'homey'); ?>:&nbsp;</strong><?php echo esc_attr($renter_name_while_booking).' <a title="'.esc_html__('Click to call', 'homey').'" href="tel:'.$renter_phone.'">'. $renter_phone.'</a></li>'; ?>
                                                <?php } ?>
                                                <li><strong><?php esc_html_e('Listing Name', 'homey'); ?>:&nbsp;</strong><?php echo get_the_title($listing_id); ?></li>
                                            </ul>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->

                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <ul class="detail-list detail-list-2-cols">
                                                <li>
                                                    <?php echo esc_html__('Rental Start', 'homey'); ?>:
                                                    <strong><?php echo homey_get_booking_start_date($reservationID); ?></strong>
                                                </li>
                                                <li>
                                                    <?php echo esc_html__('Rental End', 'homey'); ?>:
                                                    <strong><?php echo homey_get_booking_end_date($reservationID); ?></strong>
                                                </li>
                                                <li>
                                                    <?php echo esc_html__('Total Hours', 'homey'); ?>:
                                                    <strong><?php echo $total_hours; ?></strong>
                                                </li>
                                                <li>
                                                    <?php echo esc_html__('Max Guests', 'homey'); ?>:
                                                    <strong><?php echo $guests_range; ?></strong>
                                                </li>
                                            </ul>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->

                                <?php if(!empty($booking_dates)){?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Booking Dates', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right reservation-booking-dates">
                                            <?php
                                            if (!empty($booking_dates)) {
                                                foreach ($booking_dates as $booking_date) {
                                                    if (!empty($booking_date['arrive_date'])) {
                                                        $arrive_date = $booking_date['arrive_date'];
                                                        $start_hour = isset($booking_date['start_hour']) ? $booking_date['start_hour'] : '';
                                                        $end_hour = isset($booking_date['end_hour']) ? $booking_date['end_hour'] : '';
                                                        // $formatted_date = date('d-m-y', strtotime($arrive_date));
                                                        
                                                        // Ensure proper AM/PM formatting in PST timezone
                                                        $start_hour_formatted = !empty($start_hour) ? date('g:i A T', strtotime($start_hour . ':00 PST')) : 'N/A';
                                                        $end_hour_formatted = !empty($end_hour) ? date('g:i A T', strtotime($end_hour . ':00 PST')) : 'N/A';
                                                        
                                                        echo '<p>Date: ' . esc_html($arrive_date) . ' (Start Time: ' . esc_html($start_hour_formatted) . ' - End Time: ' . esc_html($end_hour_formatted) . ')</p>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <?php if(!empty($renter_msg)) { ?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e('Notes', 'homey'); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <p><?php echo esc_attr($renter_msg); ?></p>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <?php if(!empty($location_rules)) { ?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e("Location Rules", "homey-child"); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <p><?php echo esc_attr($location_rules); ?></p>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <?php if(!empty($cancellation_policy)) { ?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e("Cancellation Policy", "homey-child"); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <p><?php echo $cancellation_policy; ?></p>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <?php if(!empty($overtime_policy)) { ?>
                                <div class="block-section">
                                    <div class="block-body">
                                        <div class="block-left">
                                            <h2 class="title"><?php esc_html_e("Overtime Policy", "homey-child"); ?></h2>
                                        </div><!-- block-left -->
                                        <div class="block-right">
                                            <p><?php echo $overtime_policy; ?></p>
                                        </div><!-- block-right -->
                                    </div><!-- block-body -->
                                </div><!-- block-section -->
                                <?php } ?>

                                <?php if($reservation_type != 'overtime_policy' || empty($reservation_type)){?>
                                    <div class="block-section" data-reservation-payment-detail-section="1">
                                        <div class="block-body">
                                            <div class="block-left">
                                                <h2 class="title"><?php echo esc_html__(esc_attr($homey_local['payment_label']), 'homey'); ?></h2>
                                            </div><!-- block-left -->
                                            <div class="block-right">
                                                <?php
                                                    echo homey_calculate_reservation_cost_day_date_child($reservationID);
                                                ?>
                                            </div><!-- block-right -->
                                        </div><!-- block-body -->
                                    </div><!-- block-section -->
                                <?php }?>
                            </div><!-- .block -->


                            <div class="payment-buttons">
                                <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width'); ?>
                            </div>
                        </div><!-- .dashboard-area -->
                    </div><!-- col-lg-12 col-md-12 col-sm-12 -->
                </div>
            </div><!-- .container-fluid -->
        </div><!-- .dashboard-content-area -->
        <aside class="dashboard-sidebar">
            <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar', '', array("booking_type", $booking_type)); ?>

            <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>

        </aside><!-- .dashboard-sidebar -->
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
