<?php
/***************************************************************************/
/********   Calculate Booking Cost   *********/
/***************************************************************************/
add_action('wp_ajax_nopriv_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax');
add_action('wp_ajax_homey_calculate_booking_cost', 'homey_calculate_booking_cost_ajax');

if (!function_exists('homey_calculate_booking_cost_ajax')) {
    function homey_calculate_booking_cost_ajax()
    {
        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        $listing_id = intval($_POST['listing_id']);
        $booking_dates = $_POST['booking_dates'];
        $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
        $guests = intval($_POST['guests']);
        $total_hours = intval($_POST['total_hours']);


        $min_booking_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);
        if($min_booking_hours > $total_hours) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Minimum booking is ' . $min_booking_hours . ' hours'
                )
            );
            wp_die();
        }

        if(empty($guests)){
            echo json_encode(
                array(
                    'success' => false,
                    'message' => 'Please Select Guests!'
                )
            );
            wp_die();
        }

        homey_calculate_booking_cost_ajax_day_date_child($listing_id, $booking_dates, $total_hours, $guests, $extra_options);
        wp_die();

    }
}

function homey_calculate_booking_cost_ajax_day_date_child($listing_id, $booking_dates, $total_hours, $guests, $extra_options = null) {

    $prefix = 'homey_';
    $local = homey_get_localization();
    $allowded_html = array();
    $output = '';

    // $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

    $prices_array = homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options);

    $nights_total_price_li_html = $prices_array['nights_total_price_li_html'];
    $nights_total_price_li_html = '';
    $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
    $no_of_days = $prices_array['days_count'];

    $guest_price = $prices_array['guest_price'];

    $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

    $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
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
    $upfront_payment = $prices_array['upfront_payment'];
    $balance = $prices_array['balance'];
    $total_price = $prices_array['total_price'];

    if ($no_of_days > 1) {
        $night_label = homey_option('glc_day_nights_label');
    } else {
        $night_label = homey_option('glc_day_night_label');
    }

    if ($additional_guests > 1) {
        $add_guest_label = $local['cs_add_guests'];
    } else {
        $add_guest_label = $local['cs_add_guest'];
    }

    $output = '<div class="payment-list-price-detail clearfix">';

    if (isset($prices_array['breakdown_price'])) {
        $output .= '<div style="display:none;">' . $prices_array['breakdown_price'] . '</div>';
    }

    $exclude_taxes = 'Does not include taxes';

    $output .= '<div class="pull-left">';
    $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
    $output .= '<div class="payment-list-price-detail-note">' . esc_attr($exclude_taxes) . '</div>';
    $output .= '</div>';

    $output .= '<div class="pull-right text-right">';
    $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
    $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
    $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="collapse collapseExample" id="collapseExample">';
    $output .= '<ul>';

    if ($booking_has_custom_pricing == 1 && $booking_has_weekend == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_and_weekend_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

    } elseif ($booking_has_weekend == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($with_weekend_label) . ') <span>' . $nights_total_price . '</span></li>';

    } elseif ($booking_has_custom_pricing == 1) {
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. esc_attr($no_of_days) . ' ' . esc_attr($night_label) . ' (' . esc_attr($local['with_custom_period_label']) . ') <span>' . esc_attr($nights_total_price) . '</span></li>';

    } else {
        // $output .= '<li>' . $price_per_night . ' x ' . $no_of_days . ' ' . $night_label . ' <span>' . $nights_total_price . '</span></li>';
        if($total_hours > 1){
            $night_label = 'Hours';
        }else{
            $night_label = 'Hour';
        }
        $nights_total_price = $prices_array['total_hours_price'];
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. ($price_per_night) . ' x ' . esc_attr($total_hours) . ' ' . esc_attr($night_label) . ' <span>' . homey_formatted_price($nights_total_price) . '</span></li>';
    }

    // if (!empty($guest_price)) {
    //     $output .= '<li>' . esc_attr('Guest Price') . ' <span>' . homey_formatted_price($guest_price) . '</span></li>';
    // }

    if (!empty($additional_guests)) {
        $output .= '<li>' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
    }

    if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
        $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . ($cleaning_fee) . '</span></li>';
    }

    if (!empty($extra_prices_html)) {
        $output .= $extra_prices_html;
    }

    $services_fee = $services_fee > 0 ? $services_fee : 0;
    $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
    // $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

    if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
        $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . ($city_fee) . '</span></li>';
    }

    if (!empty($security_deposit) && $security_deposit != 0) {
        $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
    }

    if (!empty($services_fee) && $services_fee != 0) {
        $output .= '<li>' . esc_attr($local['cs_services_fee']) . ' <span>' . homey_formatted_price($services_fee) . '</span></li>';
    }

    if (!empty($taxes) && $taxes != 0) {
        $taxes_label = 'Service Fee';
        // . ' (' . esc_attr($taxes_percent) . '%)
        $output .= '<li>' . esc_attr($taxes_label) .' <span>' . homey_formatted_price($taxes) . '</span></li>';
    }

    $avg_price = homey_formatted_price(0);
    if (!empty($upfront_payment) && $upfront_payment != 0) {
        $curncy = homey_get_currency(1);

        $avg_price = $curncy . ' ' . $upfront_payment / $no_of_days;

        $avg_price .= ' <sub> /';
        $avg_price .= esc_html__('Average Night', 'homey');
        $avg_price .= '</sub>';

        $output .= '<li class="payment-due">' . esc_attr($local['cs_payment_due']) . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
        $output .= '<input data-avg-price="' . $avg_price . '" type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
    }

    $output .= '</ul>';
    $output .= '</div>';

    // This variable has been safely escaped in same file: Line: 1071 - 1128
    $output_escaped = $output;
    echo json_encode(
        array(
            'success' => true,
            'message' => 'Booking Available',
            'output' => $output_escaped
        )
    );

    wp_die();

}

function homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options = null) {

    $prefix = 'homey_';

    $enable_services_fee = homey_option('enable_services_fee');
    $enable_taxes = homey_option('enable_taxes');
    $offsite_payment = homey_option('off-site-payment');
    $reservation_payment_type = homey_option('reservation_payment');
    $booking_percent = homey_option('booking_percent');
    $tax_type = homey_option('tax_type');
    $apply_taxes_on_service_fee = homey_option('apply_taxes_on_service_fee');
    $taxes_percent_global = homey_option('taxes_percent');
    $single_listing_tax = get_post_meta($listing_id, 'homey_tax_rate', true);

    // $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

    $period_price = get_post_meta($listing_id, 'homey_custom_period', true);

    if (empty($period_price)) {
        $period_price = array();
    }

    $total_extra_services = 0;
    $extra_prices_html = "";
    $taxes_final = 0;
    $taxes_percent = 0;
    $total_price = 0;
    $total_guests_price = 0;
    $upfront_payment = 0;
    $nights_total_price = 0;
    // $nights_total_price_li_html = '<ul style="display: none;">';
    $booking_has_weekend = 0;
    $booking_has_custom_pricing = 0;
    $balance = 0;
    $taxable_amount = 0;
    $period_days = 0;
    $security_deposit = '';
    $additional_guests = '';
    $additional_guests_total_price = '';
    $services_fee_final = '';
    $taxes_fee_final = '';
    $prices_array = array();

    $listing_guests = floatval(get_post_meta($listing_id, $prefix . 'guests', true));
    $nightly_price = floatval(get_post_meta($listing_id, $prefix . 'night_price', true));
    $price_per_night = $nightly_price;
    $weekends_price = floatval(get_post_meta($listing_id, $prefix . 'weekends_price', true));
    $weekends_days = get_post_meta($listing_id, $prefix . 'weekends_days', true);
    $priceWeek = floatval(get_post_meta($listing_id, $prefix . 'priceWeek', true)); // 7 Nights
    $priceMonthly = floatval(get_post_meta($listing_id, $prefix . 'priceMonthly', true));  // 30 Nights
    $security_deposit = floatval(get_post_meta($listing_id, $prefix . 'security_deposit', true));

    $cleaning_fee = floatval(get_post_meta($listing_id, $prefix . 'cleaning_fee', true));
    $cleaning_fee_type = get_post_meta($listing_id, $prefix . 'cleaning_fee_type', true);

    $city_fee = floatval(get_post_meta($listing_id, $prefix . 'city_fee', true));
    $city_fee_type = get_post_meta($listing_id, $prefix . 'city_fee_type', true);

    $extra_guests_price = floatval(get_post_meta($listing_id, $prefix . 'additional_guests_price', true));
    $additional_guests_price = $extra_guests_price;


    $allow_additional_guests = get_post_meta($listing_id, $prefix . 'allow_additional_guests', true);

    // $check_in = new DateTime($check_in_date);
    // $check_in_unix = $check_in->getTimestamp();
    // $check_in_unix_first_day = $check_in->getTimestamp();
    // $check_out = new DateTime($check_out_date);
    // $check_out_unix = $check_out->getTimestamp();

    // $time_difference = abs(strtotime($check_in_date) - strtotime($check_out_date));
    // $days_count = $time_difference / 86400;
    // $days_count = intval($days_count);
    // $breakdown_price = '';

    // if($check_in_unix == $check_out_unix){
    //     $days_count = 1;
    // }

    $days_count = -1;
    if(!empty($booking_dates) && is_array($booking_dates)){
        foreach ($booking_dates as $date){
            $days_count++;
        }
    }

    $total_hours_price = $price_per_night;
    if(!empty($total_hours) && $total_hours > 0){
        $total_hours_price = $total_hours * $price_per_night;
    }

    // echo "<pre>";
    // print_r($price_per_night);
    // wp_die();

    // if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
    //     $price_per_night = $period_price[$check_in_unix]['night_price'];

    //     $booking_has_custom_pricing = 1;
    //     $period_days = $period_days + 1;
    // }

    // if ($days_count >= 7 && $priceWeek != 0) {
    //     $price_per_night = $priceWeek;
    // }

    // if ($days_count >= 30 && $priceMonthly != 0) {
    //     $price_per_night = $priceMonthly;
    // }

    // Check additional guests price
    // if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
    //     if ($guests > $listing_guests) {
    //         $additional_guests = $guests - $listing_guests;

    //         $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);
    //         $breakdown_price .= ', total_guests_price prev price=' . $total_guests_price . ' + weekend or reg price=' . $guests_price_return . '<br>';

    //         $total_guests_price = $total_guests_price + $guests_price_return;
    //     }
    // }

    // $breakdown_price .= ' * This first date * ' . date('d-m-Y', $check_in_unix) . '<br>';

    // $weekday = date('N', $check_in_unix_first_day);
    // if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
    //     $booking_has_weekend = 1;
    // }

    // if ($booking_has_weekend != 1 && isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {
    //     $returnPrice = $period_price[$check_in_unix]['night_price'];
    // } else {
    //     $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);
    // }

    // // echo  ' first night price= '. $returnPrice.'<br>';
    // $nights_total_price = $nights_total_price + $returnPrice;
    // $html_date_text = date('Y-m-d', $check_in_unix);
    // $nights_total_price_li_html .= '<li class="out-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

    // $check_in->modify('tomorrow');
    // $check_in_unix = $check_in->getTimestamp();

    // $total_price = $total_price + $returnPrice;

    // $total_price = $total_price * $total_hours;

    // $total_hours_price = $total_price;

    // TOTAL GUEST RPICES

    $total_price = $total_hours_price;
    $amount_after_guests = $total_hours_price;

    // $current_index = 0;
    // while ($check_in_unix < $check_out_unix) {
    //     // echo ' * This date * '.date('d-m-Y',$check_in_unix).'<br>';
    //     $current_index++;

    //     $weekday = date('N', $check_in_unix);
    //     if (homey_check_weekend($weekday, $weekends_days, $weekends_price)) {
    //         $booking_has_weekend = 1;
    //     }

    //     if (isset($period_price[$check_in_unix]) && isset($period_price[$check_in_unix]['night_price']) && $period_price[$check_in_unix]['night_price'] != 0) {

    //         $price_per_night = $period_price[$check_in_unix]['night_price'];
    //         //echo 'cond> <pre>  if( isset('.$period_price[$check_in_unix].') && isset('. $period_price[$check_in_unix]['night_price'] .') && '. $period_price[$check_in_unix]['night_price'] .'!=0 ){';
    //         //print_r($period_price[$check_in_unix]);
    //         $breakdown_price .= date('d-m-Y', $check_in_unix) . ' its custom pr ' . $price_per_night . ' custom price <br>';

    //         $booking_has_custom_pricing = 1;
    //         $period_days = $period_days + 1;
    //     } else {
    //         if ($days_count >= 7 && $priceWeek != 0) {
    //             //do the logic
    //         } else if ($days_count >= 30 && $priceMonthly != 0) {
    //             //do the logic
    //         } else {
    //             $price_per_night = $nightly_price; // this creates issue for 7+ and 30+ nights issue
    //         }
    //     }

    //     // To make this per night per additional guest, we added a condition > 1 night, because once it is added
    //     // if ($current_index > 0 && $allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
    //     if ($allow_additional_guests == 'yes' && $guests > 0 && !empty($guests)) {
    //         if ($guests > $listing_guests) {
    //             $additional_guests = $guests - $listing_guests;

    //             $guests_price_return = homey_calculate_guests_price($period_price, $check_in_unix, $additional_guests, $additional_guests_price);

    //             $breakdown_price .= ', prev price=' . $total_guests_price . ' + guest price=' . $guests_price_return . '<br>';

    //             $total_guests_price = $total_guests_price + $guests_price_return;
    //         }
    //     } // end To make this per night per additional guest, we added a condition > 1 night, because once it is added

    //     $returnPrice = homey_cal_weekend_price($check_in_unix, $weekends_price, $price_per_night, $weekends_days, $period_price);

    //     // echo ' the day => price='. $returnPrice.'<br>';

    //     $nights_total_price = $nights_total_price + $returnPrice;
    //     $html_date_text = date('Y-m-d', $check_in_unix);
        // $nights_total_price_li_html .= '<li class="in-loop">Price '.$returnPrice .' for '.$html_date_text.'</li>';

    //     $total_price = $total_price + $returnPrice;
    //     $breakdown_price .= date('d-m-Y', $check_in_unix) . ' < date ' . $total_price . ' < total price <br>';

    //     $check_in->modify('tomorrow');
    //     $check_in_unix = $check_in->getTimestamp();

    // }

    if ($cleaning_fee_type == 'daily') {
        $cleaning_fee = $cleaning_fee * $days_count;
        $total_price = $total_price + $cleaning_fee;
    } else {
        $total_price = $total_price + $cleaning_fee;
    }


    //Extra prices =======================================
    if ($extra_options != '' && is_array($extra_options)) {

        $extra_prices_output = '';
        $is_first = 0;
        if (is_array($extra_options) || is_object($extra_options)) {
            foreach ($extra_options as $extra_price) {
                if ($is_first == 0) {
                    $extra_prices_output .= '<li class="homey_price_first"><b>' . esc_html__('Add Ons', 'homey-child') . '</b></li>';
                }
                $is_first = 2;

                $ex_single_price = explode('|', $extra_price);

                $ex_name = $ex_single_price[0];
                $ex_price = floatval($ex_single_price[1]);
                
                // $ex_type = $ex_single_price[2];
                $ex_type = 'per_night';

                if ($ex_type == 'single_fee') {
                    $ex_price = $ex_price;

                } elseif ($ex_type == 'per_night') {
                    $ex_price = $ex_price * $days_count;
                } elseif ($ex_type == 'per_guest') {
                    $ex_price = $ex_price * $guests;
                } elseif ($ex_type == 'per_night_per_guest') {
                    $ex_price = $ex_price * $days_count * $guests;
                }

                $total_extra_services = $total_extra_services + $ex_price;

                $extra_prices_output .= '<li>' . esc_attr($ex_name) . ' (' . $ex_price/$days_count . ' x ' . $days_count . ')' . '<span>' . homey_formatted_price($ex_price) . '</span></li>';
            }
        }

        $total_price = $total_price + $total_extra_services;
        $extra_prices_html = $extra_prices_output;
    }

    $amount_before_tax = $total_price;

    //Calculate taxes based of original price (Excluding city, security deposit etc)
    if ($enable_taxes == 1) {

        if ($tax_type == 'global_tax') {
            $taxes_percent = $taxes_percent_global;
        } else {
            if (!empty($single_listing_tax)) {
                $taxes_percent = $single_listing_tax;
            }
        }

        $taxable_amount = $total_price;
        $taxes_final = homey_calculate_taxes($taxes_percent, $taxable_amount);
        $total_price = $total_price + $taxes_final;
    }

    $amount_after_tax = $total_price;

    //Calculate sevices fee based of original price ( guests price + extra prices ) (Excluding cleaning, city fee etc)
    if ($enable_services_fee == 1 && $offsite_payment != 1) {
        $services_fee_type = homey_option('services_fee_type');
        $services_fee = homey_option('services_fee');
        $price_for_services_fee = $total_price + $total_guests_price;
        $services_fee_final = homey_calculate_services_fee($services_fee_type, $services_fee, $price_for_services_fee);
        $total_price = (float) $total_price + (float) $services_fee_final;
    }

    $total_guests_with_additional = (int) $guests + (int) $additional_guests;

    if ($city_fee_type == 'daily') {
        $city_fee = $city_fee * $days_count;
        $total_price = $total_price + $city_fee;
    } elseif ($city_fee_type == 'per_guest') {
        $city_fee = $city_fee * $total_guests_with_additional;
        $total_price = $total_price + $city_fee;
    } elseif ($city_fee_type == 'daily_per_guest') {
        $city_fee = $city_fee * $days_count * $total_guests_with_additional;
        $total_price = $total_price + $city_fee;
    } else {
        $total_price = $total_price + $city_fee;
    }

    if (!empty($security_deposit) && $security_deposit != 0) {
        $total_price = $total_price + $security_deposit;
    }

    // if ($total_guests_price != 0) {
    //     $total_price = $total_price + $total_guests_price;
    // }

    $listing_host_id = get_post_field('post_author', $listing_id);
    $host_reservation_payment_type = get_user_meta($listing_host_id, 'host_reservation_payment', true);
    $host_booking_percent = get_user_meta($listing_host_id, 'host_booking_percent', true);

    if ($offsite_payment == 1 && !empty($host_reservation_payment_type)) {

        if ($host_reservation_payment_type == 'percent') {
            if (!empty($host_booking_percent) && $host_booking_percent != 0) {
                $upfront_payment = round($host_booking_percent * $total_price / 100, 2);
            }

        } elseif ($host_reservation_payment_type == 'full') {
            $upfront_payment = $total_price;

        } elseif ($host_reservation_payment_type == 'only_security') {
            $upfront_payment = $security_deposit;

        } elseif ($host_reservation_payment_type == 'only_services') {
            $upfront_payment = $services_fee_final;

        } elseif ($host_reservation_payment_type == 'services_security') {
            $upfront_payment = $security_deposit + $services_fee_final;
        }

    } else {

        if ($reservation_payment_type == 'percent') {
            if (!empty($booking_percent) && $booking_percent != 0) {
                $upfront_payment = round($booking_percent * $total_price / 100, 2);
            }

        } elseif ($reservation_payment_type == 'full') {
            $upfront_payment = $total_price;

        } elseif ($reservation_payment_type == 'only_security') {
            $upfront_payment = $security_deposit;

        } elseif ($reservation_payment_type == 'only_services') {
            $upfront_payment = $services_fee_final;

        } elseif ($reservation_payment_type == 'services_security') {
            $upfront_payment = (int)$security_deposit + (int)$services_fee_final;
        }
    }

    $balance = $total_price - $upfront_payment;
    // $nights_total_price_li_html .= '</ul>';

    $prices_array['price_per_hour'] = $price_per_night;
    $prices_array['total_hours'] = $total_hours;
    $prices_array['total_hours_price'] = $total_hours_price;
    $prices_array['guest_price'] = $total_guests_price;
    $prices_array['amount_after_guests'] = $amount_after_guests;
    $prices_array['amount_before_tax'] = $amount_before_tax;
    $prices_array['amount_after_tax'] = $amount_after_tax;

    $prices_array['price_per_night'] = $price_per_night;
    $prices_array['nights_total_price'] = $nights_total_price;
    // // $prices_array['nights_total_price_li_html'] = $nights_total_price_li_html;
    $prices_array['total_price'] = $total_price;
    $prices_array['cleaning_fee'] = $cleaning_fee;
    $prices_array['city_fee'] = $city_fee;
    $prices_array['services_fee'] = $services_fee_final;
    $prices_array['days_count'] = $days_count;
    $prices_array['period_days'] = $period_days;
    $prices_array['taxes'] = $taxes_final;
    $prices_array['taxes_percent'] = $taxes_percent;
    $prices_array['security_deposit'] = $security_deposit;
    $prices_array['additional_guests'] = $additional_guests;
    $prices_array['additional_guests_price'] = $additional_guests_price;
    $prices_array['additional_guests_total_price'] = $total_guests_price;
    $prices_array['booking_has_weekend'] = $booking_has_weekend;
    $prices_array['booking_has_custom_pricing'] = $booking_has_custom_pricing;
    $prices_array['extra_prices_html'] = $extra_prices_html;
    $prices_array['balance'] = $balance;
    $prices_array['upfront_payment'] = $upfront_payment;

    return $prices_array;

}

