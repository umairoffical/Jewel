<?php
global $listing_id;
$id = $args['id'];
$listing_id = $args['listing_id'];
$homey_timeperiod_availbility = get_post_meta($listing_id, 'homey_timeperiod_availbility', true);
?>
<div class="single-date-hour" style="position: relative;">
    <div class="choose-date">
        <div id="single-listing-date-range" class="search-date-range">
            <div class="search-date-range-arrive search-date-hourly-arrive">
                <input id="hourly_check_inn" name="booking_dates[<?php echo $id; ?>][arrive]" value="" readonly type="text" class="form-control check_in_date check_in_arrive_date" autocomplete="off" placeholder="<?php esc_html_e('Pick Date','homey-child'); ?>">
            </div>
            
            <div id="single-booking-search-calendar" class="search-calendar search-calendar-single clearfix single-listing-booking-calendar-js hourly-js-desktop clearfix" style="display: none;">
                <?php homeyHourlyAvailabilityCalendarChild($homey_timeperiod_availbility, $listing_id); ?>

                <div class="calendar-navigation custom-actions">
                    <button class="listing-cal-prev btn btn-action pull-left disabled"><i class="homey-icon homey-icon-arrow-left-1" aria-hidden="true"></i></button>
                    <button class="listing-cal-next btn btn-action pull-right"><i class="homey-icon homey-icon-arrow-right-1" aria-hidden="true"></i></button>
                </div><!-- calendar-navigation -->	                
            </div>
        </div>
    </div>
    <div class="choose-hours">
        <div class="choose-start-hours">
            <select name="booking_dates[<?php echo $id; ?>][start_hour]" id="start_hour" class="start_hour form-control" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
                <option value=""><?php echo homey_option('srh_starts_label'); ?></option>
            </select>
        </div>
        <div class="choose-end-hours">
            <select name="booking_dates[<?php echo $id; ?>][end_hour]" id="end_hour" class="end_hour form-control" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
                <option value=""><?php echo homey_option('srh_ends_label'); ?></option>
            </select>
        </div>
    </div>
    <a class="remove-booking-hour-row">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </a>
</div>