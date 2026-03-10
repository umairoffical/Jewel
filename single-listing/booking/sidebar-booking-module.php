<?php
global $post, $current_user, $homey_prefix, $homey_local, $listing_id;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix . 'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix . 'instant_booking', true);
$offsite_payment = homey_option('off-site-payment');
$start_hour = get_post_meta($listing_id, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($listing_id, $homey_prefix.'end_hour', true);

$key = '';
$userID = $current_user->ID;
$fav_option = 'homey_favorites-' . $userID;
$fav_option = get_option($fav_option);

if (!empty($fav_option)) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if ($key != false || $key != '') {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'homey-icon-love-it-full-01';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'homey-icon-love-it'; //homey-icon-love-it //fa-heart-o
}

$listing_price = homey_get_price();

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

$prefilled = homey_get_dates_for_booking();
$pre_start_hour = $prefilled['start'];
$pre_end_hour = $prefilled['end'];

if(empty($start_hour)) {
	$start_hour = '01:00';
}

if(empty($end_hour)) {
	$end_hour = '24:00';
}
$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);
// full hour
for ($hour = $start_hour; $hour <= $end_hour; $hour = $hour+60*60) {
    $start_hours_list .= '<option '.selected($pre_start_hour, date('H:i',$hour), false).' value="'.date('H:i',$hour).'">'.date(homey_time_format(),$hour).'</option>';
    $end_hours_list .= '<option '.selected($pre_end_hour, date('H:i',$hour), false).' value="'.date('H:i',$hour).'">'.date(homey_time_format(),$hour).'</option>';
}

// Half Hour
// for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
//     $start_hours_list .= '<option '.selected($pre_start_hour, date('H:i',$halfhour), false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
//     $end_hours_list .= '<option '.selected($pre_end_hour, date('H:i',$halfhour), false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
// }

if(!wp_is_mobile()):
?>
<div id="homey_remove_on_mobile" class="sidebar-booking-module hidden-sm hidden-xs">
    <div class="block">
        <div class="sidebar-booking-module-header">
            <div class="block-body-sidebar">

                <?php
                if (!empty($listing_price)) { ?>

                    <span class="item-price">
					$<span class="each-tier-value"><?php echo $listing_price; ?></span><sub><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label(); ?></sub>
					</span>

                <?php } else {
                    echo '<span class="item-price free">' . esc_html__('Free', 'homey') . '</span>';
                } ?>

            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-header -->
        <div class="sidebar-booking-module-body">
            <div class="homey_notification block-body-sidebar">

                <?php
                if (homey_affiliate_booking_link()) { ?>

                    <a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank"
                       class="btn btn-full-width btn-primary"><?php echo esc_html__('Book Now', 'homey'); ?></a>

                    <?php
                } else { ?>

                    <?php get_template_part('single-listing/booking/choose-hours'); ?>
                    <?php get_template_part('single-listing/booking/guests'); ?>
                    <?php get_template_part('single-listing/booking/extra-prices'); ?>

                    <?php if ($offsite_payment == 0) { ?>
                        <div class="search-message">
                            <textarea name="guest_message" class="form-control guest-message" rows="3"
                                      placeholder="<?php echo esc_html__('Introduce yourself to the host', 'homey'); ?>"></textarea>
                        </div>
                    <?php } ?>

                    <?php if (!is_user_logged_in() && $no_login_needed_for_booking == 'yes') { ?>
                        <div class="new_reser_request_user_email mt-10">
                            <input id="new_reser_request_user_email" name="new_reser_request_user_email"
                                   required="required"
                                   value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>"
                                   type="email" class="form-control new_reser_request_user_email"
                                   placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
                        </div>
                    <?php } ?>

                    <div class="homey_preloader">
                        <?php get_template_part('template-parts/spinner'); ?>
                    </div>

                    <div id="homey_booking_cost" class="payment-list"></div>

                    <input type="hidden" name="listing_id" id="listing_id" value="<?php echo intval($listing_id); ?>">
                    <input type="hidden" name="reservation-security" id="reservation-security"
                           value="<?php echo wp_create_nonce('reservation-security-nonce'); ?>"/>

                    <?php if ($instant_booking && $offsite_payment == 0) { ?>
                        <button id="instance_reservation" type="button"
                                class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
                    <?php } else { ?>
                        <?php if(homey_is_renter() || !is_user_logged_in()):?>
                            <button id="request_for_reservation" type="button"
                                class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
                        <?php else:?>
                            <span style="display: block; width: 100% !important; background-color: black; color: white; font-weight: 600; padding: 10px; text-align: center; border-radius: 6px; margin-bottom: 10px;"><?php esc_html_e("NOTE: Only Renter can make bookings!", 'homey-child');?></span>
                        <?php endif; ?>
                        <div class="text-center text-small"><i
                                    class="homey-icon homey-icon-information-circle"></i> <?php echo esc_html__("You won't be charged yet", 'homey'); ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-body -->

    </div><!-- block -->
</div><!-- sidebar-booking-module -->
<div class="sidebar-booking-module-footer">
    <div class="block-body-sidebar">

        <?php if (homey_option('detail_favorite') != 0) { ?>
            <button type="button" data-listid="<?php echo intval($post->ID); ?>"
                    class="add_fav btn btn-full-width btn-grey-outlined"><i class="homey-icon <?php echo esc_attr($heart); ?>"
                                                                            aria-hidden="true"></i> <?php echo esc_attr($favorite); ?>
            </button>
        <?php } ?>

        <?php if (homey_option('detail_contact_form') != 0 && homey_option('hide-host-contact') != 1) { ?>
            <?php if(is_user_logged_in()):?>
                <button type="button" data-toggle="modal" data-target="#modal-contact-host"
                    class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
            <?php else : ?>
                <button type="button" data-toggle="modal" data-target="#modal-register"
                    class="btn btn-full-width btn-grey-outlined"><?php echo esc_attr($homey_local['pr_cont_host']); ?></button>
            <?php endif; ?>
        <?php } ?>

        <?php if (homey_option('print_button') != 0) { ?>
            <button type="button" id="homey-print" class="homey-print btn btn-full-width btn-blank"
                    data-listing-id="<?php echo intval($listing_id); ?>">
                <i class="homey-icon homey-icon-print-text" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
            </button>
        <?php } ?>
    </div><!-- block-body-sidebar -->

    <?php
    if (homey_option('detail_share') != 0) {
        get_template_part('single-listing/share');
    }
    ?>
</div><!-- sidebar-booking-module-footer -->
<?php endif; ?>
