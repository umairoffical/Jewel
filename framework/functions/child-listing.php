<?php

add_action('wp_ajax_generate_listing_rules', 'generate_listing_rules');
add_action('wp_ajax_nopriv_generate_listing_rules', 'generate_listing_rules');
function generate_listing_rules(){
    $rulesText = $_POST['rulesText'];

    $args = array(
        'id' => uniqid(),
        'rulesText' => $rulesText,
        'data' => [],
    );
    
    ob_start();
    get_template_part('template-parts/dashboard/submit-listing/single-rule', null, $args);
    $html = ob_get_contents();
    ob_end_clean();

    $response_data = array(
        'success' => true,
        'message' => 'Rule added successfully',
        'rule_html' => $html,
    );

    echo json_encode($response_data);
    wp_die();
    
}

/*-----------------------------------------------------------------------------------*/
/*  Upload Gallery Images
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_homey_listing_gallery_upload', 'homey_listing_gallery_upload');    // only for logged in user
add_action('wp_ajax_nopriv_homey_listing_gallery_upload', 'homey_listing_gallery_upload');
function homey_listing_gallery_upload() {

    // Check security Nonce
    $verify_nonce = $_REQUEST['verify_nonce'];
    if (!wp_verify_nonce($verify_nonce, 'verify_gallery_nonce')) {
        echo json_encode(array('success' => false, 'reason' => 'Invalid nonce!'));
        die;
    }

    $submitted_file = $_FILES['listing_upload_file'];
    // $is_dimension_valid = homey_listing_image_dimension($submitted_file);
    $uploaded_image = wp_handle_upload($submitted_file, array('test_form' => false));

    if (isset($uploaded_image['file'])) {
        $file_name = basename($submitted_file['name']);
        $file_type = wp_check_filetype($uploaded_image['file']);

        // Prepare an array of post data for the attachment.
        $attachment_details = array(
            'guid' => $uploaded_image['url'],
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment_details, $uploaded_image['file']);
        $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_image['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        $thumbnail_url = wp_get_attachment_image_src($attach_id, 'thumbnail');
        $listing_thumb = wp_get_attachment_image_src($attach_id, 'homey-listing-thumb');
        $feat_image_url = wp_get_attachment_url($attach_id);

        $ajax_response = array(
            'success' => true,
            'url' => $thumbnail_url[0],
            'attachment_id' => $attach_id,
            'full_image' => $feat_image_url,
            'thumb' => $listing_thumb[0],
        );

        echo json_encode($ajax_response);
        die;

    }

}

/***********************************************************/
/*** SAVE LISTING SUBMISSION FILTER (OVERITE) ***/
/***********************************************************/
function listing_submission_filter($new_listing) {
    global $current_user;

    // echo "<pre>";
    // print_r($_POST['rule']);
    // wp_die();

    $current_user = wp_get_current_user();
    $userID = $current_user->ID;
    $user_email = $current_user->user_email;
    $admin_email = get_bloginfo('admin_email');
    $totalGuestsPlusAddtionalGuests = 0;

    $listings_admin_approved = homey_option('listings_admin_approved');
    $edit_listings_admin_approved = homey_option('edit_listings_admin_approved');

    // Title
    if (isset($_POST['listing_title'])) {
        $new_listing['post_title'] = sanitize_text_field($_POST['listing_title']);
    }

    // Description
    if (isset($_POST['description'])) {
        $new_listing['post_content'] = wp_kses_post($_POST['description']);
    }


    if (isset($_POST['post_author_id']) && !empty($_POST['post_author_id'])) {
        $new_listing['post_author'] = intval($_POST['post_author_id']);
    } else {
        $new_listing['post_author'] = $userID;
    }

    $submission_action = sanitize_text_field($_POST['action']);
    $listing_id = 0;

    $draft_listing_id = isset($_POST['draft_listing_id']) ? $_POST['draft_listing_id'] : '';
    $draft_listing_id = intval($draft_listing_id);

    if (!empty($draft_listing_id)) {
        $submission_action = 'update_listing';
    }

    $first_owner_userID = 0;
    $previous_post_status = '';

    if ($submission_action == 'homey_add_listing' || isset($_GET['duplication'])) {
        $first_owner_userID = $current_user->ID;

        if (($listings_admin_approved != 0 && !homey_is_admin())) {
            $new_listing['post_status'] = 'pending';
        } else {
            $new_listing['post_status'] = 'publish';
        }

        /*
            * Filter submission arguments before insert into database.
            */
        $new_listing = apply_filters('homey_before_submit_listing', $new_listing);

        do_action('homey_before_listing_submit', $new_listing);

        $listing_id = wp_insert_post($new_listing);

        //mandatory post metas that should not be null
        update_post_meta($listing_id, 'homey_featured', 0);

        if(isset($_GET['duplication'])){

            // copy images when duplication script is there
            homey_copy_images_for_duplicated_post($listing_id);
        }

        if (isset($_GET['dup_id'])) {
            //duplication of custom pricing
            homey_addCustomPeriodDuplicated($listing_id, $_GET['dup_id']);
        }
    } else if ($submission_action == 'update_listing') {
        if (!empty($draft_listing_id)) {
            $new_listing['ID'] = $draft_listing_id;
        } else {
            $new_listing['ID'] = intval($_POST['listing_id']);
        }

        if(homey_is_listing_owner_child($new_listing['ID'], $current_user->ID) < 1){
            echo json_encode(array('success' => false, 'listing_id' => $listing_id, 'msg' => esc_html__('Something bad is happend, please try again.', 'homey')));
            wp_die();
        }

        $previous_post_status = get_post_status($new_listing['ID']);

        $check_is_approved = get_post_meta($new_listing['ID'], 'homey_firsttime_is_admin_approved', true);
        // that is removed because was not standard but one clients request.
        // $check_is_approved = 1;

        // if (($check_is_approved == 0 || $edit_listings_admin_approved != 0) && !homey_is_admin()) {
        if ($edit_listings_admin_approved != 0 && !homey_is_admin()) {
            $new_listing['post_status'] = 'pending';
        } else {
            $new_listing['post_status'] = get_post_status($new_listing['ID']);
            if($listings_admin_approved != 0 && $new_listing['post_status'] != 'publish' && !homey_is_admin()) {// to check
                $new_listing['post_status'] = 'pending';
            }else{
                $new_listing['post_status'] = 'publish';
                // is in need to be first time approved from admin?
                update_post_meta($new_listing['ID'], 'homey_firsttime_is_admin_approved', 1);
            }
        }

        /*
            * Filter submission arguments before update into database.
            */
        $new_listing = apply_filters('homey_before_update_listing', $new_listing);

        do_action('homey_before_listing_update');
        $listing_id = wp_update_post($new_listing);
    }

    if ($listing_id > 0) {
        $prefix = 'homey_';

        //Custom Fields
        if (class_exists('Homey_Fields_Builder')) {
            $fields_array = Homey_Fields_Builder::get_form_fields();
            if (!empty($fields_array)):
                foreach ($fields_array as $value):
                    $field_name = $value->field_id;
                    $field_type = $value->type;

                    if (isset($_POST[$field_name])) {
                        if ($field_type == 'textarea') {
                            update_post_meta($listing_id, 'homey_' . $field_name, $_POST[$field_name]);
                        } else {
                            update_post_meta($listing_id, 'homey_' . $field_name, sanitize_text_field($_POST[$field_name]));
                        }

                    }

                endforeach; endif;
        }

        $listing_total_rating = get_post_meta($listing_id, 'listing_total_rating', true);
        $listing_total_number_of_reviews = get_post_meta($listing_id, 'listing_total_number_of_reviews', true);
        if ($listing_total_rating === '') {
            update_post_meta($listing_id, 'listing_total_rating', '0');
            update_post_meta($listing_id, 'listing_total_number_of_reviews', '0');
        }

        // First owner field
        if ($first_owner_userID > 0) {
            update_post_meta($listing_id, $prefix . 'first_owner_user_id', sanitize_text_field($first_owner_userID));
        }

        // Booking type
        if (isset($_POST['booking_type'])) {
            update_post_meta($listing_id, $prefix . 'booking_type', sanitize_text_field($_POST['booking_type']));
        }

        // virtual tour
        if (isset($_POST['virtual_tour'])) {
            update_post_meta($listing_id, $prefix . 'virtual_tour', $_POST['virtual_tour']);
        }

        // NEW FIELDS
        if(!empty($_POST['timeperiod'])){
            update_post_meta($listing_id, 'homey_timeperiod_availbility', $_POST['timeperiod']);
        }

        if(!empty($_POST['guest_price'])){
            update_post_meta($listing_id, 'homey_guest_price', $_POST['guest_price']);
        }

        // if(isset($_POST['host_welcome_message'])){
        //     update_post_meta($listing_id, 'homey_welcome_message', sanitize_text_field($_POST['host_welcome_message']));
        // }

        if(isset($_POST['site_rep_name'])){
            update_post_meta($listing_id, 'homey_rep_name', sanitize_text_field($_POST['site_rep_name']));
        }

        if(isset($_POST['day_of_instructions'])){
            update_post_meta($listing_id, 'homey_instructions', sanitize_text_field($_POST['day_of_instructions']));
        }

        if(isset($_POST['parking_spots'])){
            update_post_meta($listing_id, 'homey_parking_spots', sanitize_text_field($_POST['parking_spots']));
        }

        if(isset($_POST['listing_parking'])){
            $facilities_array = array();
            foreach ($_POST['listing_parking'] as $facility_id) {
                $facilities_array[] = intval($facility_id);
            }
            wp_set_object_terms($listing_id, $facilities_array, 'parking');
        }

        // rule
        if(!empty($_POST['rule'])){
            update_post_meta($listing_id, 'homey_rules', $_POST['rule']);
        }


        // Instance
        update_post_meta($listing_id, $prefix . 'instant_booking', 0);

        if (isset($_POST['instant_booking'])) {
            $instance_bk = $_POST['instant_booking'];
            if ($instance_bk == 'on') {
                $instance_bk = 1;
            }

            update_post_meta($listing_id, $prefix . 'instant_booking', sanitize_text_field($instance_bk));
        }

        // Bedrooms
        if (isset($_POST['listing_bedrooms'])) {
            update_post_meta($listing_id, $prefix . 'listing_bedrooms', sanitize_text_field($_POST['listing_bedrooms']));
        }

        // Guests
        if (isset($_POST['guests'])) {
            update_post_meta($listing_id, $prefix . 'guests', sanitize_text_field($_POST['guests']));

            $totalGuestsPlusAddtionalGuests = $_POST['guests'];
        }

        // Beds
        if (isset($_POST['beds'])) {
            update_post_meta($listing_id, $prefix . 'beds', sanitize_text_field($_POST['beds']));
        }

        // Baths
        if (isset($_POST['baths'])) {
            update_post_meta($listing_id, $prefix . 'baths', sanitize_text_field($_POST['baths']));
        }

        // Rooms
        if (isset($_POST['listing_rooms'])) {
            update_post_meta($listing_id, $prefix . 'listing_rooms', sanitize_text_field($_POST['listing_rooms']));
        }

        // affiliate_booking_link
        if (isset($_POST['affiliate_booking_link'])) {
            update_post_meta($listing_id, $prefix . 'affiliate_booking_link', sanitize_text_field($_POST['affiliate_booking_link']));
        }

        // Day Date Price
        if (isset($_POST['day_date_price'])) {
            update_post_meta($listing_id, $prefix . 'day_date_price', sanitize_text_field($_POST['day_date_price']));

            // because of sorting issue
            update_post_meta($listing_id, $prefix . 'night_price', sanitize_text_field($_POST['day_date_price']));
        }

        // Day Date Weekend Price
        if (isset($_POST['day_date_weekends_price'])) {
            update_post_meta($listing_id, $prefix . 'day_date_weekends_price', sanitize_text_field($_POST['day_date_weekends_price']));
        }

        // Night Price
        if (isset($_POST['night_price'])) {
            update_post_meta($listing_id, $prefix . 'night_price', sanitize_text_field($_POST['night_price']));
        }

        // Weekend Price
        if (isset($_POST['weekends_price'])) {
            update_post_meta($listing_id, $prefix . 'weekends_price', sanitize_text_field($_POST['weekends_price']));
        }

        // Hourly Price
        if (isset($_POST['hour_price'])) {
            update_post_meta($listing_id, $prefix . 'hour_price', sanitize_text_field($_POST['hour_price']));

            // because of sorting issue
            update_post_meta($listing_id, $prefix . 'night_price', sanitize_text_field($_POST['hour_price']));
        }

        // After Price label
        if (isset($_POST['price_postfix'])) {
            update_post_meta($listing_id, $prefix . 'price_postfix', sanitize_text_field($_POST['price_postfix']));
        }

        // Hourly Weekend Price
        if (isset($_POST['hourly_weekends_price'])) {
            update_post_meta($listing_id, $prefix . 'hourly_weekends_price', sanitize_text_field($_POST['hourly_weekends_price']));
        }

        // Min book Hours
        if (isset($_POST['min_book_hours'])) {
            update_post_meta($listing_id, $prefix . 'min_book_hours', sanitize_text_field($_POST['min_book_hours']));
        }

        // Start Hours
        if (isset($_POST['start_hour'])) {
            update_post_meta($listing_id, $prefix . 'start_hour', sanitize_text_field($_POST['start_hour']));
        }

        // End Hours
        if (isset($_POST['end_hour'])) {
            update_post_meta($listing_id, $prefix . 'end_hour', sanitize_text_field($_POST['end_hour']));
        }

        if (isset($_POST['weekends_days'])) {
            update_post_meta($listing_id, $prefix . 'weekends_days', sanitize_text_field($_POST['weekends_days']));
        }

        // Week( 7 Nights ) Price
        if (isset($_POST['priceWeek'])) {
            update_post_meta($listing_id, $prefix . 'priceWeek', sanitize_text_field($_POST['priceWeek']));
        }

        // Monthly ( 30 Nights ) Price
        if (isset($_POST['priceMonthly'])) {
            update_post_meta($listing_id, $prefix . 'priceMonthly', sanitize_text_field($_POST['priceMonthly']));
        }

        // Additional Guests price
        if (isset($_POST['additional_guests_price'])) {
            update_post_meta($listing_id, $prefix . 'additional_guests_price', sanitize_text_field($_POST['additional_guests_price']));
        }

        // Additional Guests allowed
        if (isset($_POST['num_additional_guests'])) {
            update_post_meta($listing_id, $prefix . 'num_additional_guests', sanitize_text_field($_POST['num_additional_guests']));

            //If additional guests set lets add them to the total count
            if (isset($_POST['num_additional_guests']) && $_POST['num_additional_guests'] != '') {
                $totalGuestsPlusAddtionalGuests += (int) $_POST['num_additional_guests'];
            }
        }

        //Now update the meta data with the total guest count
        update_post_meta($listing_id, $prefix . 'total_guests_plus_additional_guests', $totalGuestsPlusAddtionalGuests);

        // Security Deposit
        if (isset($_POST['allow_additional_guests'])) {
            update_post_meta($listing_id, $prefix . 'allow_additional_guests', sanitize_text_field($_POST['allow_additional_guests']));
        }

        // Cleaning fee
        if (isset($_POST['cleaning_fee'])) {
            update_post_meta($listing_id, $prefix . 'cleaning_fee', sanitize_text_field($_POST['cleaning_fee']));
        }

        // Cleaning fee
        if (isset($_POST['cleaning_fee_type'])) {
            update_post_meta($listing_id, $prefix . 'cleaning_fee_type', sanitize_text_field($_POST['cleaning_fee_type']));
        }

        // City fee
        if (isset($_POST['city_fee'])) {
            update_post_meta($listing_id, $prefix . 'city_fee', sanitize_text_field($_POST['city_fee']));
        }

        // City fee
        if (isset($_POST['city_fee_type'])) {
            update_post_meta($listing_id, $prefix . 'city_fee_type', sanitize_text_field($_POST['city_fee_type']));
        }

        // securityDeposit
        if (isset($_POST['security_deposit'])) {
            update_post_meta($listing_id, $prefix . 'security_deposit', sanitize_text_field($_POST['security_deposit']));
        }

        // securityDeposit
        if (isset($_POST['tax_rate'])) {
            update_post_meta($listing_id, $prefix . 'tax_rate', sanitize_text_field($_POST['tax_rate']));
        }

        // Listing size
        if (isset($_POST['listing_size'])) {
            update_post_meta($listing_id, $prefix . 'listing_size', sanitize_text_field($_POST['listing_size']));
        }

        // Listing size
        if (isset($_POST['listing_size_unit'])) {
            update_post_meta($listing_id, $prefix . 'listing_size_unit', sanitize_text_field($_POST['listing_size_unit']));
        }

        $full_address_arr = array();
        // Address
        if (isset($_POST['listing_address'])) {
            $full_address_arr['listing_address'] = $_POST['listing_address'];
            update_post_meta($listing_id, $prefix . 'listing_address', sanitize_text_field($_POST['listing_address']));
        }

        //AptSuit
        if (isset($_POST['aptSuit'])) {
            $full_address_arr['aptSuit'] = $_POST['aptSuit'];
            update_post_meta($listing_id, $prefix . 'aptSuit', sanitize_text_field($_POST['aptSuit']));
        }

        // Cancellation Policy
        if (isset($_POST['cancellation_policy'])) {
            update_post_meta($listing_id, $prefix . 'cancellation_policy', sanitize_textarea_field($_POST['cancellation_policy']));
        }

        // Minimum Stay
        if (isset($_POST['min_book_days'])) {
            update_post_meta($listing_id, $prefix . 'min_book_days', sanitize_text_field($_POST['min_book_days']));
        }

        if (isset($_POST['min_book_weeks'])) {
            update_post_meta($listing_id, $prefix . 'min_book_weeks', sanitize_text_field($_POST['min_book_weeks']));
        }

        if (isset($_POST['min_book_months'])) {
            update_post_meta($listing_id, $prefix . 'min_book_months', sanitize_text_field($_POST['min_book_months']));
        }

        // Maximum Stay
        if (isset($_POST['max_book_days'])) {
            update_post_meta($listing_id, $prefix . 'max_book_days', sanitize_text_field($_POST['max_book_days']));
        }
        if (isset($_POST['max_book_weeks'])) {
            update_post_meta($listing_id, $prefix . 'max_book_weeks', sanitize_text_field($_POST['max_book_weeks']));
        }
        if (isset($_POST['max_book_months'])) {
            update_post_meta($listing_id, $prefix . 'max_book_months', sanitize_text_field($_POST['max_book_months']));
        }

        // Check in After
        if (isset($_POST['checkin_after'])) {
            update_post_meta($listing_id, $prefix . 'checkin_after', sanitize_text_field($_POST['checkin_after']));
        }

        // Check Out After
        if (isset($_POST['checkout_before'])) {
            update_post_meta($listing_id, $prefix . 'checkout_before', sanitize_text_field($_POST['checkout_before']));
        }

        // Allow Smoke
        if (isset($_POST['smoke'])) {
            update_post_meta($listing_id, $prefix . 'smoke', sanitize_text_field($_POST['smoke']));
        }

        // Allow Pets
        if (isset($_POST['pets'])) {
            $pets = intval($_POST['pets']);
            update_post_meta($listing_id, $prefix . 'pets', sanitize_text_field($pets));
        }

        // Allow Party
        if (isset($_POST['party'])) {
            update_post_meta($listing_id, $prefix . 'party', sanitize_text_field($_POST['party']));
        }

        // Allow Childred
        if (isset($_POST['children'])) {
            update_post_meta($listing_id, $prefix . 'children', sanitize_text_field($_POST['children']));
        }

        // Additional Rules
        if (isset($_POST['additional_rules'])) {
            update_post_meta($listing_id, $prefix . 'additional_rules', $_POST['additional_rules']);
        }

        // Overtime Policy Rules
        if (isset($_POST['overtime_policy'])) {
            update_post_meta($listing_id, $prefix . 'overtime_policy', $_POST['overtime_policy']);
        }

        if (isset($_POST['homey_accomodation'])) {
            $homey_accomodation = $_POST['homey_accomodation'];
            if (!empty($homey_accomodation)) {
                update_post_meta($listing_id, $prefix . 'accomodation', $homey_accomodation);
            }
        } else {
            update_post_meta($listing_id, $prefix . 'accomodation', '');
        }

        if (isset($_POST['homey_services'])) {
            $homey_services = $_POST['homey_services'];
            if (!empty($homey_services)) {
                update_post_meta($listing_id, $prefix . 'services', $homey_services);
            }
        } else {
            update_post_meta($listing_id, $prefix . 'services', '');
        }

        if (isset($_POST['extra_price'])) {
            $extra_price = $_POST['extra_price'];
            if (!empty($extra_price)) {
                update_post_meta($listing_id, $prefix . 'extra_prices', $extra_price);
            }
        } else {
            update_post_meta($listing_id, $prefix . 'extra_prices', '');
        }

        // Openning Hours
        if (isset($_POST['mon_fri_open'])) {
            update_post_meta($listing_id, $prefix . 'mon_fri_open', sanitize_text_field($_POST['mon_fri_open']));
        }
        if (isset($_POST['mon_fri_close'])) {
            update_post_meta($listing_id, $prefix . 'mon_fri_close', sanitize_text_field($_POST['mon_fri_close']));
        }
        if (isset($_POST['mon_fri_closed'])) {
            update_post_meta($listing_id, $prefix . 'mon_fri_closed', sanitize_text_field($_POST['mon_fri_closed']));
        } else {
            update_post_meta($listing_id, $prefix . 'mon_fri_closed', 0);
        }

        if (isset($_POST['sat_open'])) {
            update_post_meta($listing_id, $prefix . 'sat_open', sanitize_text_field($_POST['sat_open']));
        }
        if (isset($_POST['sat_close'])) {
            update_post_meta($listing_id, $prefix . 'sat_close', sanitize_text_field($_POST['sat_close']));
        }
        if (isset($_POST['sat_closed'])) {
            update_post_meta($listing_id, $prefix . 'sat_closed', sanitize_text_field($_POST['sat_closed']));
        } else {
            update_post_meta($listing_id, $prefix . 'sat_closed', 0);
        }

        if (isset($_POST['sun_open'])) {
            update_post_meta($listing_id, $prefix . 'sun_open', sanitize_text_field($_POST['sun_open']));
        }
        if (isset($_POST['sun_close'])) {
            update_post_meta($listing_id, $prefix . 'sun_close', sanitize_text_field($_POST['sun_close']));
        }
        if (isset($_POST['sun_closed'])) {
            update_post_meta($listing_id, $prefix . 'sun_closed', sanitize_text_field($_POST['sun_closed']));
        } else {
            update_post_meta($listing_id, $prefix . 'sun_closed', 0);
        }

        // Postal Code
        if (isset($_POST['zip'])) {
            $full_address_arr['zip'] = $_POST['zip'];
            update_post_meta($listing_id, $prefix . 'zip', sanitize_text_field($_POST['zip']));
        }

        // Country
        if (isset($_POST['country'])) {
            $full_address_arr['country'] = $_POST['country'];
            $listing_country = sanitize_text_field($_POST['country']);
            $country_id = wp_set_object_terms($listing_id, $listing_country, 'listing_country');
        }

        // State
        if (isset($_POST['administrative_area_level_1'])) {
            $listing_state = sanitize_text_field($_POST['administrative_area_level_1']);
            $full_address_arr['listing_state'] = $listing_state;
            $state_id = wp_set_object_terms($listing_id, $listing_state, 'listing_state');

            $homey_meta = array();
            $homey_meta['parent_country'] = isset($_POST['country']) ? $_POST['country'] : '';
            if (!empty($state_id)) {
                update_option('_homey_listing_state_' . $state_id[0], $homey_meta);
            }
        }

        // City
        if (isset($_POST['locality'])) {
            $listing_city = sanitize_text_field($_POST['locality']);
            $full_address_arr['listing_city'] = $listing_city;
            $city_id = wp_set_object_terms($listing_id, $listing_city, 'listing_city');

            $homey_meta = array();
            $homey_meta['parent_state'] = isset($_POST['administrative_area_level_1']) ? $_POST['administrative_area_level_1'] : '';
            if (!empty($city_id)) {
                update_option('_homey_listing_city_' . $city_id[0], $homey_meta);
            }
        }

        // Area
        if (isset($_POST['neighborhood'])) {
            $listing_area = sanitize_text_field($_POST['neighborhood']);
            $full_address_arr['listing_area'] = $listing_area;
            $area_id = wp_set_object_terms($listing_id, $listing_area, 'listing_area');

            $homey_meta = array();
            $homey_meta['parent_city'] = isset($_POST['locality']) ? $_POST['locality'] : '';
            if (!empty($area_id)) {
                update_option('_homey_listing_area_' . $area_id[0], $homey_meta);
            }
        }

        // Make featured
        if (isset($_POST['listing_featured'])) {
            $featured = intval($_POST['listing_featured']);
            update_post_meta($listing_id, 'homey_featured', $featured);
        }


        if ((isset($_POST['lat']) && !empty($_POST['lat'])) && (isset($_POST['lng']) && !empty($_POST['lng']))) {
            $lat = sanitize_text_field($_POST['lat']);
            $lng = sanitize_text_field($_POST['lng']);
            $lat_lng = $lat . ',' . $lng;

            update_post_meta($listing_id, $prefix . 'geolocation_lat', $lat);
            update_post_meta($listing_id, $prefix . 'geolocation_long', $lng);
            update_post_meta($listing_id, $prefix . 'listing_location', $lat_lng);
            update_post_meta($listing_id, $prefix . 'listing_map', '1');
            update_post_meta($listing_id, $prefix . 'show_map', 1);


            if ($submission_action == 'homey_add_listing') {
                homey_insert_lat_long($lat, $lng, $listing_id);
            } elseif ($submission_action == 'update_listing') {
                homey_update_lat_long($lat, $lng, $listing_id);
            }
        }

        if (isset($_POST['room_type']) && ($_POST['room_type'] != '-1')) {
            wp_set_object_terms($listing_id, intval($_POST['room_type']), 'room_type');
        }else {
            // Get the existing terms for the listing
            $existing_terms = wp_get_object_terms($listing_id, 'room_type', array('fields' => 'ids'));

            // Check if there are any existing terms
            if (!empty($existing_terms)) {
                // Unset the room_type for the listing
                wp_remove_object_terms($listing_id, $existing_terms, 'room_type');
            }
        }

        // Listing Type
        if (isset($_POST['listing_type'])) {
            $listing_type_array = array();
            foreach ($_POST['listing_type'] as $type_id) {
                if($type_id != '-1') {
                    $listing_type_array[] = intval($type_id);
                }
            }

            // First remove any existing terms
            $existing_terms = wp_get_object_terms($listing_id, 'listing_type', array('fields' => 'ids'));
            if (!empty($existing_terms)) {
                wp_remove_object_terms($listing_id, $existing_terms, 'listing_type');
            }

            // Add new terms one by one
            if(!empty($listing_type_array)) {
                foreach($listing_type_array as $type_id) {
                    wp_set_object_terms($listing_id, $type_id, 'listing_type', true);
                }
            }
        }

        // Amenities
        if (isset($_POST['listing_amenity'])) {
            $amenities_array = array();
            foreach ($_POST['listing_amenity'] as $amenity_id) {
                $amenities_array[] = intval($amenity_id);
            }
            wp_set_object_terms($listing_id, $amenities_array, 'listing_amenity');
        }

        // Facilities
        if (isset($_POST['listing_facility'])) {
            $facilities_array = array();
            foreach ($_POST['listing_facility'] as $facility_id) {
                $facilities_array[] = intval($facility_id);
            }
            wp_set_object_terms($listing_id, $facilities_array, 'listing_facility');
        }

        // Facilities
        if (isset($_POST['listing_accessibility'])) {
            $facilities_array = array();
            foreach ($_POST['listing_accessibility'] as $facility_id) {
                $facilities_array[] = intval($facility_id);
            }
            wp_set_object_terms($listing_id, $facilities_array, 'listing_accessibility');
        }


        // clean up the old meta information related to images when listing update
        if ($submission_action == "update_listing" && !isset($_GET['duplication'])) {
            delete_post_meta($listing_id, 'homey_listing_images');
            delete_post_meta($listing_id, '_thumbnail_id');
        }

        if (isset($_POST['video_url'])) {
            update_post_meta($listing_id, $prefix . 'video_url', sanitize_text_field($_POST['video_url']));
        }

        // Listing Images
        if (isset($_POST['listing_image_ids']) && !isset($_GET['duplication'])) {
            if (!empty($_POST['listing_image_ids']) && is_array($_POST['listing_image_ids'])) {
                $listing_image_ids = array();
                foreach ($_POST['listing_image_ids'] as $img_id) {
                    if(!in_array(intval($img_id), $listing_image_ids)){
                        $listing_image_ids[] = intval($img_id);
                        add_post_meta($listing_id, 'homey_listing_images', $img_id);
                    }
                }

                // featured image
                if (isset($_POST['featured_image_id'])) {
                    $featured_image_id = intval($_POST['featured_image_id']);
                    if (in_array($featured_image_id, $listing_image_ids)) {
                        update_post_meta($listing_id, '_thumbnail_id', $featured_image_id);
                    }
                } elseif (!empty ($listing_image_ids)) {
                    update_post_meta($listing_id, '_thumbnail_id', $listing_image_ids[0]);
                }
            }
        }

        apply_filters('listing_submission_filter_filter', $listing_id);

        $post_status_text_user = esc_html__("Your listing status is published", 'homey');
        $post_status_text_admin = esc_html__("This listing status is published", 'homey');

        if ($submission_action == 'homey_add_listing') {
            if (($listings_admin_approved != 0 && !homey_is_admin())) {
                $post_status_text_user = esc_html__("Your listing status is pending for admin approval", 'homey');
                $post_status_text_admin = esc_html__("This listing status is in need to be approved from you.", 'homey');
            }

            $args = array(
                'listing_title' => get_the_title($listing_id),
                'listing_id' => $listing_id,
                'post_status_user' => $post_status_text_user,
                'post_status_admin' => $post_status_text_admin,
            );
            /*
                * Send email
                * */
            if (($listings_admin_approved != 0 && !homey_is_admin())) {
                homey_email_composer($user_email, 'new_submission_listing', $args);
            }

            homey_email_composer($admin_email, 'admin_new_submission_listing', $args);

            do_action('homey_after_listing_submit', $listing_id);

        } else if ($submission_action == 'update_listing') {

            $post_status_text_user = esc_html__("Your listing status is published", 'homey');
            $post_status_text_admin = esc_html__("This listing status is published", 'homey');

            if ($edit_listings_admin_approved != 0 && !homey_is_admin()) {
                $post_status_text_user = esc_html__("Your listing status is pending for admin approval", 'homey');
                $post_status_text_admin = esc_html__("This listing status is in need to be approved from you.", 'homey');
            }

            $args = array(
                'listing_title' => get_the_title($listing_id),
                'listing_id' => $listing_id,
                'post_status_user' => $post_status_text_user,
                'post_status_admin' => $post_status_text_admin,
            );
            /*
                * Send email
                * */
            if ($edit_listings_admin_approved != 0 && !homey_is_admin()) {
                homey_email_composer($user_email, 'update_submission_listing', $args);
            }

            homey_email_composer($admin_email, 'admin_update_submission_listing', $args);

            // email if listing is enabled from pending
            if (homey_is_admin() && $previous_post_status != 'publish' && $new_listing['post_status'] == 'publish') {
                $listing_current_owner = get_post_meta($listing_id, 'listing_owner', true);
                $listing_first_owner = get_post_meta($listing_id, 'homey_first_owner_user_id', true);

                $listing_owner = $listing_current_owner > 0 ? $listing_current_owner : $listing_first_owner;

                $user_info = get_userdata($listing_owner);

                if (isset($user_info->user_email)) {
                    $owner_email = $user_info->user_email;
                    $args = array(
                        'listing_title' => get_the_title($listing_id),
                        'listing_id' => $listing_id,
                        'post_status_user' => esc_html__('Your listing is published.', 'homey'),
                    );
                    homey_email_composer($owner_email, 'listing_approved', $args);
                }
            }

            do_action('houmey_after_listing_update', $listing_id);
        }

        $full_address_text = '';

        $listing_address = !empty($full_address_arr['listing_address']) ? $full_address_arr['listing_address'] : '';
        $aptSuit = !empty($full_address_arr['aptSuit']) ? $full_address_arr['aptSuit'] : '';
        $zip = !empty($full_address_arr['zip']) ? $full_address_arr['zip'] : '';
        $country = !empty($full_address_arr['country']) ? $full_address_arr['country'] : '';
        $listing_state = !empty($full_address_arr['listing_state']) ? $full_address_arr['listing_state'] : '';
        $listing_city = !empty($full_address_arr['listing_city']) ? $full_address_arr['listing_city'] : '';

        // Homey theme address format <address> <apt suite>, <city>, <state> <zip code> <country>
        $full_address_text = $listing_address . ' ' . $aptSuit . ', ' . $listing_city . ', ' . $listing_state . ', ' . $zip . ' ' . $country;

        update_post_meta($listing_id, $prefix . 'listing_full_address', $full_address_text);

        return $listing_id;
    }

} //listing_submission_filter
add_filter('listing_submission_filter', 'listing_submission_filter');