// homey_show_hours_based_on_date
add_action('wp_ajax_homey_show_hours_based_on_date', 'homey_show_hours_based_on_date');
add_action('wp_ajax_nopriv_homey_show_hours_based_on_date', 'homey_show_hours_based_on_date');

function homey_show_hours_based_on_date() {
    $check_in_date = $_POST['check_in_date'];
    $check_in_date = homey_convert_date($check_in_date);
    $listing_id = $_POST['listing_id'];

    $day_of_week = strtolower(substr(date('l', strtotime($check_in_date)), 0, 3));
    $homey_timeperiod_availbility = get_post_meta($listing_id, 'homey_timeperiod_availbility', true);
    
    $day_available = $homey_timeperiod_availbility[$day_of_week.'_available'];
    $day_start_hour = $homey_timeperiod_availbility[$day_of_week.'_open_time']; 
    $day_end_hour = $homey_timeperiod_availbility[$day_of_week.'_close_time'];
    
    // make a foreach loop of optoions(htlm) to get all the hours
    $options = '';
    for ($i = $day_start_hour; $i <= $day_end_hour; $i++) {
        $am_pm = ($i >= 12) ? 'PM' : 'AM';
        $display_hour = ($i > 12) ? $i - 12 : $i;
        if ($i == 0) $display_hour = 12;
        $options .= '<option value="'.$i.'">'.$display_hour.' '.$am_pm.'</option>';
    }

    if (!empty($options)) {
        echo json_encode(
            array(
                'success' => true,
                'options' => $options
            )
        );
        wp_die();
    } else {
        echo json_encode(
            array(
                'success' => false,
                'options' => $options
            )
        );
        wp_die();
    }
}

