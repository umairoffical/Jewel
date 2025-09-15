<?php
/**
 * Enqueue scripts and styles.
 */
function homey_scripts() {
    global $paged, $post, $current_user;
    wp_get_current_user();
    $userID = $current_user->ID;
    $homey_local = homey_get_localization();
    $header_map_selected_city = $header_type = $homey_allow_additional_guests = $homey_num_additional_guests = $login_redirect = $allowed_guests = $is_singular_experience = $is_singular_listing = $booking_start_hour = $booking_end_hour = $homey_min_book_days = $header_exp_map_selected_cities = '';
    $is_listing_detail = 'no';
    $is_experience_detail = 'no';
    $booked_hours_array = $pending_hours_array = array();

    $after_login_redirect = homey_option('login_redirect');
    $login_redirect = homey_after_login_redirect_page();

    $map_api_key = homey_option('map_api_key');
    $menu_sticky = homey_option('menu-sticky');
    $search_position = homey_option('search_position');
    $geo_country_limit = homey_option('geo_country_limit');
    $geocomplete_country = homey_option('geocomplete_country');

    $homey_booking_type = homey_booking_type();

    if( isset($_GET['edit_listing']) && $_GET['edit_listing'] != '' ) {
        $edit_listing_id = $_GET['edit_listing'];
        $homey_booking_type = homey_booking_type_by_id($edit_listing_id);
    }

    if( isset($_GET['edit_experience']) && $_GET['edit_experience'] != '' ) {
        $edit_experience_id = $_GET['edit_experience'];
        $homey_booking_type = homey_booking_type_by_id($edit_experience_id);
    }

    $replytocom = isset($_GET['replytocom']) ? $_GET['replytocom'] : '';

    if (!is_404() && !is_search() && !is_tax() && !is_author()) {
        
        $header_map_selected_city = isset($post->ID) ? get_post_meta($post->ID, 'homey_map_city', false) : '';
        $header_exp_map_selected_cities = isset($post->ID) ? get_post_meta($post->ID, 'homey_experiences_map_city', false) : '';
        $header_type = isset($post->ID) ? get_post_meta($post->ID, 'homey_header_type', true) : '';
    }

    $homey_current_lang = get_locale();
    $homey_current_lang = explode('_', $homey_current_lang);


    $edit_listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
    $edit_listing_page = homey_get_template_link_2('template/dashboard-submission.php');
    $edit_listing_calendar = add_query_arg( array(
        'edit_listing' => $edit_listing_id,
        'tab' => 'calendar'
    ), $edit_listing_page );

    $edit_listing_pricing = add_query_arg( array(
        'edit_listing' => $edit_listing_id,
        'tab' => 'pricing'
    ), $edit_listing_page );

    if(is_singular('listing')) {

        $homey_allow_additional_guests = get_post_meta($post->ID, 'homey_allow_additional_guests', true);
        $homey_num_additional_guests = get_post_meta($post->ID, 'homey_num_additional_guests', true);
        $allowed_guests = get_post_meta($post->ID, 'homey_guests', true);
        $is_singular_listing = 'yes';

        $booking_start_hour = get_post_meta($post->ID, 'homey_start_hour',true );
        $booking_end_hour = get_post_meta($post->ID, 'homey_end_hour',true );
        $homey_min_book_days = get_post_meta($post->ID, 'homey_min_book_days',true );
        $booked_hours_array = homey_get_booked_hours_slots($post->ID);
        $pending_hours_array = homey_get_pending_hours_slots($post->ID);

        if(empty($booking_start_hour)) {
            $booking_start_hour = '01:00';
        }

        if(empty($booking_end_hour)) {
            $booking_end_hour = '24:00';
        }

        $is_listing_detail = 'yes';
        
    }


    // data type => experience
    $edit_experience_id = isset($_GET['edit_experience']) ? $_GET['edit_experience'] : '';
    $edit_experience_page = homey_get_template_link_2('template/dashboard-experience-submission.php');
    $edit_experience_calendar = add_query_arg( array(
        'edit_experience' => $edit_experience_id,
        'tab' => 'calendar'
    ), $edit_experience_page );

    $edit_experience_pricing = add_query_arg( array(
        'edit_experience' => $edit_experience_id,
        'tab' => 'pricing'
    ), $edit_experience_page );

    if(is_singular('experience')) {

        $homey_allow_additional_guests = get_post_meta($post->ID, 'homey_allow_additional_guests', true);
        $homey_num_additional_guests = get_post_meta($post->ID, 'homey_num_additional_guests', true);
        $allowed_guests = get_post_meta($post->ID, 'homey_guests', true);
        $is_singular_experience = 'yes';

        $booking_start_hour = get_post_meta($post->ID, 'homey_start_hour',true );
        $booking_end_hour = get_post_meta($post->ID, 'homey_end_hour',true );
        $homey_min_book_days = get_post_meta($post->ID, 'homey_min_book_days',true );
        $booked_hours_array = homey_get_booked_hours_slots($post->ID);
        $pending_hours_array = homey_get_pending_hours_slots($post->ID);

        if(empty($booking_start_hour)) {
            $booking_start_hour = '01:00';
        }

        if(empty($booking_end_hour)) {
            $booking_end_hour = '24:00';
        }

        $is_experience_detail = 'yes';

    }

    // /data type => experience

    $homey_logged_in = 'yes';
    if (!is_user_logged_in()) {
        $homey_logged_in = 'no';
    }

    $markerPricePins = homey_option('markerPricePins');
    if(isset($_GET['marker']) && $_GET['marker'] == 'pricePins') {
        $markerPricePins = 'yes';
    }

    $protocol = is_ssl() ? 'https' : 'http';

    if (is_rtl()) {
        $homey_rtl = "yes";
    } else {
        $homey_rtl = "no";
    }

    $enable_reCaptcha = homey_option('enable_reCaptcha');
    $recaptha_site_key = homey_option('recaptha_site_key');
    $recaptha_secret_key = homey_option('recaptha_secret_key');

    //Logos
    $simple_logo = homey_option('custom_logo', '', 'url');
    $retina_logo = homey_option('retina_logo', '', 'url');
    $mobile_logo = homey_option('mobile_logo', '', 'url');
    $mobile_retina_logo = homey_option('mobile_retina_logo', '', 'url');
    $retina_logo_mobile = homey_option('mobile_retina_logo', '', 'url');
    $custom_logo_mobile_splash = homey_option('custom_logo_mobile_splash', '', 'url');
    $retina_logo_mobile_splash = homey_option('retina_logo_mobile_splash', '', 'url');
    $custom_logo_splash = homey_option('custom_logo_splash', '', 'url');
    $retina_logo_splash = homey_option('retina_logo_splash', '', 'url');

    $map_cluster = homey_option('map_cluster', '', 'url');
    if (!empty($map_cluster)) {
        $clusterIcon = $map_cluster;
    } else {
        $clusterIcon = get_template_directory_uri() . '/images/cluster-icon.png';
    }

    $minify_css = homey_option('minify_css');
    $css_minify_prefix = '';
    if ($minify_css != 0) {
        $css_minify_prefix = '.min';
    }

    $minify_js = homey_option('minify_js');
    $js_minify_prefix = '';
    if ($minify_js != 0) {
        $js_minify_prefix = '.min';
    }


    /* Register Styles
        * ----------------------*/

    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', array(), '3.3.7', 'all');
    wp_enqueue_style('bootstrap-select', get_template_directory_uri() . '/css/bootstrap-select.min.css', array(), '1.7.2', 'all');
    wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/font-awesome.css', array(), '4.7.0', 'all');
    wp_enqueue_style('homey-icons', get_template_directory_uri() . '/css/icons.css', array(), '1.0.0', 'all');
    //wp_enqueue_style('swipebox', get_template_directory_uri() . '/css/swipebox.min.css', array(), '1.3.0', 'all');
    //wp_enqueue_style('fancybox', get_template_directory_uri() . '/css/jquery.fancybox-1.3.4.css', array(), '1.3.4', 'all');
    wp_enqueue_style('fancybox', get_template_directory_uri() . '/css/fancybox-3.min.css', array(), '3', 'all');
    wp_enqueue_style('slick', get_template_directory_uri() . '/css/slick.css', array(), '1.0.0', 'all');
    wp_enqueue_style('slick-theme', get_template_directory_uri() . '/css/slick-theme.css', array(), '1.0.0', 'all');
    wp_enqueue_style('jquery-ui', get_template_directory_uri() . '/css/jquery-ui.css', array(), '1.12.0', 'all');
    wp_enqueue_style('radio-checkbox', get_template_directory_uri() . '/css/radio-checkbox.css', array(), '1.0.0', 'all');

    if( (is_singular('listing') || is_page_template('template/dashboard-submission.php') ) && $homey_booking_type == 'per_hour') {
        
        wp_enqueue_script('fullcalendar-core', get_template_directory_uri() . '/js/fullcalendar/core/main.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-local-all', get_template_directory_uri() . '/js/fullcalendar/core/locales-all.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-daygrid', get_template_directory_uri() . '/js/fullcalendar/daygrid/main.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-timegrid', get_template_directory_uri() . '/js/fullcalendar/timegrid/main.min.js', array('jquery'), '4.0.2', true);

        wp_enqueue_style('fullcalendar-css', get_template_directory_uri() . '/css/fullcalendar.min.css', array(), HOMEY_THEME_VERSION, 'all');
        wp_enqueue_style('fullcalendar-css2', get_template_directory_uri() . '/js/fullcalendar/core/main.min.css', array(), HOMEY_THEME_VERSION, 'all');
        wp_enqueue_style('fullcalendar-css3', get_template_directory_uri() . '/js/fullcalendar/timegrid/main.min.css', array(), HOMEY_THEME_VERSION, 'all');

    }

    if( (is_singular('experience') || is_page_template('template/dashboard-experience-submission.php') ) && $homey_booking_type == 'per_hour') {
        wp_enqueue_script('fullcalendar-core', get_template_directory_uri() . '/js/fullcalendar/core/main.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-local-all', get_template_directory_uri() . '/js/fullcalendar/core/locales-all.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-daygrid', get_template_directory_uri() . '/js/fullcalendar/daygrid/main.min.js', array('jquery'), '4.0.2', true);
        wp_enqueue_script('fullcalendar-timegrid', get_template_directory_uri() . '/js/fullcalendar/timegrid/main.min.js', array('jquery'), '4.0.2', true);
    }
    
    if (is_rtl()) {
        wp_enqueue_style('homey-rtl', get_template_directory_uri() . '/css/rtl'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');
        wp_enqueue_style('bootstrap-rtl.min', get_template_directory_uri() . '/css/bootstrap-rtl.min.css', array(), '3.3.4', 'all');
    } else {
        wp_enqueue_style('homey-main', get_template_directory_uri() . '/css/main'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');
    }

    wp_enqueue_style('homey-styling-options', get_template_directory_uri() . '/css/styling-options'.$css_minify_prefix.'.css', array(), HOMEY_THEME_VERSION, 'all');

    
    wp_enqueue_style('homey-style', get_stylesheet_uri(), array(), HOMEY_THEME_VERSION, 'all');
    
    if(homey_option("show_radius") == 0){
        wp_enqueue_style('radius_hide', get_template_directory_uri() . '/css/hide_radius.min.css', array(), '1.0.0', 'all');
    }

    /* Register Scripts
        * ----------------------*/
    wp_enqueue_script('moment', get_template_directory_uri() . '/js/moment.min.js', array('jquery'), '2.17.1', true);
    wp_enqueue_script('modernizr-custom', get_template_directory_uri() . '/js/modernizr.custom.js', array('jquery'), '3.2.0', true);
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
    wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick.min.js', array('jquery'), '1.0.0', true);
    
    wp_enqueue_script('jquery-fancybox', get_template_directory_uri() . '/js/fancybox-3.min.js', array('jquery'), '3', true);

    wp_enqueue_script('bootstrap-select', get_template_directory_uri() . '/js/bootstrap-select.min.js', array('jquery'), '1.12.4', true);
    wp_enqueue_script('bootstrap-slider', get_template_directory_uri() . '/js/bootstrap-slider.min.js', array('jquery'), '10.0.2', true);

    wp_enqueue_script('parallax-background', get_template_directory_uri() . '/js/parallax-background.min.js', array('jquery'), '1.2', true);
    wp_enqueue_script('jquery-matchHeight', get_template_directory_uri() . '/js/jquery.matchHeight-min.js', array('jquery'), '0.7.2', true);
    wp_enqueue_script('jquery-vide', get_template_directory_uri() . '/js/jquery.vide.min.js', array('jquery'), '0.5.1', true);
    wp_enqueue_script('theia-sticky-sidebar', get_template_directory_uri() . '/js/theia-sticky-sidebar.js', array('jquery'), '0.5.1', true);

    wp_enqueue_script('jquery-effects-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script( 'jquery-ui-slider' );

    $woo_checkout_url = '';
    if ( class_exists( 'WooCommerce' ) ) {
        $woo_checkout_url = wc_get_checkout_url();
    }
    
    if( homey_is_halfmap_page() ) { 
        $homey_booking_type = get_post_meta( $post->ID, 'homey_halfmap_booking_type', true );
        
    }

    if( homey_is_listing_page() ) { 
        $homey_booking_type = get_post_meta( $post->ID, 'homey_listings_booking_type', true );
    }

    // Ajax Calls
    wp_enqueue_script('homey-ajax-calls', get_stylesheet_directory_uri() . '/js/homey-ajax'.$js_minify_prefix.'.js', array('jquery', 'bootstrap'), HOMEY_THEME_VERSION, true);
    wp_localize_script('homey-ajax-calls', 'HOMEY_ajax_vars',
        array(
            'admin_url' => get_admin_url(),
            'homey_header_slider_autoplay' => homey_option('homey_header_slider_autoplay', 1),
            'homey_is_rtl' => $homey_rtl,
            'redirect_type' => $after_login_redirect,
            'login_redirect' => $login_redirect,
            'woo_checkout_url' => esc_url($woo_checkout_url),
            'login_loading' => esc_html__('Sending user info, please wait...', 'homey'),
            'direct_pay_text' => esc_html__('Processing, Please wait...', 'homey'),
            'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
            'already_registered_text' => esc_html__('You are already registered, please login here.', 'homey'),
            'already_booked_text' => esc_html__('Sorry, someone booked the dates, please go back and select new dates.', 'homey'),
            'already_login_text' => esc_html__('You are already login, please refresh the page.', 'homey'),
            'user_id' => $userID,
            'is_singular_listing' => $is_singular_listing,
            'is_singular_experience' => $is_singular_experience,
            'process_loader_refresh' => 'homey-icon homey-icon-loading-half fa-refresh',
            'process_loader_spinner' => 'homey-icon homey-icon-loading-half fa-spinner',
            'process_loader_circle' => 'homey-icon homey-icon-loading-half fa-circle-o-notch',
            'process_loader_cog' => 'homey-icon homey-icon-loading-half fa-cog',
            'success_icon' => 'homey-icon homey-icon-check-circle-1',
            'stripe_publishable_key' => homey_option('stripe_publishable_key', ''),

            'add_compare' => $homey_local['add_compare'],
            'remove_compare' => $homey_local['remove_compare'],
            'compare_limit' => $homey_local['compare_limit'],
            'compare_url' => homey_get_template_link_2('template/template-compare.php'),

            'add_compare_exp' => $homey_local['add_compare_exp'],
            'remove_compare_exp' => $homey_local['remove_compare_exp'],
            'compare_limit_exp' => $homey_local['compare_limit_exp'],
            'compare_url_exp' => homey_get_template_link_2('template/template-compare-exp.php'),

            'prev_text' => $homey_local['prev_text'],
            'next_text' => $homey_local['next_text'],
            'are_you_sure_text' => $homey_local['are_you_sure_text'],
            'delete_btn_text' => $homey_local['delete_btn'],
            'cancel_btn_text' => $homey_local['cancel_btn'],
            'confirm_btn_text' => esc_html__('Confirm', 'homey'),
            'paypal_connecting' => esc_html__('Connecting to paypal, Please wait... ', 'homey'),
            'currency_updating_msg' => esc_html__('Updating Currency, Please wait...', 'homey'),
            'agree_term_text' => $homey_local['agree_term_text'],
            'choose_gateway_text' => $homey_local['choose_gateway_text'],

            'homey_tansparent_logo' => homey_is_transparent_logo(),
            'homey_transparent_logo' => homey_is_transparent_logo(),

            'homey_is_tansparent_logo' => homey_is_transparent_logo(),
            'homey_is_transparent_logo' => homey_is_transparent_logo(),

            'homey_is_transparent' => homey_is_transparent(),

            'homey_tansparent' => homey_is_transparent(),

            'homey_is_top_header' => homey_is_top_header(),
            'simple_logo' => $simple_logo,
            'retina_logo' => $retina_logo,
            'mobile_logo' => $mobile_logo,
            'retina_logo_mobile' => $retina_logo_mobile,
            'custom_logo_mobile_splash' => $custom_logo_mobile_splash,
            'retina_logo_mobile_splash' => $retina_logo_mobile_splash,
            'custom_logo_splash' => $custom_logo_splash,
            'retina_logo_splash' => $retina_logo_splash,
            'no_more_listings' => $homey_local['no_more_listings'],
            'no_more_experiences' => $homey_local['no_more_experiences'],
            'allow_additional_guests' => $homey_allow_additional_guests,
            'allowed_guests_num' => $allowed_guests,
            'num_additional_guests' => $homey_num_additional_guests,
            'homey_reCaptcha' => $enable_reCaptcha,

            'calendar_link' => $edit_listing_calendar,
            'pricing_link' => $edit_listing_pricing,

            'exp_calendar_link' => $edit_experience_calendar,
            'exp_pricing_link' => $edit_experience_pricing,

            'search_position' => $search_position,
            'replytocom' => $replytocom,
            'homey_is_dashboard' => homey_is_dashboard(),
            'is_listing_detail' => $is_listing_detail,
            'homey_booking_type' => $homey_booking_type,
            'booked_hours_array' => json_encode($booked_hours_array),
            'pending_hours_array' => json_encode($pending_hours_array),
            'booking_start_hour' => $booking_start_hour,
            'booking_end_hour' => $booking_end_hour,
            'hc_reserved_label' => $homey_local['hc_reserved_label'],
            'hc_pending_label' => $homey_local['hc_pending_label'],
            'hc_hours_label' => $homey_local['hc_hours_label'],
            'hc_today_label' => $homey_local['hc_today_label'],
            'homey_timezone' => get_option('timezone_string'),
            'homey_current_lang' => $homey_current_lang,
            'homey_date_format' => homey_option('homey_date_format'),
            'geo_country_limit' => $geo_country_limit,
            'homey_calendar_months' => homey_calendar_months(),
            'geocomplete_country' => $geocomplete_country,
            'homey_min_book_days' => $homey_min_book_days,
            'homey_login_register_msg_text' => esc_html__('Error: Something wrong happened. If you are not able to login, contact to Website Administrator.', 'homey'),

        )
    ); // end ajax calls
    
    wp_enqueue_script('homey-custom', get_template_directory_uri() . '/js/custom'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
    $enable_phone_number = homey_option('enable_phone_number', 'yes');
    if($enable_phone_number == 'yes') {
        wp_enqueue_style( 'homey-int-tel-css', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css', array(), HOMEY_THEME_VERSION, 'all' );
        wp_enqueue_script('homey-int-tel', get_template_directory_uri() . '/js/intlTelInput.min.js', array('jquery'), HOMEY_THEME_VERSION, true);
    }

    $bedrooms_icon = homey_option('lgc_bedroom_icon'); 
    $bathroom_icon = homey_option('lgc_bathroom_icon'); 
    $guests_icon = homey_option('lgc_guests_icon');

    if(!empty($bedrooms_icon)) {
        $bedrooms_icon = '<i class="'.esc_attr($bedrooms_icon).'"></i>';
    }
    if(!empty($bathroom_icon)) {
        $bathroom_icon = '<i class="'.esc_attr($bathroom_icon).'"></i>';
    }
    if(!empty($guests_icon)) {
        $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
    }

    $arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
    $depart = isset($_GET['depart']) ? $_GET['depart'] : '';
    $guests = isset($_GET['guest']) ? $_GET['guest'] : '';
    $pets = isset($_GET['pets']) ? $_GET['pets'] : '';
    $bedrooms = isset($_GET['bedrooms']) ? $_GET['bedrooms'] : '';
    $rooms = isset($_GET['rooms']) ? $_GET['rooms'] : '';
    $room_size = isset($_GET['room_size']) ? $_GET['room_size'] : '';
    $search_country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
    $search_city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
    $search_area = isset($_GET['search_area']) ? $_GET['search_area'] : '';
    $listing_type = isset($_GET['listing_type']) ? $_GET['listing_type'] : '';
    $min_price = isset($_GET['min-price']) ? $_GET['min-price'] : '';
    $max_price = isset($_GET['max-price']) ? $_GET['max-price'] : '';
    $area = isset($_GET['area']) ? $_GET['area'] : '';
    $amenity = isset($_GET['amenity']) ? $_GET['amenity'] : '';
    $facility = isset($_GET['facility']) ? $_GET['facility'] : '';
    $language = isset($_GET['language']) ? $_GET['language'] : '';
    $experience_type = isset($_GET['experience_type']) ? $_GET['experience_type'] : '';
    $country = isset($_GET['country']) ? $_GET['country'] : '';
    $state = isset($_GET['state']) ? $_GET['state'] : '';
    $city = isset($_GET['city']) ? $_GET['city'] : '';
    $area = isset($_GET['area']) ? $_GET['area'] : '';
    $booking_type = isset($_GET['booking_type']) ? $_GET['booking_type'] : '';
    $start_time = isset($_GET['start']) ? $_GET['start'] : '';
    $end_time = isset($_GET['end']) ? $_GET['end'] : '';
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $lat = isset($_GET['lat']) ? $_GET['lat'] : '';
    $lng = isset($_GET['lng']) ? $_GET['lng'] : '';
    $radius = isset($_GET['radius']) ? $_GET['radius'] : '';

    $default_radius = homey_option('default_radius');
    if(isset($_GET['radius'])) {
        $default_radius = $_GET['radius'];
    }

    $pin_cluster = homey_option('pin_cluster_enable');
    $pin_cluster_icon = homey_option('pin_cluster', false, 'url');
    $pin_cluster_zoom = homey_option('pin_cluster_zoom');
    $set_initial_zoom = homey_option('set_initial_zoom');

    //API and Ajax Calls for map

    if( homey_get_map_system() == 'google' ) {
        if (is_ssl()) {
            wp_enqueue_script('google-map', 'https://maps-api-ssl.google.com/maps/api/js?libraries=places&language=' . get_locale() . '&key='.esc_html($map_api_key).'', array('jquery'), '1.0', false);
        } else {
            wp_enqueue_script('google-map', 'http://maps.googleapis.com/maps/api/js?libraries=places&language=' . get_locale() . '&key='.esc_html($map_api_key).'', array('jquery'), '1.0', false);
        }

        if($markerPricePins == 'yes') {
            wp_enqueue_script('richmarker-compiled', get_template_directory_uri() . '/js/richmarker-compiled.js', array(), '1.0.0', true);
        }

        wp_enqueue_script('infobox-packed', get_template_directory_uri() . '/js/infobox_packed.js', array('jquery'), '1.1.19', false);

        if( is_page_template('template/template-exp-search.php')  
            || is_page_template('template/template-experience-sticky-map.php')
            || is_page_template('template/template-half-map-exp.php')
            || is_page_template('template/dashboard-experience-submission.php')
            || $header_type == 'experiences_map'
            || is_singular('experience')
        ){
            wp_enqueue_script('homey-maps', get_template_directory_uri() . '/js/homey-exp-maps'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
        }else{
            wp_enqueue_script('homey-maps', get_template_directory_uri() . '/js/homey-maps'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
        }

        wp_enqueue_script('markerclusterer-min', get_template_directory_uri() . '/js/markerclusterer.min.js', array('jquery'), '2.1.1', true);

    } else {
        // Enqueue leaflet CSS
        wp_enqueue_style( 'leaflet', get_template_directory_uri() . '/js/leaflet/leaflet.css', array(), '1.9.3' );

        // Enqueue leaflet JS
        wp_enqueue_script( 'leaflet', get_template_directory_uri() . '/js/leaflet/leaflet.js', array(), '1.9.3', true );

        if( homey_option('pin_cluster_enable') == 'yes' ) {
            wp_enqueue_style('leafletMarkerCluster', get_template_directory_uri() . '/js/leafletCluster/MarkerCluster.css', array(), '1.4.0', 'all');
            wp_enqueue_style('leafletMarkerClusterDefault', get_template_directory_uri() . '/js/leafletCluster/MarkerCluster.Default.css', array(), '1.4.0', 'all');
            wp_enqueue_script('leafletMarkerCluster', get_template_directory_uri() . '/js/leafletCluster/leaflet.markercluster.js', array('leaflet'), '1.4.0', false);
        }

        wp_enqueue_script( 'jquery-ui-autocomplete' );

        if( is_page_template('template/template-exp-search.php')  
            || is_page_template('template/template-experience-sticky-map.php')
            || is_page_template('template/template-half-map-exp.php')
            || is_page_template('template/dashboard-experience-submission.php')
            || $header_type == 'experiences_map'
            || is_singular('experience')
        ){ 
            wp_enqueue_script('homey-maps', get_template_directory_uri() . '/js/homey-open-street-exp-maps'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
        }else{ 
            wp_enqueue_script('homey-maps', get_stylesheet_directory_uri() . '/js/homey-open-street-maps'.$js_minify_prefix.'.js', array('jquery'), HOMEY_THEME_VERSION, true);
        }
    }

    wp_localize_script('homey-maps', 'HOMEY_map_vars',
        array(
            'admin_url' => get_admin_url(),
            'user_id' => $userID,
            'homey_is_rtl' => $homey_rtl,
            'is_singular_listing' => $is_singular_listing,
            'header_map_city' => $header_map_selected_city,
            'header_exp_map_cities' => $header_exp_map_selected_cities,
            'markerPricePins' => $markerPricePins,
            'pin_cluster' => $pin_cluster,
            'pin_cluster_icon' => $pin_cluster_icon,
            'pin_cluster_zoom' => $pin_cluster_zoom,
            'set_initial_zoom' => $set_initial_zoom,
            'geo_country_limit' => $geo_country_limit,
            'geocomplete_country' => $geocomplete_country,
            'infoboxClose' => get_template_directory_uri() . '/images/close.gif',
            'google_map_style' => homey_option('googlemap_stype'),
            'not_found' => esc_html__("We didn't find any results", 'homey'),
            'is_mapbox' => homey_option('homey_map_system'),
            'api_mapbox' => homey_option('mapbox_api_key'),
            'arrive' => $arrive,
            'depart' => $depart,
            'guests' => $guests,
            'pets' => $pets,
            'search_country' => $search_country,
            'search_city' => $search_city,
            'search_area' => $search_area,
            'listing_type' => $listing_type,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'area' => $area,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'bedrooms' => $bedrooms,
            'rooms' => $rooms,
            'room_size' => $room_size,
            'booking_type' => $booking_type,
            'amenity' => $amenity,
            'facility' => $facility,
            'language' => $language,
            'host_languages' => $language,
            'experience_type' => $experience_type,
            'keyword' => $keyword,
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
            'bedrooms_icon' => $bedrooms_icon,
            'bathroom_icon' => $bathroom_icon,
            'guests_icon' => $guests_icon,
            'default_lat' => homey_option('default_lat'),
            'default_lng' => homey_option('default_lng'),
            'homey_default_radius' => $default_radius,
            'all_listings_for_pin' => homey_option('show_all_listing_pins_on_map', 0),
        )
    ); // end ajax calls

    //Listing Submission 
    if ( homey_is_dashboard() ) {

        $booked_hours_array2 = $pending_hours_array2 = $booking_start_hour2 = $booking_end_hour2 = array();
        
        $edit_listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';

        if(!empty($edit_listing_id)) {
            $edit_listing_id = intval( trim( $edit_listing_id ) );

            $booking_start_hour2 = get_post_meta($edit_listing_id, 'homey_start_hour',true );
            $booking_end_hour2 = get_post_meta($edit_listing_id, 'homey_end_hour',true );
            $booked_hours_array2 = homey_get_booked_hours_slots($edit_listing_id);
            $pending_hours_array2 = homey_get_pending_hours_slots($edit_listing_id);

            if(empty($booking_start_hour2)) {
                $booking_start_hour2 = '01:00';
            }

            if(empty($booking_end_hour2)) {
                $booking_end_hour2 = '24:00';
            }
        }

        wp_enqueue_script('plupload');
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_script('jquery-validate-min', get_template_directory_uri() . '/js/jquery.validate.min.js', array('jquery'), '1.15.0', true);
        wp_enqueue_script('bootbox-min', get_template_directory_uri() . '/js/bootbox.min.js', array('jquery'), '4.4.0', true);

        if( isset($_GET['edit_listing']) && $_GET['edit_listing'] != '' ) {
            $edit_listing_id = $_GET['edit_listing'];
            $homey_booking_type = homey_booking_type_by_id($edit_listing_id);
        } else {
            $homey_booking_type = isset($_GET['mode']) ? $_GET['mode'] : '';
        }

        if($homey_booking_type == 'per_hour') {
            $ex_per_night = esc_html__('Per Hour', 'homey');
            $ex_per_night_per_guest = esc_html__('Per Hour Per Guest', 'homey');
        } else if($homey_booking_type == 'per_week') {
            $ex_per_night = esc_html__('Per Week', 'homey');
            $ex_per_night_per_guest = esc_html__('Per Week Per Guest', 'homey');
        } else if($homey_booking_type == 'per_month') {
            $ex_per_night = esc_html__('Per Month', 'homey');
            $ex_per_night_per_guest = esc_html__('Per Month Per Guest', 'homey');
        } else {
            $ex_per_night = $homey_local['ex_per_night'];
            $ex_per_night_per_guest = $homey_local['ex_per_night_per_guest'];
        }

        if (is_page_template('template/dashboard-experience-submission.php') || is_page_template('template/dashboard-experience-submitted.php')  || is_page_template('template/dashboard-experiences.php')
            || is_page_template('template/dashboard-experience-list.php') || is_page_template('template/dashboard-reservations-experiences.php') || is_page_template('template/dashboard-reservations2-experiences.php')
        ){
            wp_enqueue_script('homey-experience', get_template_directory_uri() . '/js/homey-experience.js', array('jquery', 'plupload', 'jquery-ui-sortable'), HOMEY_THEME_VERSION, true);

            $experience_data = array(
                'ajaxURL' => admin_url('admin-ajax.php'),
                'verify_experience_gallery_nonce' => wp_create_nonce('verify_experience_gallery_nonce'),
                'verify_file_type' => esc_html__('Valid file formats', 'homey'),
                'msg_digits' => esc_html__('Please enter only digits', 'homey'),
                'homey_is_rtl' => $homey_rtl,
                'max_prop_images' => '',
                'image_max_file_size' => '',
                'homey_logged_in' => $homey_logged_in,
                'process_loader_refresh' => 'homey-icon homey-icon-loading-half fa-refresh',
                'process_loader_spinner' => 'homey-icon homey-icon-loading-half fa-spinner',
                'process_loader_circle' => 'homey-icon homey-icon-loading-half fa-circle-o-notch',
                'process_loader_cog' => 'homey-icon homey-icon-loading-half fa-cog',
                'success_icon' => 'homey-icon homey-icon-check-circle-1',
                'are_you_sure_text' => $homey_local['are_you_sure_text'],
                'delete_btn_text' => $homey_local['delete_btn'],
                'cancel_btn_text' => $homey_local['cancel_btn'],
                'confirm_btn_text' => esc_html__('Confirm', 'homey'),
                'login_loading' => esc_html__('Sending user info, please wait...', 'homey'),
                'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
                'already_login_text' => esc_html__('You are already registered, please login here.', 'homey'),
                'add_experience_msg' => esc_html__('Submitting, Please wait...', 'homey'),
                'both_required' => esc_html__('Both fields required.', 'homey'),
                'discount_value' => esc_html__('Enter discount value', 'homey'),
                'btn_save' => esc_html__('Save', 'homey'),
                'acc_bedroom_name' => homey_option('ad_acc_bedroom_name'),
                'acc_bedroom_name_plac' => homey_option('ad_acc_bedroom_name_plac'),
                'acc_guests' => homey_option('ad_acc_guests'),
                'acc_guests_plac' => homey_option('ad_acc_guests_plac'),
                'uploaded_of_text' => esc_html__('Uploaded Of', 'homey'),
                'process_completed_text' => esc_html__('Process completed', 'homey'),
                'acc_no_of_beds' => homey_option('ad_acc_no_of_beds'),
                'acc_no_of_beds_plac' => homey_option('ad_acc_no_of_beds_plac'),
                'acc_bedroom_type' => homey_option('ad_acc_bedroom_type'),
                'acc_bedroom_type_plac' => homey_option('ad_acc_bedroom_type_plac'),
                'acc_btn_remove_room' => homey_option('ad_acc_btn_remove_room'),
                'service_name' => homey_option('ad_service_name'),
                'service_name_plac' => homey_option('ad_service_name_plac'),
                'service_price' => homey_option('ad_service_price'),
                'service_price_plac' => homey_option('ad_service_price_plac'),
                'service_des' => homey_option('ad_service_des'),
                'service_des_plac' => homey_option('ad_service_des_plac'),
                'btn_remove_service' => homey_option('ad_btn_remove_service'),
                'exp_calendar_link' => $edit_experience_calendar,
                'exp_pricing_link' => $edit_experience_pricing,
                'geo_coding' => esc_html__('Geocode was not successful for the following reason', 'homey'),
                'avail_label' => $homey_local['avail_label'],
                'unavail_label' => $homey_local['unavail_label'],
                'add_ical_feeds' => esc_html__('Please add feeds first.', 'homey'),
                'add_expense_msg' => esc_html__('Please add expense first.', 'homey'),
                'geo_country_limit' => $geo_country_limit,
                'geocomplete_country' => $geocomplete_country,
                'homey_booking_type' => $homey_booking_type,
                'booked_hours_array' => json_encode($booked_hours_array2),
                'pending_hours_array' => json_encode($pending_hours_array2),
                'booking_start_hour' => $booking_start_hour2,
                'booking_end_hour' => $booking_end_hour2,
                'hc_reserved_label' => $homey_local['hc_reserved_label'],
                'hc_pending_label' => $homey_local['hc_pending_label'],
                'hc_hours_label' => $homey_local['hc_hours_label'],
                'hc_today_label' => $homey_local['hc_today_label'],
                'ex_name' => $homey_local['ex_name'],
                'ex_name_plac' => $homey_local['ex_name_plac'],
                'ex_price' => $homey_local['ex_price'],
                'ex_price_plac' => $homey_local['ex_price_plac'],
                'ex_type' => $homey_local['ex_type'],
                'ex_type_plac' => $homey_local['ex_type_plac'],
                'ex_single_fee' => $homey_local['ex_single_fee'],
                'ex_per_night' => $ex_per_night,
                'ex_per_guest' => $homey_local['ex_per_guest'],
                'ex_per_night_per_guest' => $ex_per_night_per_guest,
                'homey_timezone' => get_option('timezone_string'),
                'homey_current_lang' => $homey_current_lang,
                'edit_tab' => isset($_GET['tab']) ? $_GET['tab'] : '',

                'what_to_bring_name' => esc_html__(esc_attr(homey_option('experience_what_bring_name'), 'homey')),
                'what_to_bring_name_plac' => esc_html__(esc_attr(homey_option('experience_what_bring_name_plac'), 'homey')),

                'what_to_bring_desc' => esc_html__('Description', 'homey'),
                'what_to_bring_desc_plac' => esc_html__('Type description here.', 'homey'),

                'what_to_provided_name' => esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name'), 'homey')),
                'what_to_provided_name_plac' => esc_html__(esc_attr(homey_option('experience_ad_acc_what_provide_name_plac'), 'homey')),

                'what_to_provided_desc' => esc_html__('Description', 'homey'),
                'what_to_provided_desc_plac' => esc_html__('Type description here.', 'homey'),

            );
            wp_localize_script('homey-experience', 'Homey_Experience', $experience_data);
        }else{
            wp_enqueue_script('homey-listing', get_stylesheet_directory_uri() . '/js/homey-listing.js', array('jquery', 'plupload', 'jquery-ui-sortable'), HOMEY_THEME_VERSION, true);

            $listing_data = array(
                'ajaxURL' => admin_url('admin-ajax.php'),
                'verify_nonce' => wp_create_nonce('verify_gallery_nonce'),
                'verify_file_type' => esc_html__('Valid file formats', 'homey'),
                'msg_digits' => esc_html__('Please enter only digits', 'homey'),
                'homey_is_rtl' => $homey_rtl,
                'max_prop_images' => '',
                'image_max_file_size' => '',
                'homey_logged_in' => $homey_logged_in,
                'process_loader_refresh' => 'homey-icon homey-icon-loading-half fa-refresh',
                'process_loader_spinner' => 'homey-icon homey-icon-loading-half fa-spinner',
                'process_loader_circle' => 'homey-icon homey-icon-loading-half fa-circle-o-notch',
                'process_loader_cog' => 'homey-icon homey-icon-loading-half fa-cog',
                'success_icon' => 'homey-icon homey-icon-check-circle-1',
                'are_you_sure_text' => $homey_local['are_you_sure_text'],
                'delete_btn_text' => $homey_local['delete_btn'],
                'cancel_btn_text' => $homey_local['cancel_btn'],
                'confirm_btn_text' => esc_html__('Confirm', 'homey'),
                'login_loading' => esc_html__('Sending user info, please wait...', 'homey'),
                'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
                'already_login_text' => esc_html__('You are already registered, please login here.', 'homey'),
                'add_listing_msg' => esc_html__('Submitting, Please wait...', 'homey'),
                'both_required' => esc_html__('Both fields required.', 'homey'),
                'discount_value' => esc_html__('Enter discount value', 'homey'),
                'btn_save' => esc_html__('Save', 'homey'),
                'acc_bedroom_name' => homey_option('ad_acc_bedroom_name'),
                'acc_bedroom_name_plac' => homey_option('ad_acc_bedroom_name_plac'),
                'acc_guests' => homey_option('ad_acc_guests'),
                'acc_guests_plac' => homey_option('ad_acc_guests_plac'),
                'acc_no_of_beds' => homey_option('ad_acc_no_of_beds'),
                'acc_no_of_beds_plac' => homey_option('ad_acc_no_of_beds_plac'),
                'acc_bedroom_type' => homey_option('ad_acc_bedroom_type'),
                'acc_bedroom_type_plac' => homey_option('ad_acc_bedroom_type_plac'),
                'acc_btn_remove_room' => homey_option('ad_acc_btn_remove_room'),

                'uploaded_of_text' => esc_html__('Uploaded Of', 'homey'),
                'process_completed_text' => esc_html__('Process completed', 'homey'),

                'service_name' => homey_option('ad_service_name'),
                'service_name_plac' => homey_option('ad_service_name_plac'),
                'service_price' => homey_option('ad_service_price'),
                'service_price_plac' => homey_option('ad_service_price_plac'),
                'service_des' => homey_option('ad_service_des'),
                'service_des_plac' => homey_option('ad_service_des_plac'),
                'btn_remove_service' => homey_option('ad_btn_remove_service'),
                'calendar_link' => $edit_listing_calendar,
                'pricing_link' => $edit_listing_pricing,
                'geo_coding' => esc_html__('Geocode was not successful for the following reason', 'homey'),
                'avail_label' => $homey_local['avail_label'],
                'unavail_label' => $homey_local['unavail_label'],
                'add_ical_feeds' => esc_html__('Please add feeds first.', 'homey'),
                'add_expense_msg' => esc_html__('Please add expense first.', 'homey'),
                'geo_country_limit' => $geo_country_limit,
                'geocomplete_country' => $geocomplete_country,
                'homey_booking_type' => $homey_booking_type,
                'booked_hours_array' => json_encode($booked_hours_array2),
                'pending_hours_array' => json_encode($pending_hours_array2),
                'booking_start_hour' => $booking_start_hour2,
                'booking_end_hour' => $booking_end_hour2,
                'hc_reserved_label' => $homey_local['hc_reserved_label'],
                'hc_pending_label' => $homey_local['hc_pending_label'],
                'hc_hours_label' => $homey_local['hc_hours_label'],
                'hc_today_label' => $homey_local['hc_today_label'],
                'ex_name' => $homey_local['ex_name'],
                'ex_name_plac' => $homey_local['ex_name_plac'],
                'ex_price' => $homey_local['ex_price'],
                'ex_price_plac' => $homey_local['ex_price_plac'],
                'ex_type' => $homey_local['ex_type'],
                'ex_type_plac' => $homey_local['ex_type_plac'],
                'ex_single_fee' => $homey_local['ex_single_fee'],
                'ex_per_night' => $ex_per_night,
                'ex_per_guest' => $homey_local['ex_per_guest'],
                'ex_per_night_per_guest' => $ex_per_night_per_guest,
                'homey_timezone' => get_option('timezone_string'),
                'homey_current_lang' => $homey_current_lang,
                'edit_tab' => isset($_GET['tab']) ? $_GET['tab'] : '',
                'is_edit_listing' => isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '',
            );
            wp_localize_script('homey-listing', 'Homey_Listing', $listing_data);
        }
    }

    // Edit profile template
    if (is_page_template('template/dashboard-profile.php') || homey_is_dashboard()) {
        wp_enqueue_script('plupload');
        wp_register_script('homey-profile', get_template_directory_uri() . '/js/homey-profile.js', array('jquery', 'plupload'), HOMEY_THEME_VERSION, true);
        wp_enqueue_script('homey-profile');

        $profile_data = array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'user_id' => $userID,
            'homey_upload_nonce' => wp_create_nonce('homey_upload_nonce'),
            'verify_file_type' => esc_html__('Valid file formats', 'homey'),
            'homey_site_url' => esc_url( home_url() ),
            'process_loader_refresh' => 'homey-icon homey-icon-loading-half fa-refresh',
            'process_loader_spinner' => 'homey-icon homey-icon-loading-half fa-spinner',
            'process_loader_circle' => 'homey-icon homey-icon-loading-half fa-circle-o-notch',
            'process_loader_cog' => 'homey-icon homey-icon-loading-half fa-cog',
            'success_icon' => 'homey-icon homey-icon-check-circle-1',
            'processing_text' => esc_html__('Processing, Please wait...', 'homey'),
            'already_login_text' => esc_html__('You are already registered, please login here.', 'homey'),
            'gdpr_agree_text' => esc_html__('Please Agree with GDPR', 'homey'),
            'sending_info' => esc_html__('Sending info', 'homey'),

            'profile_picture_req_text' => esc_html__('Profile Picture is required.', 'homey'),
            'first_name_req_text' => esc_html__('First Name is required.', 'homey'),
            'last_name_req_text' => esc_html__('Last Name is required.', 'homey'),
            'tell_about_req_text' => esc_html__('Tell About Yourself is required.', 'homey'),
            'mobile_num_req_text' => esc_html__('Mobile number is required.', 'homey'),
            'phone_num_req_text' => esc_html__('Phone number is required.', 'homey'),
        );
        wp_localize_script('homey-profile', 'homeyProfile', $profile_data);

    } // end edit profile


    if( homey_option('enable_stripe') ) {
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
        $reservation_page_link_host = homey_get_template_link('template/dashboard-reservations2.php');

        $reservation_exp_page_link = homey_get_template_link('template/dashboard-reservations-experiences.php');
        $reservation_exp_page_link_host = homey_get_template_link('template/dashboard-reservations2-experiences.php');

        $add_new_listing = homey_get_template_link('template/dashboard-submission.php');
        $add_new_experience = homey_get_template_link('template/dashboard-experience-submission.php');

        $reservation_id = isset($_GET['reservation_id']) ? $_GET['reservation_id'] : '';

        if(homey_is_renter()) {
            $reservation_return_link = $reservation_page_link;
            $reservation_exp_return_link = $reservation_exp_page_link;
        } else {
            $reservation_return_link = $reservation_page_link_host;
            $reservation_exp_return_link = $reservation_exp_page_link_host;
        }

        $return_link = add_query_arg(
            array(
                'edit_listing' => isset($_GET['upgrade_id']) ? $_GET['upgrade_id'] : '',
                'featured' => true
            ), $add_new_listing );

        $return_link_exp = add_query_arg(
            array(
                'edit_experience' => isset($_GET['upgrade_id']) ? $_GET['upgrade_id'] : '',
                'featured' => true
            ), $add_new_experience );

        wp_enqueue_script('stripe','https://js.stripe.com/v3/',array('jquery'), '1.0', true);

        wp_register_script('homey-stripe', get_stylesheet_directory_uri() . '/js/stripe-payment-enhanced.js', array('jquery'), HOMEY_THEME_VERSION, true);

        $is_experience_template = 0;
        if ( is_page_template('template/template-instance-exp-booking.php')
            || is_page_template('template/dashboard-exp-payment.php') )
        {
            $is_experience_template = 1;
        }

        wp_localize_script('homey-stripe', 'HOMEY_CHILD_stripe_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('homey_stripe_nonce'),
                'stripe_publishable_key' => homey_option('stripe_publishable_key', ''),
                'stripe_secret_key' => homey_option('stripe_secret_key', ''),
                'featured_return_link' => $return_link,
                'featured_return_link_exp' => $return_link_exp,
                'is_experience_template' => $is_experience_template,
                'reservation_return_link' => $reservation_return_link,
                'reservation_exp_return_link' => $reservation_exp_return_link,
                'req_name' => esc_html__('Name field required', 'homey'),
                'req_email' => esc_html__('Email field required', 'homey'),
                'req_phone' => esc_html__('Phone field required', 'homey'),
                'payment_failed' => esc_html__('Payment Failed, please make sure you have entered name, email and valid card number', 'homey'),

                'successful_message' => esc_html__('Successfully Paid, Redirecting...', 'homey'),
            )
        ); // end vars
        wp_enqueue_script('homey-stripe');
    }


    if ($enable_reCaptcha != 0 && !empty($recaptha_site_key) && !empty($recaptha_secret_key)) {
        wp_enqueue_script('google-reCaptcha', 'https://www.google.com/recaptcha/api.js?onload=homeyReCaptchaLoad&hl=' . get_locale() . '&render=explicit', array('jquery'), HOMEY_THEME_VERSION, true);
        wp_enqueue_script('homey-reCaptcha', get_template_directory_uri() . '/js/homey-reCapthca.js', array('jquery', 'google-reCaptcha'), HOMEY_THEME_VERSION, true);

        $reCaptcha_data = array(
            'site_key' => $recaptha_site_key,
            'secret_key' => $recaptha_secret_key,
            'is_singular_listing' => $is_singular_listing,
            //'homey_show_captcha' => $homey_show_captcha,
            'homey_logged_in' => $homey_logged_in,

        );
        wp_localize_script('homey-reCaptcha', 'homey_reCaptcha', $reCaptcha_data);
    }
    
    if (is_singular('post') && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}