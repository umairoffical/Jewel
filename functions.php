<?php
// Load enhanced payment processor
require_once get_stylesheet_directory() . '/framework/functions/class-homey-payment-processor.php';

// Load dynamic settings
require_once get_stylesheet_directory() . '/framework/functions/class-homey-dynamic-settings.php';

// Load dynamic settings admin panel
require_once get_stylesheet_directory() . '/framework/options/stripe-dynamic-settings.php';

// Load Stripe user helper functions
require_once get_stylesheet_directory() . '/framework/functions/stripe-user-helpers.php';

// Load Stripe classes early
function homey_child_load_stripe_early() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Starting Stripe SDK initialization...');
    }

    // First check if Stripe is already loaded
    if (class_exists('\Stripe\Stripe')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Stripe SDK already loaded');
        }
        return;
    }

    // Try loading from the parent theme/plugin
    if (defined('HOMEY_PLUGIN_PATH')) {
        $stripe_sdk_path = HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php';
        if (file_exists($stripe_sdk_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Loading Stripe SDK from plugin: ' . $stripe_sdk_path);
            }
            require_once $stripe_sdk_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Stripe SDK not found in plugin path: ' . $stripe_sdk_path);
            }
        }
    }
    
    // If still not loaded, try composer's autoload
    if (!class_exists('\Stripe\Stripe')) {
        $composer_autoload = get_stylesheet_directory() . '/vendor/autoload.php';
        if (file_exists($composer_autoload)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Loading Stripe SDK from composer: ' . $composer_autoload);
            }
            require_once $composer_autoload;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Composer autoload not found: ' . $composer_autoload);
            }
        }
    }
    
    // If still not loaded, try direct include
    if (!class_exists('\Stripe\Stripe')) {
        $direct_stripe_path = get_stylesheet_directory() . '/framework/functions/stripe-php/init.php';
        if (file_exists($direct_stripe_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Loading Stripe SDK directly: ' . $direct_stripe_path);
            }
            require_once $direct_stripe_path;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Direct Stripe SDK not found: ' . $direct_stripe_path);
            }
        }
    }
    
    // Always load our initialization
    if (file_exists(get_stylesheet_directory() . '/framework/functions/stripe-init.php')) {
        require_once get_stylesheet_directory() . '/framework/functions/stripe-init.php';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Stripe initialization loaded');
            error_log('Stripe SDK loaded: ' . (class_exists('\Stripe\Stripe') ? 'Yes' : 'No'));
            error_log('Stripe Connect class loaded: ' . (class_exists('Homey_Stripe_Connect') ? 'Yes' : 'No'));
            error_log('Stripe Child class loaded: ' . (class_exists('Homey_Stripe_Child') ? 'Yes' : 'No'));
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Failed to find stripe-init.php');
        }
    }
}
add_action('after_setup_theme', 'homey_child_load_stripe_early', 5);

// Other includes
include_once( get_stylesheet_directory() . '/framework/functions/child-register-scripts.php');
include_once( get_stylesheet_directory() . '/framework/functions/child-listing.php');
include_once( get_stylesheet_directory() . '/framework/functions/listing-booking.php');
include_once( get_stylesheet_directory() . '/framework/functions/calendar-child.php');
include_once( get_stylesheet_directory() . '/framework/options/get_section_by_id.php');
include_once( get_stylesheet_directory() . '/framework/options/extra_fields.php');
include_once( get_stylesheet_directory() . '/framework/functions/search-child.php');
include_once( get_stylesheet_directory() . '/framework/functions/overtime-threads.php');
include_once( get_stylesheet_directory() . '/framework/functions/child-messages.php');
// Load Stripe files in correct order
include_once( get_stylesheet_directory() . '/framework/functions/class-homey-stripe-connect.php');
include_once( get_stylesheet_directory() . '/framework/functions/class-homey-stripe-child.php');
include_once( get_stylesheet_directory() . '/framework/functions/stripe-includes.php');

// ENQUEUE STYLES
function homey_child_enqueue_styles() {
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1');
    wp_enqueue_style('homey-child', get_stylesheet_directory_uri() . '/style.css', array('homey'));
}
add_action('wp_enqueue_scripts', 'homey_child_enqueue_styles');