add_action('wp_ajax_booking_add_multiple_dates', 'booking_add_multiple_dates');
add_action('wp_ajax_nopriv_booking_add_multiple_dates', 'booking_add_multiple_dates');

function booking_add_multiple_dates() {
    $listing_id = $_POST['listing_id'];

    $id = uniqid();
    ob_start();
    $args = array(
        'id' => $id,
        'listing_id' => $listing_id
    );
    get_template_part('single-listing/booking/single-hour-row', null, $args);
    $html = ob_get_clean();

    echo json_encode(
        array(
            'success' => true,
            'html' => $html
        )
    );
    wp_die();
}


add_action('wp_ajax_nopriv_homey_add_reservation', 'homey_add_reservation');
add_action('wp_ajax_homey_add_reservation', 'homey_add_reservation');
function homey_add_reservation() {
    
    global $current_user;
    $local = homey_get_localization();

    $admin_email = get_option('new_admin_email');
    $admin_email = empty($admin_email) ? get_option( 'admin_email' ) : $admin_email;

    $current_user = wp_get_current_user();
    $userID = $current_user->ID;
    $no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

    if ($no_login_needed_for_booking != "yes" && !isset($_REQUEST['new_reser_request_user_email'])) {
        //check security
        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'reservation-security-nonce')) {

            echo json_encode(
                array(
                    'success' => false,
                    'message' => $local['security_check_text']
                )
            );
            wp_die();
        }
    }

    if ($current_user->ID == 0 && $no_login_needed_for_booking == "yes" && isset($_REQUEST['new_reser_request_user_email'])) {
        $email = trim($_REQUEST['new_reser_request_user_email']);

        if (empty($email)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('Enter email address', 'homey')
                )
            );
            wp_die();
        }

        $user = get_user_by('email', $email);

        if (isset($user->ID)) {
            echo json_encode(
                array(
                    'success' => false,
                    'message' => esc_html__('This email already registered, please login first, or try with new email.', 'homey')
                )
            );
            wp_die();

            //add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
            //for_reservation_nop_auto_login($user);
        } else { //create user from email
            $user_login = $email;

            $display_name  = $email;
            $nickname   = $email; ;
            $first_name   = "New" ;
            $last_name   = "User" ;
            $description    = "New User";

            $user_email = $email;
            $role = 'homey_renter';
            $user_pass = wp_generate_password(8, false);
            $userdata = compact('user_login', 'user_email', 'user_pass', 'role', 'display_name', 'nickname', 'first_name', 'last_name', 'description');
            $new_user_id = wp_insert_user($userdata);

            if ($new_user_id > 0) {
                homey_wp_new_user_notification($new_user_id, $user_pass);
            }

            update_user_meta($new_user_id, 'viaphp', 1);

            // log in automatically
            if (!is_user_logged_in()) {
                $user = get_user_by('email', $email);

                add_filter('authenticate', 'for_reservation_nop_auto_login', 3, 10);
                for_reservation_nop_auto_login($user);
            }
        }
    }

    $current_user = wp_get_current_user();
    $userID = $current_user->ID;

    $local = homey_get_localization();
    $allowded_html = array();
    $reservation_meta = array();

    $listing_id = intval($_POST['listing_id']);
    $listing_owner_id = get_post_field('post_author', $listing_id);
    $extra_options = isset($_POST['extra_options']) ? $_POST['extra_options'] : '';
    $guest_message = stripslashes($_POST['guest_message']);
    $guests = intval($_POST['guests']);
    $total_hours = intval($_POST['total_hours']);
    $booking_dates = $_POST['booking_dates'];
    $title = $local['reservation_text'];

    $owner = homey_usermeta($listing_owner_id);
    $owner_email = $owner['email'];

    $min_booking_hours = get_post_meta($listing_id, 'homey_min_book_hours', true);
    if($min_booking_hours > $total_hours) {
        echo json_encode(
            array(
                'success' => false,
                'message' => 'Minimum booking is ' . $min_booking_hours . ' hours'
            )
        );
        wp_die();
    }

    if(empty($guests)){
        echo json_encode(
            array(
                'success' => false,
                'message' => 'Please Select Guests!'
            )
        );
        wp_die();
    }

    if (!is_user_logged_in() || $userID === 0) {
        echo json_encode(
            array(
                'success' => false,
                'message' => $local['login_for_reservation']
            )
        );
        wp_die();
    }

    $booking_hide_fields = homey_option('booking_hide_fields');
    if (empty($guests) && $booking_hide_fields['guests'] != 1) {
        echo json_encode(
            array(
                'success' => false,
                'message' => $local['choose_guests']
            )
        );
        wp_die();
    }

    if ($userID == $listing_owner_id) {
        echo json_encode(
            array(
                'success' => false,
                'message' => $local['own_listing_error']
            )
        );
        wp_die();
    }

    $is_available = true;

    if ($is_available) {

        $prices_array = homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options);
        $nights_total_price = $prices_array['nights_total_price'];
        $reservation_meta['nights_total_price'] = $nights_total_price;
        $reservation_meta['reservation_listing_type'] = 'per_night';

        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $cleaning_fee = $prices_array['cleaning_fee'];
        $city_fee = $prices_array['city_fee'];
        $services_fee = $prices_array['services_fee'];
        $days_count = $prices_array['days_count'];
        $period_days = $prices_array['period_days'];
        $taxes = $prices_array['taxes'];
        $taxes_percent = $prices_array['taxes_percent'];
        $security_deposit = $prices_array['security_deposit'];
        $additional_guests = $prices_array['additional_guests'];
        $additional_guests_price = $prices_array['additional_guests_price'];
        $additional_guests_total_price = $prices_array['additional_guests_total_price'];
        $booking_has_weekend = $prices_array['booking_has_weekend'];
        $booking_has_custom_pricing = $prices_array['booking_has_custom_pricing'];

        $reservation_meta['guests'] = $guests;
        $reservation_meta['listing_id'] = $listing_id;
        $reservation_meta['upfront'] = $upfront_payment;
        $reservation_meta['balance'] = $balance;
        $reservation_meta['total'] = $total_price;

        $reservation_meta['cleaning_fee'] = $cleaning_fee;
        $reservation_meta['city_fee'] = $city_fee;
        $reservation_meta['services_fee'] = $services_fee;
        $reservation_meta['period_days'] = $period_days;
        $reservation_meta['taxes'] = $taxes;
        $reservation_meta['taxes_percent'] = $taxes_percent;
        $reservation_meta['security_deposit'] = $security_deposit;
        $reservation_meta['additional_guests_price'] = $additional_guests_price;
        $reservation_meta['additional_guests_total_price'] = $additional_guests_total_price;
        $reservation_meta['booking_has_weekend'] = $booking_has_weekend;
        $reservation_meta['booking_has_custom_pricing'] = $booking_has_custom_pricing;

        $reservation = array(
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'homey_reservation',
            'post_author' => $userID
        );
        $reservation_id = wp_insert_post($reservation);

        $reservation_update = array(
            'ID' => $reservation_id,
            'post_title' => $title . ' ' . $reservation_id
        );
        wp_update_post($reservation_update);

        update_post_meta($reservation_id, 'reservation_listing_id', $listing_id);
        update_post_meta($reservation_id, 'listing_owner', $listing_owner_id);
        update_post_meta($reservation_id, 'listing_renter', $userID);
        update_post_meta($reservation_id, 'reservation_booking_dates', $booking_dates);
        update_post_meta($reservation_id, 'reservation_guests', $guests);
        update_post_meta($reservation_id, 'reservation_meta', $reservation_meta);
        update_post_meta($reservation_id, 'reservation_status', 'under_review');
        update_post_meta($reservation_id, 'is_hourly', 'no');
        update_post_meta($reservation_id, 'extra_options', $extra_options);
        update_post_meta($reservation_id, 'reservation_total_hours', $total_hours);

        update_post_meta($reservation_id, 'reservation_upfront', $total_price);
        update_post_meta($reservation_id, 'reservation_balance', $total_price);
        update_post_meta($reservation_id, 'reservation_total', $total_price);

        $reservation_time = current_time('timestamp');
        $reservation_time = date('Y-m-d H:i:s', $reservation_time); 
        update_post_meta($reservation_id, 'reservation_time', $reservation_time);

        $reservation_page = homey_get_template_link_dash('template/dashboard-reservations2.php');
        $reservation_detail_link = add_query_arg('reservation_detail', $reservation_id, $reservation_page);

        echo json_encode(
            array(
                'success' => true,
                'message' => $local['request_sent'],
                'reservation_detail' => reservation_detail_link($reservation_id),
            )
        );

        $guest_message = empty($guest_message) ? esc_html__("To send another message, click on view.", "homey") : $guest_message;

        if (!empty(trim($guest_message))) {
            do_action('homey_create_messages_thread', $guest_message, $reservation_id);
        }

        $message_link = homey_thread_link_after_reservation($reservation_id);

        $user_info = get_userdata($userID);
        $renter_email = '';
        if ($user_info) {
            $renter_email = $user_info->user_email;
        }

        $email_args = array(
            'reservation_detail_url' => reservation_detail_link($reservation_id),
            'check_in_date' => homey_get_booking_start_date($reservation_id),
            'check_out_date' => homey_get_booking_end_date($reservation_id),
            'guests' => homey_get_total_guests_range($reservation_id, $listing_id),
            'total_price' => $total_price,
            'renter_email' => $renter_email,
            'guest_message' => $guest_message,
            'message_link' => $message_link
        );

        if ($owner_email != $admin_email) {
            homey_email_composer($owner_email, 'new_reservation', $email_args);
        }

        homey_email_composer($admin_email, 'new_reservation', $email_args);

        $user_info = get_userdata($userID);
        $renter_email = '';
        if ($user_info) {
            $renter_email = $user_info->user_email;
        }

        $email_args = array(
            'reservation_detail_url' => $reservation_detail_link,
            'check_in_date' => homey_get_booking_start_date($reservation_id),
            'check_out_date' => homey_get_booking_end_date($reservation_id),
            'guests' => homey_get_total_guests_range($reservation_id, $listing_id),
            'total_price' => $total_price,
            'renter_email' => $renter_email,
            'guest_message' => $guest_message,
            'message_link' => $message_link
        );

        homey_email_composer($current_user->user_email, 'new_reservation_sent', $email_args);

        wp_die();

    }
}

