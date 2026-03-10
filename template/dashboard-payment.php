<?php
/**
 * Template Name: Reservation Payment
 */
if ( !is_user_logged_in() ) {
    wp_redirect(  home_url('/') );
}

get_header();
global $current_user;

wp_get_current_user();
$userID = $current_user->ID;

$reservation_id = $reservation_status = '';
if(isset($_GET['reservation_id']) && !empty($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
} 
$offsite_payment = homey_option('off-site-payment');

$enable_paypal = homey_option('enable_paypal');
$enable_stripe = homey_option('enable_stripe');
$stripe_processor_link = homey_get_template_link('template/template-stripe-charge.php');
$is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

$renter_id = get_current_user_id();

$listing_owner = get_post_meta($reservation_id, 'listing_owner', true);

$user_meta = homey_get_author_by_id('100', '100', 'img-circle', $listing_owner);

$payout_payment_method = $user_meta['payout_payment_method'];
$payout_paypal_email = $user_meta['payout_paypal_email'];
$payout_skrill_email = $user_meta['payout_skrill_email'];

// Beneficiary Information
$ben_first_name = $user_meta['ben_first_name'];
$ben_last_name = $user_meta['ben_last_name'];
$ben_company_name = $user_meta['ben_company_name'];
$ben_tax_number = $user_meta['ben_tax_number'];
$ben_street_address = $user_meta['ben_street_address'];
$ben_apt_suit = $user_meta['ben_apt_suit'];
$ben_city = $user_meta['ben_city'];
$ben_state = $user_meta['ben_state'];
$ben_zip_code = $user_meta['ben_zip_code'];

//Wire Transfer Information
$bank_account = $user_meta['bank_account'];
$swift = $user_meta['swift'];
$bank_name = $user_meta['bank_name'];
$wir_street_address = $user_meta['wir_street_address'];
$wir_aptsuit = $user_meta['wir_aptsuit'];
$wir_city = $user_meta['wir_city'];
$wir_state = $user_meta['wir_state'];
$wir_zip_code = $user_meta['wir_zip_code'];
?>

<section id="body-area">

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-with-sidebar" style="padding-top: 120px;">
        <?php
        // $reservation_id
        $listing_id = get_post_meta($reservation_id,'reservation_listing_id',true);   
        $listing_title = get_the_title($listing_id);
        $listing_image_id = get_post_meta($listing_id,'homey_listing_images',true);
        $listing_image_url = wp_get_attachment_image_url($listing_image_id, 'full');

        $booking_dates = get_post_meta($reservation_id, 'reservation_booking_dates', true);
        $total_hours = get_post_meta($reservation_id, 'reservation_total_hours', true);
        $guests = get_post_meta($reservation_id, 'reservation_guests', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);

        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $prices_array = homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options);

        $nights_total_price_li_html = '';
        $price_per_night = $prices_array['price_per_night'];
        $no_of_days = $prices_array['days_count'];

        $guest_price = $prices_array['guest_price'];

        $nights_total_price = $price_per_night * $total_hours;

        $services_fee = $prices_array['services_fee'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $city_fee = homey_formatted_price($prices_array['city_fee']);
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];

        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];
        $with_weekend_label = $local['with_weekend_label'];

        $extra_prices_html = $prices_array['extra_prices_html'];
        $total_price = $prices_array['total_price'];

        ?>
        <div class="reservation-renter-pays">
            <div class="reservation-payment-details" style="width:50%;">
                <h2><?php esc_html_e('Pay through Stripe','homey-child');?></h2>
                <div class="reservation-payment-details-inner" style="width:100%;">
                    <div class="reservation-payment-details-inner-left">
                        <img width="120" src="<?php echo $listing_image_url; ?>" alt="<?php echo $listing_title; ?>">
                    </div>
                    <div class="reservation-payment-details-inner-right" style="width:100%;">
                        <h3 class="mb-0"><?php echo $listing_title; ?></h3>
                        <?php 
                        $reservation_type = get_post_meta($reservation_id, 'reservation_type', true);
                        if($reservation_type == 'overtime_policy'){

                            $price_per_night = get_post_meta($reservation_id, 'reservation_overtime_price_per_hour', true);
                            $total_hours = get_post_meta($reservation_id, 'reservation_overtime_hours', true);
                            $total_price = get_post_meta($reservation_id, 'reservation_total', true);

                            ?>
                                <ul>
                                    <li>Total Price <b><?php echo homey_formatted_price($total_price); ?></b></li>
                                    <li>
                                        <?php echo homey_formatted_price($price_per_night, true) . ' x ' . esc_attr($total_hours) . ' Hours'; ?>
                                        <span><?php echo homey_formatted_price($total_price, true); ?></span>
                                    </li>
                                </ul>
                            <?php
                        }else{
                            ?>
                            <ul>
                                <li>Total Price <b><?php echo homey_formatted_price($total_price); ?></b></li>
                                <li>
                                    <?php echo homey_formatted_price($price_per_night, true) . ' x ' . esc_attr($total_hours) . ' Hours'; ?>
                                    <span><?php echo homey_formatted_price($nights_total_price, true); ?></span>
                                </li>
                                <?php
                                    if (!empty($extra_prices_html)) {
                                        echo $extra_prices_html;
                                    }

                                    if(!empty($taxes) && $taxes > 0){
                                        echo '<li>Services Fee <span>' . homey_formatted_price($taxes, false) . '</span></li>';
                                    }
                                    ?>
                            </ul>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="stripe-payment-details-form" style="background-color: #f5f5f5; width:50%; padding: 20px; border-radius: 8px;">
                <?php
                // Check for payment success message
                $success_data = get_transient('renter_payment_success_' . $reservation_id);
                if ($success_data && isset($_GET['payment']) && $_GET['payment'] === 'success') {
                    delete_transient('renter_payment_success_' . $reservation_id);
                    $amount = isset($success_data['amount']) ? $success_data['amount'] : 0;
                    ?>
                    <div class="payment-success-message" style="background-color: #d4edda; color: #155724; padding: 20px; border-radius: 8px; text-align: center;">
                        <h3 style="color: #155724; margin-bottom: 10px;">
                            <i class="homey-icon homey-icon-check-circle-1" style="color: #28a745;"></i> <?php esc_html_e('Payment Completed Successfully!','homey-child');?>
                        </h3>
                        <p style="margin-bottom: 5px; font-size: 16px;">
                            <?php printf(esc_html__('Your payment of %s has been processed.','homey-child'), '<strong>'.homey_formatted_price($amount).'</strong>'); ?>
                        </p>
                        <p style="margin-bottom: 5px; font-size: 14px; color: #6c757d;">
                            <?php esc_html_e('A temporary hold has been placed on your account. Once the host confirms your booking, your reservation will be finalized and the full amount will be charged. If the host does not approve within 24 hours, the hold will be released back to your account and the reservation will be automatically canceled.','homey-child');?>
                        </p>
                        <p style="margin-bottom: 0; font-size: 14px; color: #6c757d;">
                            <?php esc_html_e('You can check the Reservations and Messages tabs in your dashboard for real-time updates and any communication from the host.','homey-child');?>
                        </p>
                    </div>
                    <script>
                        jQuery(document).ready(function($) {
                            // Hide form
                            $("#stripe-payment-form").hide();
                            
                            // Clean URL (remove query parameters)
                            if (window.history && window.history.pushState) {
                                var cleanUrl = window.location.pathname + "?reservation_id=<?php echo $reservation_id; ?>";
                                window.history.pushState({path: cleanUrl}, "", cleanUrl);
                            }
                        });
                    </script>
                    <?php
                } else {

                    $reservation_payment_status = get_post_meta($reservation_id, 'reservation_payment_status', true);

                    if(!empty($reservation_payment_status) && $reservation_payment_status == 'paid'){

                        // page explired
                        ?>
                        <div class="payment-success-message" style="background-color: #d4edda; color: #155724; padding: 20px; border-radius: 8px; text-align: center;">
                            <h3 style="color: #155724; margin-bottom: 10px;">
                                <i class="homey-icon homey-icon-exclamation-circle" style="color: #dc3545;"></i> <?php esc_html_e('Payment Paid!','homey-child');?>
                            </h3>
                            <p style="margin-bottom: 0; font-size: 14px; color: #6c757d;">
                                <?php esc_html_e('You already pay for this booking and page is expired for now. You can not pay again for this booking.','homey-child');?>
                            </p>
                        </div>
                        <?php

                    }else{
                    ?>
                    <form id="stripe-payment-form">
                        <div id="payment-form-errors" style="display: none; background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;"></div>
                        
                        <!-- Email Section -->
                        <div class="form-section" style="margin-bottom: 10px;">
                            <h3 style="font-weight: bold; margin-bottom: 5px; color: #333;">Email</h3>
                            <div class="form-group">
                                <?php
                                $current_user = wp_get_current_user();
                                $email = $current_user->user_email;
                                if(!empty($email)){
                                    ?>
                                    <input type="email" id="payment_email" name="payment_email" class="form-control payment-field" value="<?php echo $email; ?>" placeholder="email@example.com" style="border-radius: 8px; border: 1px solid #ddd; padding: 12px 15px; height: auto;">
                                    <?php
                                }else{
                                    ?>
                                    <input type="email" id="payment_email" name="payment_email" class="form-control payment-field" placeholder="email@example.com" style="border-radius: 8px; border: 1px solid #ddd; padding: 12px 15px; height: auto;">
                                    <?php
                                }
                                ?>
                                <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                            </div>
                        </div>

                        <!-- Shipping Address Section -->
                        <!-- <div class="form-section" style="margin-bottom: 10px;">
                            <h3 style="font-weight: bold; margin-bottom: 5px; color: #333;">Shipping address</h3>
                            <div style="background-color: white; border-radius: 8px; padding: 15px;">
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <input type="text" id="full_name" name="full_name" class="form-control payment-field" placeholder="Full name" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                    <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <input type="text" id="country" name="country" class="form-control payment-field" placeholder="Add Country" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                    <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <input type="text" id="address_line1" name="address_line1" class="form-control payment-field" placeholder="Address line 1" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                    <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <input type="text" id="address_line2" name="address_line2" class="form-control" placeholder="Address line 2" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                </div>
                                <div class="row" style="margin-bottom: 15px;">
                                    <div class="col-xs-6" style="padding-right: 10px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <input type="text" id="city" name="city" class="form-control payment-field" placeholder="City" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                            <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6" style="padding-left: 10px;">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <input type="text" id="zip" name="zip" class="form-control payment-field" placeholder="ZIP" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                            <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <input type="text" id="state" name="state" class="form-control payment-field" placeholder="State" style="border: none; border-bottom: 1px solid #ddd; border-radius: 0; padding: 12px 0; box-shadow: none; margin-bottom:0px;">
                                    <span class="field-error" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></span>
                                </div>
                            </div>
                        </div> -->

                        <!-- Payment Method Section -->
                        <div class="form-section">
                            <h3 style="font-weight: bold; margin-bottom: 5px; color: #333;">Payment method</h3>
                            <div style="background-color: white; border-radius: 8px; padding: 15px;">
                                <p style="font-size: 14px; color: #666; margin-bottom: 0; text-align: center;">
                                    You will be redirected to Stripe's secure payment page to complete your payment.
                                </p>
                            </div>
                        </div>
                        
                        <input type="hidden" class="reservation_id" name="reservation_id" value="<?php echo $reservation_id;?>" />
                        <input type="hidden" class="renter_id" name="renter_id" value="<?php echo $renter_id; ?>" />
                        <input type="hidden" class="owner_id" name="owner_id" value="<?php echo $listing_owner; ?>" />
                        <button type="submit" class="btn btn-primary renter-pay-reservation-amount btn-full-width mt-15 mb-0" style="font-size: 15px; padding: 7px 0px;"><?php esc_html_e('Pay Now','homey-child');?></button>
                    </form>
                    <?php 
                    }
                } 
                ?>
            </div>
        </div>  
        
    </div><!-- .user-dashboard-right -->

</section><!-- #body-area -->

<?php get_footer();?>