function homey_is_listing_owner_child($listing_id=0, $current_user_id=0) {
    if($current_user_id == 0){
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
    }

    $listing_owner_id = get_post_field('post_author', $listing_id);
    if(homey_is_admin() || $listing_owner_id == $current_user_id){
        return 1;
    }

    return 0;
}


add_action( 'wp_ajax_nopriv_homey_host_contact_child', 'homey_host_contact_child' );
add_action( 'wp_ajax_homey_host_contact_child', 'homey_host_contact_child' );

if( !function_exists('homey_host_contact_child') ) {
    function homey_host_contact_child() {

        $nonce = $_POST['host_contact_security'];
        if (!wp_verify_nonce( $nonce, 'host-contact-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Invalid Nonce!', 'homey-core')
            ));
            wp_die();
        }

        $sender_phone = sanitize_text_field( $_POST['phone'] );
        $permalink = esc_url( $_POST['permalink'] );
        $listing_title = sanitize_text_field( $_POST['listing_title'] );
        $response = isset($_POST["g-recaptcha-response"]) ? $_POST["g-recaptcha-response"] : "";

        $target_email = $_POST['target_email'];
        if ( !is_array( $target_email ) ) {
            $target_email = is_email($target_email);
        }
        if (!$target_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => sprintf( esc_html__('%s Email address is not configured!', 'homey-core'), $target_email )
            ));
            wp_die();
        }

        $sender_name = sanitize_text_field($_POST['name']);
        if ( empty($sender_name) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Name field is empty!', 'homey-core')
            ));
            wp_die();
        }
        
        // if ( empty($sender_phone) ) {
        //     echo json_encode(array(
        //         'success' => false,
        //         'msg' => esc_html__('Phone field is empty!', 'homey-core')
        //     ));
        //     wp_die();
        // }

        // $sender_email = sanitize_email($_POST['email']);
        // $sender_email = is_email($sender_email);
        // if (!$sender_email) {
        //     echo json_encode(array(
        //         'success' => false,
        //         'msg' => esc_html__('Invalid email address!', 'homey-core')
        //     ));
        //     wp_die();
        // }

        $sender_msg = wp_kses_post( $_POST['message'] );
        if ( empty($sender_msg) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Your message is empty!', 'homey-core')
            ));
            wp_die();
        }

        $enable_forms_gdpr = homey_option('enable_forms_gdpr');

        if( $enable_forms_gdpr != 0 ) {
            $privacy_policy = isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '';
            if ( empty($privacy_policy) ) {
                echo json_encode(array(
                    'success' => false,
                    'msg' => homey_option('forms_gdpr_validation')
                ));
                wp_die();
            }
        }

        homey_google_recaptcha_callback();


        $subject = sprintf( esc_html__('New message sent by %s using host contact form at %s', 'homey-core'), $sender_name, get_bloginfo('name') );

        $body = esc_html__("You have received new message from: ", 'homey-core') . $sender_name . " <br/>";

        if ( ! empty( $listing_title ) ) {
            $body .= "<br/>" . esc_html__("Listing Title : ", 'homey-core') . $listing_title . " <br/>";
        }

        if ( ! empty( $permalink ) ) {
            $body .= esc_html__("Listing URL : ", 'homey-core') . '<a href="'. $permalink. '">' . $permalink . "</a><br/>";
        }

        if (! empty($sender_phone)) {
            $body .= esc_html__("Phone Number : ", 'homey-core') . $sender_phone . " <br/>";
        }

        $body .= "<br/>" . esc_html__("Additional message is.", 'homey-core') . " <br/>";
        $body .= wpautop( $sender_msg ) . " <br/>";
        // $body .= sprintf( esc_html__( 'You can contact %s via email %s', 'homey-core'), $sender_name, $sender_email );

        $header = 'Content-type: text/html; charset=utf-8' . "\r\n";

        // $header  .= "From: $sender_name <$sender_email>\r\n";
        // $header .= "MIME-Version: 1.0\r\n";
        $header .= "Contact-Name: $sender_name\r\n";

        if ( wp_mail( $target_email, $subject, $body, $header ) ) {
            echo json_encode( array(
                'success' => true,
                'msg' => esc_html__("Email Sent Successfully!", 'homey-core')
            ));
            wp_die();
        } else {
            echo json_encode(array(
                    'success' => false,
                    'msg' => esc_html__("Server Error: Make sure Email function working on your server!", 'homey-core')
                )
            );
            wp_die();
        }
        wp_die();

    }
}