// CALCULATE BOOKING PRICES
if (!function_exists('homey_calculate_reservation_cost_day_date_child')) {
    function homey_calculate_reservation_cost_day_date_child($reservation_id, $collapse = false)
    {

        $reservation_id = intval($reservation_id);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $booking_dates = get_post_meta($reservation_id, 'reservation_booking_dates', true);
        $total_hours = get_post_meta($reservation_id, 'reservation_total_hours', true);
        $guests = get_post_meta($reservation_id, 'reservation_guests', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        $prefix = 'homey_';
        $local = homey_get_localization();
        $allowded_html = array();
        $output = '';

        // $total_hours = homey_calculate_booking_hours($check_in_date, $start_hour, $check_out_date, $end_hour);

        $prices_array = homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options);

        // $nights_total_price_li_html = $prices_array['nights_total_price_li_html'];
        $nights_total_price_li_html = '';
        $price_per_night = homey_formatted_price($prices_array['price_per_night'], true);
        $no_of_days = $prices_array['days_count'];

        $guest_price = $prices_array['guest_price'];

        $nights_total_price = homey_formatted_price($prices_array['nights_total_price'], false);

        $cleaning_fee = homey_formatted_price($prices_array['cleaning_fee']);
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
        $upfront_payment = $prices_array['upfront_payment'];
        $balance = $prices_array['balance'];
        $total_price = $prices_array['total_price'];
        $amount_after_tax = $prices_array['amount_after_tax'];

        $amount_before_tax = $prices_array['amount_before_tax'];

        if(homey_is_host()){
            $host_fee = homey_option('host_fee');
            $host_fee_amount = $amount_before_tax * $host_fee / 100;
            $sub_total_price = $total_price - $host_fee_amount;
            if(!empty($taxes) && $taxes != 0){
                $sub_total_price = $sub_total_price - $taxes;
            }
        }

        if ($additional_guests > 1) {
            $add_guest_label = $local['cs_add_guests'];
        } else {
            $add_guest_label = $local['cs_add_guest'];
        }

        $output = '<div class="payment-list-price-detail clearfix">';

        if (isset($prices_array['breakdown_price'])) {
            $output .= '<div style="display:none;">' . $prices_array['breakdown_price'] . '</div>';
        }

        $exclude_taxes = 'Does not include taxes';

        $output .= '<div class="pull-left">';
        $output .= '<div class="payment-list-price-detail-total-price">' . esc_attr($local['cs_total']) . '</div>';
        // $output .= '<div class="payment-list-price-detail-note">' . esc_attr($exclude_taxes) . '</div>';
        $output .= '</div>';

        $output .= '<div class="pull-right text-right">';
        if(homey_is_host()){
            $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($amount_after_tax) . '</div>';
        }else{
            $output .= '<div class="payment-list-price-detail-total-price">' . homey_formatted_price($total_price) . '</div>';
        }
        // $output .= '<a class="payment-list-detail-btn" data-toggle="collapse" data-target=".collapseExample" href="javascript:void(0);" aria-expanded="false" aria-controls="collapseExample">' . esc_attr($local['cs_view_details']) . '</a>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="reservation-detail-page-payment" id="">';
        $output .= '<ul>';

        if($total_hours > 1){
            $night_label = 'Hours';
        }else{
            $night_label = 'Hour';
        }
        $nights_total_price = $prices_array['total_hours_price'];
        $output .= '<li class="homey_price_first">' .$nights_total_price_li_html. ($price_per_night) . ' x ' . esc_attr($total_hours) . ' ' . esc_attr($night_label) . ' <span>' . homey_formatted_price($nights_total_price) . '</span></li>';

        if (!empty($additional_guests)) {
            $output .= '<li>' . esc_attr($additional_guests) . ' ' . esc_attr($add_guest_label) . ' <span>' . homey_formatted_price($additional_guests_total_price) . '</span></li>';
        }

        if (!empty($prices_array['cleaning_fee']) && $prices_array['cleaning_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_cleaning_fee']) . ' <span>' . ($cleaning_fee) . '</span></li>';
        }

        if (!empty($extra_prices_html)) {
            $output .= $extra_prices_html;
        }

        $services_fee = $services_fee > 0 ? $services_fee : 0;
        $sub_total_amnt = $total_price - $prices_array['city_fee'] - $security_deposit - $services_fee - $taxes;
        // $output .= '<li class="sub-total">' . esc_html__('Sub Total', 'homey') . '<span>' . homey_formatted_price($sub_total_amnt) . '</span></li>';

        if (!empty($prices_array['city_fee']) && $prices_array['city_fee'] != 0) {
            $output .= '<li>' . esc_attr($local['cs_city_fee']) . ' <span>' . ($city_fee) . '</span></li>';
        }

        if (!empty($security_deposit) && $security_deposit != 0) {
            $output .= '<li>' . esc_attr($local['cs_sec_deposit']) . ' <span>' . homey_formatted_price($security_deposit) . '</span></li>';
        }

        if (!empty($taxes) && $taxes != 0) {
            $taxes_label = 'Service Fee';
            // . ' (' . esc_attr($taxes_percent) . '%)
            $output .= '<li>' . esc_attr($taxes_label) .' <span>' . homey_formatted_price($taxes) . '</span></li>';
        }

        if(homey_is_host() && $host_fee_amount > 0){
            $output .= '<li>Host Fee <span>' . homey_formatted_price($host_fee_amount) . '</span></li>';
        }

        if(homey_is_host()){
            $output .= '<li style="color:black; font-weight:500; font-size:16px;">Total Host Payment <span>' . homey_formatted_price($sub_total_price) . '</span></li>';
        }

        if(!homey_is_host()){
            $avg_price = homey_formatted_price(0);
            if (!empty($upfront_payment) && $upfront_payment != 0) {
                $curncy = homey_get_currency(1);

                $avg_price = $curncy . ' ' . $upfront_payment / $no_of_days;

                $avg_price .= ' <sub> /';
                $avg_price .= esc_html__('Average Night', 'homey');
                $avg_price .= '</sub>';

                $output .= '<li class="payment-due">' . esc_attr($local['cs_payment_due']) . ' <span>' . homey_formatted_price($upfront_payment) . '</span></li>';
                $output .= '<input data-avg-price="' . $avg_price . '" type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="' . $upfront_payment . '">';
            }
        }

        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
}

