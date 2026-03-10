<?php
global $homey_local, $dashboard_invoices, $current_user;
wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$user_address = get_user_meta( $userID, 'homey_street_address', true);

$invoice_id = $_GET['invoice_id'];
$post = get_post( $invoice_id );
$invoice_data = homey_get_invoice_meta( $invoice_id );
$invoice_item_id = $invoice_data['invoice_item_id'];

$reservationID = $invoice_item_id;

$publish_date = $post->post_date;
$publish_date = date_i18n( homey_convert_date(homey_option('homey_date_format')), strtotime( $publish_date ) );
$invoice_logo = homey_option( 'invoice_logo', false, 'url' );
$invoice_company_name = homey_option( 'invoice_company_name' );
$invoice_additional_info = homey_option( 'invoice_additional_info' );

$homey_invoice_buyer = get_post_meta( $invoice_id, 'homey_invoice_buyer', true );

$user_info = get_userdata($homey_invoice_buyer);
$user_phone = get_user_meta( $homey_invoice_buyer, 'phone', true);

$user_email     = isset($user_info->user_email)?$user_info->user_email:'-';
$first_name     = isset($user_info->first_name)?$user_info->first_name:'-';
$last_name      = isset($user_info->last_name)?$user_info->last_name:'-';
$resv_id = $invoice_item_id;

$is_hourly = get_post_meta($resv_id, 'is_hourly', true);

if( !empty($first_name) && !empty($last_name) ) {
    $fullname = $first_name.' '.$last_name;
} else {
    $fullname = $user_info->display_name;
}

$is_reservation_invoice = false;
if($invoice_data['invoice_billion_for'] == 'reservation') {
    $is_reservation_invoice = true;
}

if($invoice_data['invoice_billion_for'] == 'reservation') {

    $billing_for_text = $homey_local['resv_fee_text'];

} elseif($invoice_data['invoice_billion_for'] == 'listing') {
    if( $invoice_data['upgrade'] == 1 ) {
        $billing_for_text =  $homey_local['upgrade_text'];

    } else {
        $billing_for_text =  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
    }
} elseif($invoice_data['invoice_billion_for'] == 'upgrade_featured') {
    $billing_for_text =  $homey_local['upgrade_text'];

} elseif($invoice_data['invoice_billion_for'] == 'package') {
    $billing_for_text =  $homey_local['inv_package'];
}
$logged_in_user = get_current_user_id();

$listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
$total_hours = get_post_meta($reservationID, 'reservation_total_hours', true);
$guests_range = homey_get_total_guests_range($reservationID, $listing_id);
$cancellation_policy = homey_option('cancellation_policy_text');
$overtime_policy = homey_option('overtime_policy_text');
$site_rep_name = get_post_meta($listing_id, 'homey_rep_name', true);
$welcome_message = get_post_meta($listing_id, 'homey_instructions', true);
// Get the location rules and remove HTML tags (for nice, clean display in <ul><li>)
$location_rules_raw = get_post_meta($listing_id, 'homey_additional_rules', true);
$location_rules = wp_strip_all_tags($location_rules_raw);
$booking_dates = get_post_meta($reservationID, 'reservation_booking_dates', true);
?>
<div class="invoice-detail block">
    <?php
    if($fullname != '- -' || homey_is_admin()){
        if(homey_is_admin() || $invoice_data['invoice_resv_owner'] == $logged_in_user
            || $invoice_data['invoice_buyer_id'] == $logged_in_user){ ?>
            <div class="invoice-header clearfix">
                <div class="block-left">
                    <div class="invoice-logo">
                        <?php if( !empty($invoice_logo) ) { ?>
                            <img src="<?php echo esc_url($invoice_logo); ?>" alt="<?php esc_attr_e('logo', 'homey');?>">
                        <?php } ?>
                    </div>
                    <ul class="list-unstyled">
                        <?php if( !empty($invoice_company_name) ) { ?>
                            <li><strong><?php echo ($invoice_company_name); ?></strong></li>
                        <?php } ?>
                        <li><?php echo homey_option( 'invoice_address' ); ?></li>
                    </ul>
                </div>
                <div class="block-right">
                    <ul class="list-unstyled">
                        <li><strong><?php esc_html_e('Invoice:', 'homey'); ?></strong> <?php echo esc_attr($invoice_id); ?></li>
                        <li><strong><?php esc_html_e('Date:', 'homey'); ?></strong> <?php echo esc_attr($publish_date); ?></li>
                    </ul>
                </div>
            </div><!-- invoice-header -->

            <div class="invoice-body clearfix">
                <ul class="list-unstyled">
                    <li><strong><?php echo esc_html__('To:', 'homey'); ?></strong></li>
                    <li><?php echo esc_attr($fullname); ?></li>
                    <li><?php echo esc_html__('Email:', 'homey'); ?> <?php echo esc_attr($user_email);?></li>
                    <li><?php echo esc_html__('Phone:', 'homey'); ?> <?php echo esc_attr($user_phone);?></li>
                </ul>


                <!-- <h2 class="title"><?php //esc_html_e('Details', 'homey'); ?></h2> -->

                <?php
                // if($is_reservation_invoice) {
                //     $resv_id = $invoice_item_id;
                //     if($is_hourly == 'yes') {
                //         echo homey_calculate_hourly_reservation_cost($resv_id);
                //     } else {
                //         echo homey_calculate_reservation_cost($resv_id);
                //     }

                // } else {
                //     echo '<div class="payment-list"><ul>';
                //     echo '<li>'.$homey_local['billing_for'].' <span>'.$billing_for_text.'</span></li>';
                //     echo '<li>'.$homey_local['billing_type'].' <span>'.esc_html( $invoice_data['invoice_billing_type'] ).'</span></li>';
                //     echo '<li>'.$homey_local['inv_pay_method'].' <span>'.esc_html($invoice_data['invoice_payment_method']).'</span></li>';
                //     $price_is_zero = homey_formatted_price( $invoice_data['invoice_item_price'] );
                //     echo '<li class="payment-due gf">'.$homey_local['inv_total'].' <span>'.$price_is_zero != '' ? $price_is_zero : "0".'</span></li>';
                //     echo '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$invoice_data['invoice_item_price'].'">';

                //     echo '</ul></div>';
                // }
                ?>
                <div class="invoice-details" style="margin-bottom: 15px;">
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e('Details', 'homey'); ?></h2>
                    <ul class="detail-list detail-list-2-cols">
                        <li style="margin-bottom: 0px;">
                            <?php echo esc_html__('Rental Start', 'homey'); ?>:
                            <strong><?php echo date('d-m-y', strtotime(homey_get_booking_start_date($reservationID))); ?></strong>
                        </li>
                        <li style="margin-bottom: 0px;">
                            <?php echo esc_html__('Rental End', 'homey'); ?>:
                            <strong><?php echo date('d-m-y', strtotime(homey_get_booking_end_date($reservationID))); ?></strong>
                        </li>
                        <li style="margin-bottom: 0px;">
                            <?php echo esc_html__('Total Hours', 'homey'); ?>:
                            <strong><?php echo $total_hours; ?></strong>
                        </li>
                        <li style="margin-bottom: 0px;">
                            <?php echo esc_html__('Max Guests', 'homey'); ?>:
                            <strong><?php echo $guests_range; ?></strong>
                        </li>
                    </ul>
                </div><!-- block-right -->

                <?php if(!empty($booking_dates)){?>
                    <div class="reservation-booking-dates" style="margin-bottom: 15px;">
                        <h2 style="margin-bottom: 0px;"><?php esc_html_e('Booking Dates', 'homey'); ?></h2>
                        <?php
                        if (!empty($booking_dates)) {
                            foreach ($booking_dates as $booking_date) {
                                if (!empty($booking_date['arrive_date'])) {
                                    $arrive_date = $booking_date['arrive_date'];
                                    $start_hour = isset($booking_date['start_hour']) ? $booking_date['start_hour'] : '';
                                    $end_hour = isset($booking_date['end_hour']) ? $booking_date['end_hour'] : '';
                                    $formatted_date = date('d-m-y', strtotime($arrive_date));
                                    
                                    // Ensure proper AM/PM formatting
                                    $start_hour_formatted = !empty($start_hour) ? date('g:i A', strtotime($start_hour . ':00')) : 'N/A';
                                    $end_hour_formatted = !empty($end_hour) ? date('g:i A', strtotime($end_hour . ':00')) : 'N/A';
                                    
                                    echo '<p style="margin-bottom: 0px;">Date: ' . esc_html($formatted_date) . ' (Start Time: ' . esc_html($start_hour_formatted) . ' - End Time: ' . esc_html($end_hour_formatted) . ')</p>';
                                }
                            }
                        }
                        ?>
                    </div>
                <?php } ?>

                <?php if(!empty($welcome_message)) { ?>
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e("Instructions"); ?></h2> 
                    <div style="margin-bottom: 15px;">
                        <b><?php echo $site_rep_name; ?></b>
                        <p><?php echo esc_attr($welcome_message); ?></p>
                    </div>
                <?php } ?>

                <?php if(!empty($renter_msg)) { ?>
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e('Notes', 'homey'); ?></h2>
                    <p style="margin-bottom: 15px;"><?php echo esc_attr($renter_msg); ?></p>
                <?php } ?>

                <?php if(!empty($location_rules)) { ?>
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e("Location Rules", "homey-child"); ?></h2>
                    <p style="margin-bottom: 15px;"><?php echo esc_attr($location_rules); ?></p>
                <?php } ?>

                <?php if(!empty($cancellation_policy)) { ?>
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e("Cancellation Policy", "homey-child"); ?></h2>
                    <p style="margin-bottom: 15px;"><?php echo $cancellation_policy; ?></p>
                <?php } ?>

                <?php if(!empty($overtime_policy)) { ?>
                    <h2 style="margin-bottom: 0px;"><?php esc_html_e("Overtime Policy", "homey-child"); ?></h2>
                    <p style="margin-bottom: 15px;"><?php echo $overtime_policy; ?></p>
                <?php } ?>


                <h2 style="margin-bottom: 0px;"><?php echo esc_html__(esc_attr($homey_local['payment_label']), 'homey'); ?></h2>
                <?php echo homey_calculate_reservation_cost_day_date_child($reservationID); ?>

            </div><!-- invoice-body -->

            <?php if( !empty($invoice_additional_info)) { ?>
                <div class="invoice-footer clearfix">
                    <dl>
                        <dt><?php echo esc_html__('Additional Information:', 'homey'); ?></dt>
                        <dd><?php echo homey_option( 'invoice_additional_info' ); ?></dd>
                    </dl>
                </div><!-- invoice-footer -->
            <?php } ?>
        <?php }else{ ?>
            <div class="invoice-body clearfix">
                <h3><?php echo __("You are not allowed to see this."); ?></h3>
            </div>
        <?php }
    }else{ ?>
        <div class="invoice-body clearfix">
            <h3><?php echo __("The Buyer User is deleted, please contact to the admin for this."); ?></h3>
        </div>
    <?php  } ?>
</div>