// ENQUEUE SCRIPTS
function homey_child_enqueue_scripts() {
    // Enqueue Leaflet JS before our custom script
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
    
    wp_enqueue_script('homey-child-js', get_stylesheet_directory_uri() . '/js/homey-child.js', array('jquery'), null, true);
    wp_enqueue_script('homey-child-maps', get_stylesheet_directory_uri() . '/js/homey-child-maps.js', array('jquery', 'leaflet'), '1.0', true);
    
    // Enqueue Stripe Connect script
    if (is_page_template('template/dashboard-host-stripe.php')) {
        wp_enqueue_script('homey-stripe-connect', get_stylesheet_directory_uri() . '/js/stripe-connect.js', array('jquery'), null, true);
        wp_localize_script('homey-stripe-connect', 'HOMEY_stripe_connect_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('homey_stripe_connect_nonce')
        ));
    }
    
    wp_localize_script('homey-child-js', 'homey_child_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('homey_child_nonce'),
        'process_loader_spinner' => 'homey-icon homey-icon-loading-half fa-spinner',
        'success_icon' => 'homey-icon homey-icon-check-circle-1',
    ));
}
add_action('wp_enqueue_scripts', 'homey_child_enqueue_scripts');

// TIMES OPTIONS
if(!function_exists('homey_get_times_options')) {
    function homey_get_times_options() {
        $times = array(
            '1' => '1:00 AM',
            '2' => '2:00 AM', 
            '3' => '3:00 AM',
            '4' => '4:00 AM',
            '5' => '5:00 AM',
            '6' => '6:00 AM',
            '7' => '7:00 AM',
            '8' => '8:00 AM',
            '9' => '9:00 AM',
            '10' => '10:00 AM',
            '11' => '11:00 AM',
            '12' => '12:00 AM',
            '13' => '1:00 PM',
            '14' => '2:00 PM',
            '15' => '3:00 PM',
            '16' => '4:00 PM',
            '17' => '5:00 PM',
            '18' => '6:00 PM',
            '19' => '7:00 PM',
            '20' => '8:00 PM',
            '21' => '9:00 PM',
            '22' => '10:00 PM',
            '23' => '11:00 PM',
            '24' => '12:00 PM'
        );
        
        return $times;
    }
}


// parking taxonomy for listings
function homey_child_parking_taxonomy() {
    $labels = array(
        'name' => 'Parking',
        'singular_name' => 'Parking',
        'search_items' => 'Search Parking',
        'all_items' => 'All Parking',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'parking'),
    );

    register_taxonomy('parking', 'listing', $args);
}
add_action('init', 'homey_child_parking_taxonomy');


// accessibility taxonomy for listings
function homey_child_accessibility_taxonomy() {
    $labels = array(
        'name' => 'Accessibility',
        'singular_name' => 'Accessibility',
        'search_items' => 'Search Accessibility',
        'all_items' => 'All Accessibility',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'listing_accessibility'),
    );

    register_taxonomy('listing_accessibility', 'listing', $args);
}
add_action('init', 'homey_child_accessibility_taxonomy');


function homey_get_price_label($number = 1) {
    return esc_html__('Per Hour', 'homey');    
}

function homey_get_booking_start_date($reservation_id) {
    $booking_dates = get_post_meta($reservation_id, 'reservation_booking_dates', true);
    if (is_array($booking_dates) && !empty($booking_dates)) {
        foreach($booking_dates as $items) {
            if (!empty($items['arrive_date'])) {
                return $items['arrive_date'];
            }
        }
    }
    return null;
}

function homey_get_booking_end_date($reservation_id) {
    $booking_dates = get_post_meta($reservation_id, 'reservation_booking_dates', true);
    if (is_array($booking_dates) && !empty($booking_dates)) {
        $last_date = null;
        foreach($booking_dates as $items) {
            if (!empty($items['arrive_date'])) {
                $last_date = $items['arrive_date'];
            }
        }
        return $last_date;
    }
    return null;
}