if (!function_exists('homey_stripe_payment_child')) {
    function homey_stripe_payment_child($reservation_id)
    {

        $allowded_html = array();

        if(isset($_REQUEST['reservation_no_userHash'])){
            if($_REQUEST['reservation_no_userHash'] > 0){
                $userID = (int) $_REQUEST['reservation_no_userHash'];
                $userID = intval(deHashNoUserId($userID));
                $current_user = get_userdata($userID);

                if ($current_user) {
                    $user_email = $current_user->user_email;
                }
            }
        }else{
            $current_user = wp_get_current_user();
            $userID = $current_user->ID;
            $user_email = $current_user->user_email;
        }

        $user_email = $current_user->user_email;
        $reservation_payment_type = homey_option('reservation_payment');

        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $extra_options = get_post_meta($reservation_id, 'extra_options', true);


        // $listing_id = intval($reservation_meta['listing_id']);
        // $check_in_date = wp_kses($reservation_meta['check_in_date'], $allowded_html);
        // $check_out_date = wp_kses($reservation_meta['check_out_date'], $allowded_html);
        // $guests = intval($reservation_meta['guests']);
        // $adult_guest = isset($reservation_meta['adult_guest']) ? intval($reservation_meta['adult_guest']) : 0;
        // $child_guest = isset($reservation_meta['child_guest']) ? intval($reservation_meta['child_guest']) : 0;

        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $check_in_date = homey_get_booking_start_date($reservation_id);
        $check_out_date = homey_get_booking_end_date($reservation_id);
        $guests = homey_get_total_guests_range($reservation_id, $listing_id);

        $booking_type = homey_booking_type_by_id($listing_id);

        // if ($booking_type == 'per_day_date') {
        //     $prices_array = homey_get_day_date_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        // } else {
        //     $prices_array = homey_get_prices($check_in_date, $check_out_date, $listing_id, $guests, $extra_options);
        // }

        $upfront_payment = get_post_meta($reservation_id, 'reservation_upfront', true);

        // $extra_expenses = homey_get_extra_expenses($reservation_id);
        // $extra_discount = homey_get_extra_discount($reservation_id);

        // if (!empty($extra_expenses) && $reservation_payment_type == 'full') {
        //     $expenses_total_price = $extra_expenses['expenses_total_price'];
        //     $upfront_payment = $upfront_payment + $expenses_total_price;
        // }

        // if (!empty($extra_discount) && $reservation_payment_type == 'full') {
        //     $discount_total_price = $extra_discount['discount_total_price'];
        //     $upfront_payment = $upfront_payment - $discount_total_price;
        // }

        $minimum_currency_amount = get_minimum_currency();
        // echo "<pre>";
        // print_r($minimum_currency_amount);
        
        if ($upfront_payment < $minimum_currency_amount) {
            echo $minimum_amount_error = esc_html__("You can't pay using Stripe because minimum amount limit is 0.5", 'homey');
            return $minimum_amount_error;
        }

        $description = esc_html__('Reservation ID', 'homey') . ' ' . $reservation_id;

        if ($userID < 1) {
            echo esc_html__("Please register yourself to continue.", 'homey');
            return $userID;
        }

        // Use the enhanced Stripe Child class for payment splitting
        if (class_exists('Homey_Stripe_Child')) {
            $stripe_payments = new Homey_Stripe_Child($userID);
        } else {
            // Fallback to parent class if child class not available
            require_once(HOMEY_PLUGIN_PATH . '/classes/class-stripe.php');
            $stripe_payments = new Homey_Stripe($userID);
        }

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
        $metadata = array(
            'reservation_id_for_stripe' => $reservation_id,
            'userID' => $userID,
            'guests' => $guests,
            'adult_guest' => 0,
            'child_guest' => 0,
            'is_hourly' => 0,
            'payment_type' => 'reservation_fee',
            'extra_options' => ($extra_options == '') ? 0 : 1,
            'message' => esc_html__('Reservation Payment', 'homey')
        );

        if ($upfront_payment > 0) {
            $stripe_payments->homey_stripe_form($upfront_payment, $metadata, $description);
        } else {
            $message_text = esc_html__('Your amount in your wallet is: ', 'homey');
            $upfront_payment_with_symbol = homey_option("currency_symbol") . ' ' . $upfront_payment;
            echo '<h3>' . $message_text . ' ' . $upfront_payment_with_symbol . '</h3>';
        }

        print'
        </div>';


    }
}

