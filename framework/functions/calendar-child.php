<?php
function homeyHourlyAvailabilityCalendarChild($homey_timeperiod_availbility, $listing_id) {
    $numberOfMonths = 1;
    $timeNow  = current_time( 'timestamp' );
    $now = date('Y-m-d');
    $date = new DateTime();
    $disabled_days = array();
    
    $currentMonth = gmdate('m', $timeNow);
    $currentYear  = gmdate('Y', $timeNow);             
    $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);


    if(!empty($homey_timeperiod_availbility)) {
        $availability = is_string($homey_timeperiod_availbility) ? unserialize($homey_timeperiod_availbility) : $homey_timeperiod_availbility;
        
        // Map days of week to numbers (0=Sunday, 6=Saturday)
        $day_map = array(
            'sun' => 0,
            'mon' => 1, 
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        );
        
        // Check each day and add to disabled array if not available
        foreach($day_map as $day_key => $day_num) {
            if(isset($availability[$day_key.'_available']) && $availability[$day_key.'_available'] == 'no') {  
                $disabled_days[] = $day_num;
            }
        }
    }

    echo "<div style='position: absolute; right: 10px; top: 10px;' class='pull-right' id='calendar-cross-btn'><i id='calendar-cross-btn-i' class='homey-icon homey-icon-close'></i></div>";

    while( $numberOfMonths <= homey_calendar_months() ) {
        homeyHourlyGenerateMonthChild( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $disabled_days, $listing_id );
      
        $date->modify( 'first day of next month' );
        $currentMonth = $date->format( 'm' );
        $currentYear  = $date->format( 'Y' );
        $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        $numberOfMonths++;
    }
}

function homeyHourlyGenerateMonthChild( $numberOfMonths, $unixMonth, $currentMonth, $currentYear, $disabled_days, $listing_id ) {
    global $wpdb, $post, $wp_locale;

    $bookedDays  = get_post_meta($post->ID, 'reservation_dates',true  ); 
    $pending_dates  = get_post_meta($post->ID, 'reservation_pending_dates',true  );

    if(empty($bookedDays)) {
        $bookedDays = array();
    }

    if(empty($pending_dates)) {
        $pending_dates = array(); 
    }

    // Get already booked dates and add to disabled days
    $already_booked_days = array();
    $already_booked_dates = homey_get_reservation_dates_by_reservation_id($listing_id);
    if(!empty($already_booked_dates)){
        foreach($already_booked_dates as $already_booked_date){
            $date_obj = DateTime::createFromFormat('m-d-Y', $already_booked_date);
            if($date_obj !== false) {
                $already_booked_days[] = $date_obj->format('Y-m-d');
            }
        }
    }

    $daysInMonth = homeyHourlyDaysInMonth($currentMonth, $currentYear);
    $weekBegins = intval(homey_option('weekBegins', 1));
    $weekArray = array();
    $weekDays = '';
    $monthDays = '';
    $weekDayInitial = true;
    $prevMonthDays = '';
    $calendar_day_class = '';
    $resv_class = '';
    $resv_start = '';
    $resv_end = '';

    $main_class = 'hourly-calendar';

    $style = "";
    if( $numberOfMonths > 1 ) {
        $style = 'style="display:none;"';
    }

    for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
        $weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
    }

    foreach ( $weekArray as $weekDay ) {
        $dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
        $weekDays .= '<li data-dayName = "'.esc_attr($weekDay).'">'.esc_attr($dayName).'</li>';
    }

    $weekMod = calendar_week_mod(date('w', $unixMonth) - $weekBegins);
    if( $weekMod != 0 ) {
        for( $wm = 1; $wm <= $weekMod; $wm++ ) {
            $prevMonthDays .= '<li class="prev-month"></li>';
        }
    }

    for ( $day = 1; $day <= $daysInMonth; ++$day ) {
        $timestamp = strtotime( $day.'-'.$currentMonth.'-'.$currentYear);
        $dayOfWeek = date('w', $timestamp);
        $current_date = date('Y-m-d', $timestamp);

        $dayClass = '';
        $resv_class = '';
        $calendar_day_class = '';

        // Check if day is in the past
        if( $timestamp < (time()-24*60*60) ) {
            $dayClass = "day-disabled past-day";
        } else {
            $dayClass = "future-day";
        }

        // Check if day is booked or pending
        if( array_key_exists($timestamp, $bookedDays) ) {
            $calendar_day_class = 'day-booked homey-not-available-for-booking';
            $resv_end = 1;
            if($resv_start == 1){
                $resv_class = 'reservation_start';
                $resv_start = 0;
            }
        } elseif( array_key_exists($timestamp, $pending_dates) ) {
            $calendar_day_class = 'day-pending homey-not-available-for-booking';
            $resv_end = 1;
            if($resv_start == 1){
                $resv_class = 'reservation_start';
                $resv_start = 0;
            }
        } else {
            $calendar_day_class = 'day-available';
            $resv_start = 1;
            if($resv_end === 1){
                $resv_class = 'reservation_end';
                $resv_end = 0;
            }
        }

        // Check if day is in disabled_days array or already booked
        if(in_array($dayOfWeek, $disabled_days) || in_array($current_date, $already_booked_days)) {
            $calendar_day_class = 'day-unavailable homey-not-available-for-booking';
            $dayClass .= ' disabled-day';
        }

        $dateTimeStamp = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
        $dateTimeStamp = $dateTimeStamp->getTimestamp();
        $homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

        // Add data attributes for disabled days
        $disabled_attr = (in_array($dayOfWeek, $disabled_days) || in_array($current_date, $already_booked_days)) ? ' data-disabled="true"' : '';

        if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {
            $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'"'.$disabled_attr.' class="current-month current-day '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
        } else {
            $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'"'.$disabled_attr.' class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
                <span class="day-number">'.esc_attr($day).'</span>
            </li>';
        }
    }

    $output = '<div class="single-listing-hourly-calendar-wrap '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';
        $output .= '<div class="month clearfix">';
            $output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).' <span>'.esc_attr($currentYear).'</span></h4>';
        $output .= '</div>';

        $output .= '<ul class="weekdays clearfix">';
            $output .= $weekDays;
        $output .= '</ul>';

        $output .= '<ul class="days clearfix">';
            $output .= $prevMonthDays;
            $output .= $monthDays;
        $output .= '</ul>';
    $output .= '</div>';

    echo ''.$output;
}