<?php 
global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = homey_get_price_by_id($listing_id); //get_post_meta($listing_id, $homey_prefix.'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix.'instant_booking', true);
$offsite_payment = homey_option('off-site-payment');
$guest_message = isset($_GET['guest_message']) ? $_GET['guest_message'] : '';
$rating = homey_option('rating');
$total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );
$listing_total_number_of_reviews = update_post_meta($listing_id, 'listing_total_number_of_reviews', '0');

$total_rating_for_html = $listing_total_number_of_reviews;

$prefilled = homey_get_dates_for_booking();

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'homey-icon-love-it';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'homey-icon-love-it';
}

if($instant_booking && $offsite_payment == 0) {
	$btn_name = esc_html__('Instant Booking', 'homey');
} else {
	$btn_name = esc_html__('Request to Book', 'homey');
}
$booking_or_contact_theme_options = homey_option('what_to_show');
$booking_or_contact = homey_get_listing_data('booking_or_contact');
if(empty($booking_or_contact)) {
    $what_to_show = $booking_or_contact_theme_options;
} else {
    $what_to_show = $booking_or_contact;
}

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

$listing_price = homey_get_price();

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
if(wp_is_mobile()):
?>
<div id="overlay-booking-module" class="overlay-booking-module overlay-contentscale">
	<div class="overlay-search-title">
        <?php 
		// if($instant_booking && $offsite_payment == 0 ) {
        //     echo esc_html__('Request to book', 'homey');
        // } else {
        //     echo esc_html__('Instant Book', 'homey');
        // } 
		echo esc_html__('Request to book', 'homey');
		?>
    </div>
	<button type="button" class="overlay-booking-module-close btn-blank"><i class="homey-icon homey-icon-close" aria-hidden="true"></i></button>
	<div class="sidebar-booking-module">
		<div class="block">
			<!-- <div class="sidebar-booking-module-body">
				<div class="homey_notification search-wrap search-banner">

					<div id="single-listing-date-range" class="search-date-range">
						<div class="search-date-range-arrive">
							<input name="arrive" value="<?php //echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
						</div>
						<div class="search-date-range-depart">
							<input name="depart" value="<?php //echo esc_attr($prefilled['depart']); ?>" readonly type="text" class="form-control check_out_date" placeholder="<?php echo esc_attr(homey_option('srh_depart_label')); ?>">
						</div>
						<div id="single-booking-search-calendar" class="search-calendar single-listing-booking-calendar-js clearfix" style="display: none;">
							<?php //homeyAvailabilityCalendar(); ?>
							
							<div class="calendar-navigation custom-actions">
		                        <button class="listing-cal-prev btn btn-action pull-left disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
		                        <button class="listing-cal-next btn btn-action pull-right"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
		                    </div>               
						</div>
					</div>
					
					<?php //get_template_part('single-listing/booking/guests'); ?>

					<?php //get_template_part('single-listing/booking/extra-prices'); ?>

					<?php //if(!$instant_booking) { ?>
					<div class="search-message">
						<textarea name="guest_message" class="form-control" rows="3" placeholder="<?php //echo esc_html__('Introduce yourself to the host', 'homey'); ?>"><?php //echo $guest_message; ?></textarea>
					</div>
					<?php //} ?>

                    	

					<div id="homey_booking_cost" class="payment-list"></div>	
					
					<?php //if($instant_booking && $offsite_payment == 0 ) { ?>
						<button id="instance_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php //echo esc_html__('Instant Booking', 'homey'); ?></button>
					<?php //} else { ?>
						<button id="request_for_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php //echo esc_html__('Request to Book', 'homey'); ?></button>
						<div class="text-center text-small"><i class="homey-icon homey-icon-information-circle"></i> <?php //echo esc_html__("You won't be charged yet", 'homey'); ?></div>
					<?php //} ?>

				</div>
			</div> -->
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
							<button id="request_for_reservation" type="button"
									class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
							<div class="text-center text-small"><i
										class="homey-icon homey-icon-information-circle"></i> <?php echo esc_html__("You won't be charged yet", 'homey'); ?>
							</div>
						<?php } ?>
					<?php } ?>
				</div><!-- block-body-sidebar -->
       		</div><!-- sidebar-booking-module-body -->
		</div><!-- block -->
	</div><!-- sidebar-booking-module -->
</div><!-- overlay-booking-module -->

<div class="overlay-booking-btn visible-sm visible-xs">
	<div class="pull-left">
		<div class="overlay-booking-price">
			<?php echo homey_formatted_price($price_per_night, true, false); ?><span><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></span>
		</div>
		<?php 
        if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
		<div class="list-inline rating">
			<?php echo homey_get_review_stars($total_rating, false, true, true, $total_rating_for_html); ?>
		</div>
		<?php } ?>
	</div>

	<?php

    if( homey_affiliate_booking_link() ) { ?>
		
		<a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank" class="trigger-overlay btn btn-primary"><?php echo esc_html__(
			'Book Now', 'homey'); ?></a>

	<?php	
	} else {
		if($what_to_show == 'booking_form') { ?>
	        <button id="trigger-overlay-booking-form" class="trigger-overlay btn btn-primary" type="button"><?php echo esc_attr($btn_name); ?></button>
	    <?php     
	    } elseif($what_to_show == 'contact_form') { ?>
	        <button type="button" data-toggle="modal" data-target="#modal-contact-host" class="trigger-overlay btn btn-primary"><?php echo esc_html__('Request Information', 'homey'); ?></button>
	    <?php    
	    }elseif($what_to_show == 'contact_form_to_guest') {
            if(is_user_logged_in()){ ?>
                <button id="trigger-overlay-booking-form" class="trigger-overlay btn btn-primary" type="button"><?php echo esc_attr($btn_name); ?></button>
            <?php }else{ ?>
                <button type="button" data-toggle="modal" data-target="#modal-contact-host" class="trigger-overlay btn btn-primary"><?php echo esc_html__('Request Information', 'homey'); ?></button>
            <?php }
        }
	} ?>
</div>
<?php endif;?>