function homey_get_reservation_notification($status, $reservation_id = null) {

    $notification = '';
    $local = homey_get_localization();

    if (homey_listing_guest($reservation_id)) {

        if ($status == 'under_review') {
            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                            ' . esc_html__('Thank you, your request has been submitted to the host - if approved, payment option will be provided. If not approved, the reservation will be cancelled. The host has 48 hrs to respond.', 'homey') . '
                        </div>';
        } elseif ($status == 'available') {
            $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                    ' . esc_html__('So far so good! Host confirmed availability for this reservation. Complete the payment due.', 'homey') . '
                                </div>';
        } elseif ($status == 'booked') {
            $admin_or_host = homey_is_host() ? 'Well done! Payment received the reservation has been booked.' : 'Congratulations! The reservation has been booked.';
            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                    ' . esc_html__($admin_or_host, 'homey') . '
                                </div>';

        } elseif ($status == 'declined') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                ' . esc_html__('Your reservation has been declined by the host', 'homey') . '
                            </div>';
        } elseif ($status == 'cancelled') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                ' . esc_html__('You have cancelled the reservation', 'homey') . '
                            </div>';
        }

    } else {
        if ($status == 'under_review') {
            $notification = '<div class="alert alert-info alert-dismissible" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                            ' . esc_html__('Hooray! You received a new reservation. Confirm Availability.', 'homey') . '
                        </div>';
        } elseif ($status == 'available') {
            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                    ' . esc_html__('You confirmed availability for this reservation.', 'homey') . '
                                </div>';
        } elseif ($status == 'booked') {
            $admin_or_host = homey_is_host() ? 'Well done! Payment received the reservation has been booked.' : 'Congratulations! The reservation has been booked.';

            $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                    ' . esc_html__($admin_or_host, 'homey') . '
                                </div>';

        } elseif ($status == 'declined') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                ' . esc_html__('You have declined the reservation', 'homey') . '
                            </div>';

        } elseif ($status == 'cancelled') {
            $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                                ' . esc_html__('The reservation has been cancellated', 'homey') . '
                            </div>';
        }
    }

    if ($status == 'not_owner') {
        $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                            ' . $local['listing_owner_text'] . '
                        </div>';
    }

    if ($status == 'host_payment_method') {
        $notification = '<div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                            ' . esc_html__('Please setup your payment method first', 'homey') . '
                        </div>';
    }

    if ($status == 'sent_for_verification') {
        $notification = '<div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
                            ' . esc_html__('Request successfully sent for verification', 'homey') . '
                        </div>';
    }

    // if ($status == 'waiting_host_payment_verification' && homey_is_host() || homey_is_admin()) {
    //     $notification = '<div class="alert alert-success alert-dismissible" role="alert">
    //                         <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>
    //                         ' . esc_html__('Payment Received? Mark as Paid', 'homey') . '
    //                     </div>';
    // }

    return $notification;

}


