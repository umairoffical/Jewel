<?php
add_action( 'wp_ajax_nopriv_homey_half_map', 'homey_half_map' );
add_action( 'wp_ajax_homey_half_map', 'homey_half_map' );

function homey_half_map() {

    global $homey_prefix, $homey_local, $template_args;

    $homey_prefix = 'homey_';
    $homey_local = homey_get_localization();

    $homey_search_type = homey_search_type();

    $rental_text = $homey_local['rental_label'];

    //check_ajax_referer('homey_map_ajax_nonce', 'security');

    $tax_query = array();
    $meta_query = array();
    $allowed_html = array();
    $query_ids = '';

    $cgl_meta = homey_option('cgl_meta');
    $cgl_beds = homey_option('cgl_beds');
    $cgl_baths = homey_option('cgl_baths');
    $cgl_guests = homey_option('cgl_guests');
    $cgl_types = homey_option('cgl_types');
    $price_separator = homey_option('currency_separator');

    $arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
    $depart = isset($_POST['depart']) ? $_POST['depart'] : '';
    $guests = isset($_POST['guest']) ? $_POST['guest'] : '';
    $pets = isset($_POST['pets']) ? $_POST['pets'] : -1;
    $bedrooms = isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '';
    $rooms = isset($_POST['rooms']) ? $_POST['rooms'] : '';
    $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : '';
    $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : '';
    $room_size = isset($_POST['room_size']) ? $_POST['room_size'] : '';
    $search_country = isset($_POST['search_country']) ? $_POST['search_country'] : '';
    $search_city = isset($_POST['search_city']) ? $_POST['search_city'] : '';
    $search_area = isset($_POST['search_area']) ? $_POST['search_area'] : '';
    $listing_type = isset($_POST['listing_type']) && $_POST['listing_type'] != -1 ? $_POST['listing_type'] : '';
    $search_lat = isset($_POST['search_lat']) ? $_POST['search_lat'] : '';
    $search_lng = isset($_POST['search_lng']) ? $_POST['search_lng'] : '';
    $search_radius = isset($_POST['radius']) ? $_POST['radius'] : 20;

    $paged = isset($_POST['paged']) ? ($_POST['paged']) : '';
    $sort_by = isset($_POST['sort_by']) ? ($_POST['sort_by']) : '';
    $layout = isset($_POST['layout']) ? ($_POST['layout']) : 'list';
    $num_posts = isset($_POST['num_posts']) ? ($_POST['num_posts']) : '9';

    $country = isset($_POST['country']) ? $_POST['country'] : '';
    $state = isset($_POST['state']) ? $_POST['state'] : '';
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $area = isset($_POST['area']) ? $_POST['area'] : '';
    $booking_type = isset($_POST['booking_type']) ? $_POST['booking_type'] : '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

    $template_args = array( 'listing-item-view' => 'item-grid-view' );

    if ( $layout == 'list' || $layout == 'list-v2' ) {
        $template_args = array( 'listing-item-view' => 'item-list-view' );
    } elseif ( $layout == 'card' ) {
        $template_args = array( 'listing-item-view' => 'item-card-view' );
    }

    $arrive = homey_search_date_format($arrive);
    $depart = homey_search_date_format($depart);

    $beds_baths_rooms_search = homey_option('beds_baths_rooms_search');
    $search_criteria = '=';
    if( $beds_baths_rooms_search == 'greater') {
        $search_criteria = '>=';
    } elseif ($beds_baths_rooms_search == 'lessthen') {
        $search_criteria = '<=';
    }

    if( !empty($booking_type) ) {
        $homey_search_type = $booking_type;
    }

    $query_args = array(
        'post_type' => 'listing',
        'posts_per_page' => $num_posts,
        'post_status' => 'publish',
        'paged' => $paged,
    );

    $keyword = trim($keyword);
    if (!empty($keyword)) {
        $query_args['s'] = $keyword;
    }

    if( !empty( $_POST["optimized_loading"] ) ) {
        $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
        $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
        $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
        $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

        $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
    }


    if( homey_option('enable_radius') ) {
        if($homey_search_type == 'per_hour') {
            $available_listings_ids = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
        } else {
            $available_listings_ids = apply_filters('homey_check_search_availability_filter', $query_args, $arrive, $depart);
        }

        $radius_ids = apply_filters('homey_radius_filter', $query_args, $search_lat, $search_lng, $search_radius);

        if(!empty($available_listings_ids) && !empty($radius_ids)) {
            $query_ids =  array_intersect($available_listings_ids, $radius_ids);

            if(empty($query_ids)) {
                $query_ids = array(0);
            }

        } elseif(empty($available_listings_ids)) {
            $query_ids = $radius_ids;

        } elseif(empty($radius_ids)) {
            $query_ids = $available_listings_ids;
        }

        if(!empty($query_ids)) {
            $query_args['post__in'] = $query_ids;
        }
    } else {

        if($homey_search_type == 'per_hour') {
            $query_args = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
        } else {
            $query_args = apply_filters('homey_check_search_availability_filter_optimized', $query_args, $arrive, $depart);
            if(!empty($arrive)){
                //$query_args[ 'post__not_in' ] = check_all_availability_for_search($arrive, $depart);
            }

        }

        if(!empty($search_city) || !empty($search_area)) {
            $_tax_query = Array();

            if(!empty($search_city) && !empty($search_area)) {
                $_tax_query['relation'] = 'AND';
            }

            if(!empty($search_city)) {
                $_tax_query[] = array(
                    'taxonomy' => 'listing_city',
                    'field' => 'slug',
                    'terms' => $search_city
                );
            }

            if(!empty($search_area)) {
                $_tax_query[] = array(
                    'taxonomy' => 'listing_area',
                    'field' => 'slug',
                    'terms' => $search_area
                );
            }

            $tax_query[] = $_tax_query;
        }

        if(!empty($search_country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => homey_traverse_comma_string($search_country)
            );
        }

    }

    if(!empty($listing_type)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_type',
            'field' => 'slug',
            'terms' => homey_traverse_comma_string($listing_type)
        );
    }

    if(!empty($country)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_country',
            'field' => 'slug',
            'terms' => $country
        );
    }

    if(!empty($state)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_state',
            'field' => 'slug',
            'terms' => $state
        );
    }

    if(!empty($city)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_city',
            'field' => 'slug',
            'terms' => $city
        );
    }

    if(!empty($area)) {
        $tax_query[] = array(
            'taxonomy' => 'listing_area',
            'field' => 'slug',
            'terms' => $area
        );
    }

    // min and max price logic
    if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any' && isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
        $min_price = doubleval(homey_clean($_POST['min-price']));
        $max_price = doubleval(homey_clean($_POST['max-price']));

        if ($min_price > 0 && $max_price > $min_price) {
            $meta_query[] = array(
                'key' => array('homey_day_date_price', 'homey_night_price'),
                'value' => array($min_price, $max_price),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }
    } else if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any') {
        $min_price = doubleval(homey_clean($_POST['min-price']));
        if ($min_price > 0) {
            $meta_query[] = array(
                'key' => array('homey_day_date_price', 'homey_night_price'),
                'value' => $min_price,
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }
    } else if (isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
        $max_price = doubleval(homey_clean($_POST['max-price']));
        if ($max_price > 0) {
            $meta_query[] = array(
                'key' => array('homey_day_date_price', 'homey_night_price'),
                'value' => $max_price,
                'type' => 'NUMERIC',
                'compare' => '<=',
            );
        }
    }

    if(!empty($guests)) {
        $meta_query[] = array(
            'key' => 'homey_total_guests_plus_additional_guests',
            'value' => intval($guests),
            'type' => 'NUMERIC',
            'compare' => $search_criteria,
        );
    }

    //because this is boolean, no other option other than yes or no
    //$pets = $pets == '' ? 1 : $pets;
    //if(!empty($pets) && $pets != '0') {
    if(!empty($pets) && $pets != -1) {
        $meta_query[] = array(
            'key' => 'homey_pets',
            'value' => $pets,
            'type' => 'NUMERIC',
            'compare' => '=',
        );
    }
    //print_r($meta_query);exit;
    if (!empty($bedrooms)) {
        $bedrooms = sanitize_text_field($bedrooms);
        $meta_query[] = array(
            'key' => 'homey_listing_bedrooms',
            'value' => $bedrooms,
            'type' => 'NUMERIC',
            'compare' => $search_criteria,
        );
    }

    if (!empty($rooms)) {
        $rooms = sanitize_text_field($rooms);
        $meta_query[] = array(
            'key' => 'homey_listing_rooms',
            'value' => $rooms,
            'type' => 'NUMERIC',
            'compare' => $search_criteria,
        );
    }

    // if( !empty($booking_type) ) {
    //     $meta_query[] = array(
    //         'key'     => 'homey_booking_type',
    //         'value'   => $booking_type,
    //         'compare' => '=',
    //         'type'    => 'CHAR'
    //     );
    // }

    if (isset($_POST['area']) && !empty($_POST['area'])) {
        if (is_array($_POST['area'])) {
            $areas = $_POST['area'];

            foreach ($areas as $area):
                $tax_query[] = array(
                    'taxonomy' => 'listing_area',
                    'field' => 'slug',
                    'terms' => homey_traverse_comma_string($area)
                );
            endforeach;
        }
    }

    if (isset($_POST['amenity']) && !empty($_POST['amenity'])) {
        if (is_array($_POST['amenity'])) {
            $amenities = $_POST['amenity'];

            foreach ($amenities as $amenity):
                $tax_query[] = array(
                    'taxonomy' => 'listing_amenity',
                    'field' => 'slug',
                    'terms' => $amenity
                );
            endforeach;
        }
    }

    if (isset($_POST['facility']) && !empty($_POST['facility'])) {
        if (is_array($_POST['facility'])) {
            $facilities = $_POST['facility'];

            foreach ($facilities as $facility):
                $tax_query[] = array(
                    'taxonomy' => 'listing_facility',
                    'field' => 'slug',
                    'terms' => $facility
                );
            endforeach;
        }
    }

    if(!empty($room_size)) {
        $tax_query[] = array(
            'taxonomy' => 'room_type',
            'field' => 'slug',
            'terms' => homey_traverse_comma_string($room_size)
        );
    }

    // $booking_mode = homey_booking_type();
    // $search_price_filed = 'homey_night_price';

    // if ($booking_mode == 'per_day_date'){
    //     $search_price_filed = 'homey_day_date_price';
    // }else if($booking_mode == 'per_day'){
    //     $search_price_filed = 'homey_night_price';
    // }else if($booking_mode == 'per_hour'){
    //     $search_price_filed = 'homey_hour_price';
    // }else if($booking_mode == 'per_week'){
    //     $search_price_filed = 'homey_day_date_weekends_price';
    // }else if($booking_mode == 'per_month'){
    //     $search_price_filed = 'homey_night_price';
    // }

    // if ( $sort_by == 'a_price' ) {
    //     $query_args['orderby'] = 'meta_value_num';
    //     $query_args['meta_key'] = $search_price_filed;
    //     $query_args['order'] = 'ASC';
    // } else if ( $sort_by == 'd_price' ) {
    //     $query_args['orderby'] = 'meta_value_num';
    //     $query_args['meta_key'] = $search_price_filed;
    //     $query_args['order'] = 'DESC';
    // } else if ( $sort_by == 'a_rating' ) {
    //     $query_args['orderby'] = 'meta_value_num';
    //     $query_args['meta_key'] = 'listing_total_rating';
    //     $query_args['order'] = 'ASC';
    // } else if ( $sort_by == 'd_rating' ) {
    //     $query_args['orderby'] = 'meta_value_num';
    //     $query_args['meta_key'] = 'listing_total_rating';
    //     $query_args['order'] = 'DESC';
    // } else if ( $sort_by == 'featured' ) {
    //     $query_args['meta_key'] = 'homey_featured';
    //     $query_args['meta_value'] = '1';
    // } else if ( $sort_by == 'a_date' ) {
    //     $query_args['orderby'] = 'date';
    //     $query_args['order'] = 'ASC';
    // } else if ( $sort_by == 'd_date' ) {
    //     $query_args['orderby'] = 'date';
    //     $query_args['order'] = 'DESC';
    // } else if ( $sort_by == 'featured_top' ) {
    //     // $query_args['orderby'] = 'meta_value_num';
    //     // $query_args['meta_key'] = 'homey_featured';
    //     // $query_args['order'] = 'DESC';
    //     //The above was getting featured first but there was a date sorting issue, the below is nice in working
    //     $query_args['meta_key'] = 'homey_featured';
    //     $query_args['orderby'] = array(
    //         'homey_featured' => 'DESC',
    //         'date' => 'DESC',
    //     );
    // }

    $meta_count = count($meta_query);

    if( $meta_count > 1 ) {
        $meta_query['relation'] = 'AND';
    }
    if( $meta_count > 0 ){
        $query_args['meta_query'] = $meta_query;
    }

    $tax_count = count( $tax_query );

    if( $tax_count > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    if( $tax_count > 0 ){
        $query_args['tax_query'] = $tax_query;
    }

    $query_args = new WP_Query( $query_args );

    $listings = array();

    ob_start();

    $total_listings = $query_args->found_posts;

    if($total_listings > 1) {
        $rental_text = $homey_local['rentals_label'];
    }

    $all_listings_for_pins = all_listings_for_pin();
    while( $query_args->have_posts() ): $query_args->the_post();

        $listing_id = get_the_ID();
        $address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
        $bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );

        $guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
        $additional_guests         = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );
        $guests = (int) $guests + (int) $additional_guests;

        $searched_guests = isset($_POST['guest']) ? $_POST['guest'] : '';

        if( !empty ($searched_guests) && $beds_baths_rooms_search == 'greater') {
            if(! $searched_guests >= $guests){
                //continue;
            }
        } elseif (!empty ($searched_guests) && $beds_baths_rooms_search == 'lessthen') {
            if(! $searched_guests <= $guests){
                //continue;
            }
        }

        $beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
        $baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
        $night_price          = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
        $location = get_post_meta( get_the_ID(), $homey_prefix.'listing_location',true);
        $lat_long = explode(',', $location);

        $per_stay_label = false;
        if(empty($arrive) && empty($depart) || homey_option('show_unit_or_dates_price', 0) < 1){
            $listing_price = homey_get_price_by_id($listing_id);
            $template_args['listing_price_from_search_results'] = $listing_price;
        }else{
            if(empty($depart)){
                $depart = $arrive;
            }

            $listing_price_arr = homey_get_prices($arrive, $depart, $listing_id, $guests);
            $listing_price = $listing_price_arr['total_price'];
            $template_args['listing_price_from_search_results'] = $listing_price;
            $per_stay_label = true;
        }

        $listing_type = wp_get_post_terms( get_the_ID(), 'listing_type', array("fields" => "ids") );

        if($cgl_beds != 1) {
            $bedrooms = '';
        }

        if($cgl_baths != 1) {
            $baths = '';
        }

        if($cgl_guests != 1) {
            $guests = '';
        }

        $lat = $long = '';
        if(!empty($lat_long[0])) {
            $lat = $lat_long[0];
        }

        if(!empty($lat_long[1])) {
            $long = $lat_long[1];
        }

        $listing = new stdClass();

        $listing->id = $listing_id;
        $listing->title = get_the_title();
        $listing->lat = $lat;
        $listing->long = $long;
        $listing->price = homey_formatted_price($listing_price, false, false).'<sub>'.esc_attr($price_separator).homey_get_price_label_by_id($listing_id, $per_stay_label).'</sub>';
        $listing->address = $address;
        $listing->bedrooms = $bedrooms;
        $listing->guests = $guests;
        $listing->beds = $beds;
        $listing->baths = $baths;

        $listing->arrive = $_POST['arrive'];
        $listing->depart = $_POST['depart'];

        if($cgl_types != 1) {
            $listing->listing_type = '';
        } else {
            $listing->listing_type = homey_taxonomy_simple('listing_type');
        }
        $listing->thumbnail = get_the_post_thumbnail( $listing_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
        $listing->url = get_permalink();

        $listing->icon = get_template_directory_uri() . '/images/custom-marker.png';

        $listing->retinaIcon = get_template_directory_uri() . '/images/custom-marker.png';

        if(!empty($listing_type)) {
            foreach( $listing_type as $term_id ) {

                $listing->term_id = $term_id;

                $icon_id = get_term_meta($term_id, 'homey_marker_icon', true);
                $retinaIcon_id = get_term_meta($term_id, 'homey_marker_retina_icon', true);

                $icon = wp_get_attachment_image_src( $icon_id, 'full' );
                $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

                if( !empty($icon['0']) ) {
                    $listing->icon = $icon['0'];
                }
                if( !empty($retinaIcon['0']) ) {
                    $listing->retinaIcon = $retinaIcon['0'];
                }
            }
        }

        array_push($listings, $listing);

        if($layout == 'card') {
            get_template_part('template-parts/listing/listing', 'card', $template_args);
        }elseif($layout == 'grid-v2' || $layout == 'list-v2') {
            get_template_part('template-parts/listing/listing', 'item-v2', $template_args);
        } else {
            get_template_part('template-parts/listing/listing', 'item', $template_args);
        }

    endwhile;

    wp_reset_postdata();

    homey_pagination_halfmap( $query_args->max_num_pages, $paged, $range = 2 );

    $listings_html = ob_get_contents();
    ob_end_clean();

    if( count($listings) > 0 ) {
        echo json_encode( array( 'getListings' => true, 'all_listings_for_pins' => $all_listings_for_pins, 'listings' => $listings, 'total_results' => $total_listings.' '.$rental_text, 'listingHtml' => $listings_html ) );
        exit();
    } else {
        echo json_encode( array( 'getListings' => false, 'total_results' => $total_listings.' '.$rental_text ) );
        exit();
    }
    die();
}