function homey_get_total_guests_range($reservation_id, $listing_id){
    $guest_price = get_post_meta($reservation_id, 'reservation_guests', true);
    $guests_data = get_post_meta($listing_id, 'homey_guest_price', true);

    if(!empty($guests_data)) {
        foreach($guests_data as $key => $data) {
            $price = $data['price'];

            if($guest_price == $price) {
                $total_guests = $key;
            }
        }
        return str_replace('_', ' ', $total_guests);
    } else {
        $total_guests = 0;
    }
}

// Change WP Admin Login Logo
function custom_login_logo() {
    echo '
    <style type="text/css">
        #login h1 a {
            background-image: url(' . get_stylesheet_directory_uri() . '/images/admin-logo.png);
            background-size: contain;
            width: 100%;
        }
    </style>
    ';
}
add_action('login_head', 'custom_login_logo');

function custom_login_url() {
    return home_url();
}
add_filter('login_headerurl', 'custom_login_url');

function custom_login_title() {
    return get_bloginfo('name');
}
add_filter('login_headertitle', 'custom_login_title');


if(!function_exists('homey_get_reservation_dates_by_reservation_id')){
    function homey_get_reservation_dates_by_reservation_id($listing_id){

        $args = array(
            'post_type' => 'homey_reservation',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'reservation_listing_id',
                    'value' => $listing_id,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                ),
                array(
                    'key' => 'reservation_status',
                    'value' => 'booked',
                    'compare' => '=',
                    'type' => 'CHAR'
                )
            )
        );
        $posts = get_posts($args);

        $arrive_date = array();
        if(!empty($posts)){
            foreach($posts as $post){
                $booking_dates = get_post_meta($post->ID, 'reservation_booking_dates', true);
                if(!empty($booking_dates)){
                    foreach($booking_dates as $booking_date){
                        if(!empty($booking_date['arrive_date'])){
                            $arrive_date[] = $booking_date['arrive_date'];
                        }
                    }
                }
            }
        }

        return $arrive_date;
    }
}

function homey_is_dashboard() {

    $files = apply_filters( 'homey_is_dashboard_filter', array(
        'template/dashboard.php',
        'template/dashboard-profile.php',
        'template/dashboard-submission.php',
        'template/dashboard-listing-submitted.php',
        'template/dashboard-favorites.php',
        'template/dashboard-listings.php',
        'template/dashboard-messages.php',

        'template/dashboard-reservations.php',
        'template/dashboard-reservations2.php',

        'template/dashboard-reservations-experiences.php',
        'template/dashboard-reservations2-experiences.php',

        'template/dashboard-saved-searches.php',
        'template/dashboard-payment.php',
        'template/dashboard-exp-payment.php',
        'template/dashboard-invoices.php',
        'template/dashboard-wallet.php',
        'template/dashboard-membership-host.php',

        'template/dashboard-experience-submitted.php',
        'template/dashboard-experiences.php',
        'template/dashboard-experience-submission.php',

        'template/dashboard-host-stripe.php',


    ) );

    if ( is_page_template( $files ) ) {
        return true;
    }
    return false;
}

function homey_is_dashboard_footer() {

    $files = apply_filters( 'homey_is_dashboard_footer_filter', array(
        'template/dashboard.php',
        'template/dashboard-profile.php',
        'template/dashboard-submission.php',
        'template/dashboard-listing-submitted.php',
        'template/dashboard-favorites.php',
        'template/dashboard-listings.php',
        'template/dashboard-messages.php',

        'template/dashboard-reservations.php',
        'template/dashboard-reservations2.php',

        'template/dashboard-reservations-experiences.php',
        'template/dashboard-reservations2-experiences.php',

        'template/dashboard-saved-searches.php',
        'template/dashboard-payment.php',

        'template/dashboard-exp-payment.php',

        'template/dashboard-invoices.php',
        'template/dashboard-wallet.php',
        'template/template-splash.php',
        'template/template-splash-exp.php',
        'template/dashboard-membership-host.php',
        'template/dashboard-experience-submitted.php',
        'template/dashboard-experiences.php',
        'template/dashboard-experience-submission.php',

        'template/dashboard-host-stripe.php',
        
    ) );
    if ( is_page_template( $files ) ) {

        return true;
    }
    return false;
}