function homey_get_reservation_action($status, $upfront_payment, $payment_link, $ID, $class) {

    $action = '';
    $local = homey_get_localization();

    $offsite_payment = homey_option('off-site-payment');

    if (homey_listing_guest($ID)) {

        if ($status == 'under_review') {
            $action = '<span class="btn btn-success-outlined ' . esc_attr($class) . '"><i class="homey-icon homey-icon-check-circle-1"></i>' . esc_html__('Awaiting Host Confirmation', 'homey') . '</span>';

            $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';

        } elseif ($status == 'available') {

            if (homey_is_woocommerce()) {

                $action = '<a href="#" data-reservation_id="' . intval($ID) . '" class="homey-woo-reservation-pay btn btn-success ' . esc_attr($class) . '">' . esc_html__('Pay Now', 'homey') . ' ' . $upfront_payment . '</a>';

            } else {
                $action = '<a href="' . esc_url($payment_link) . '" class="btn btn-success ' . esc_attr($class) . '">' . esc_html__('Pay Now', 'homey') . ' ' . $upfront_payment . '</a>';
            }

            $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';

        } elseif ($status == 'booked') {
            $action = '<span class="btn btn-success-outlined ' . esc_attr($class) . '"><i class="homey-icon homey-icon-check-circle-1"></i> ' . esc_html__('Booked', 'homey') . '</span>';

            $action .= '<button class="btn btn-grey-light btn-half-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';

        } elseif ($status == 'waiting_host_payment_verification' && homey_is_host()) {
            $action = '<span class="btn btn-warning-outlined ' . esc_attr($class) . '"> ' . esc_html__('Waiting Approval', 'homey') . '</span>';
        }

    } else {

        if ($status == 'under_review') {

            if ($offsite_payment == 1) {
                $action = '<button data-reservation_id="' . intval($ID) . '" class="confirm-offsite-reservation btn btn-success ' . esc_attr($class) . '">' . esc_html__('Confirm Availability', 'homey') . '</button>';
            } else {
                $action = '<button data-reservation_id="' . intval($ID) . '" class="confirm-reservation btn btn-success ' . esc_attr($class) . '">' . esc_html__('Confirm Availability', 'homey') . '</button>';
            }

            $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';

        } elseif ($status == 'available') {
            $action = '<span class="btn btn-success-outlined ' . esc_attr($class) . '"><i class="homey-icon homey-icon-check-circle-1"></i>' . esc_html__('Available', 'homey') . '</span>';

            $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="collapse" id="decline-reservation-btn" data-target="#decline-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';


        } elseif ($status == 'booked') {
            $action = '<span class="btn btn-success-outlined ' . esc_attr($class) . '"><i class="homey-icon homey-icon-check-circle-1"></i> ' . esc_html__('Booked', 'homey') . '</span>';

            $action .= '<button class="btn btn-grey-light btn-half-width" data-toggle="collapse" id="cancel-reservation-btn" data-target="#cancel-reservation" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Cancel', 'homey') . '</button>';

        } elseif ($status == 'waiting_host_payment_verification' && homey_is_host()) {
            $action = '<a href="#" data-id="' . intval($ID) . '" class="mark-as-paid btn btn-success ' . esc_attr($class) . '">' . esc_html__('Payment Received? Mark as Paid', 'homey') . '</a>';
        }

    }

    if ($status == 'declined') {
        $action = '<span class="btn btn-danger-outlined ' . esc_attr($class) . '"><i class="homey-icon homey-icon-check-circle-1"></i> ' . esc_html__('Declined', 'homey') . '</span>';
    }

    // if (!homey_listing_guest($ID) && $status == 'under_review') {
    //     $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="modal" data-target="#modal-extra-expenses">' . esc_html__('Extra Expenses', 'homey') . '</button>';

    //     // $action .= '<button class="btn btn-grey-light ' . esc_attr($class) . '" data-toggle="modal" data-target="#modal-discount" aria-expanded="false" aria-controls="collapseExample">' . esc_html__('Discount', 'homey') . '</button>';
    // }

    return $action;

}

// RESERVATION EXPIRE
add_action('wp_ajax_expire_reservation_timeout', 'homey_expire_reservation_timeout');
add_action('wp_ajax_nopriv_expire_reservation_timeout', 'homey_expire_reservation_timeout');

function homey_expire_reservation_timeout(){
    $reservation_id = $_POST['reservation_id'];

    if(!empty($reservation_id)){
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        if(!empty($reservation_status) && ($reservation_status == 'under_review' || $reservation_status == 'available')){
            
            update_post_meta($reservation_id, 'reservation_status', 'cancelled');
            update_post_meta($reservation_id, 'res_cancel_reason', 'Reservation Time Out');

            echo json_encode(array('success' => true));
        }else{
            echo json_encode(array('success' => false));
        }
    }

}