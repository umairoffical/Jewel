<?php
// Load dynamic settings first
require_once get_stylesheet_directory() . '/framework/functions/class-homey-dynamic-settings.php';

// Load dynamic settings admin panel
require_once get_stylesheet_directory() . '/framework/options/stripe-dynamic-settings.php';

// Load Stripe user helper functions
require_once get_stylesheet_directory() . '/framework/functions/stripe-user-helpers.php';


// LOGOUT INACTIVE USER AFTER 15 MINUTES
add_filter( 'auth_cookie_expiration', 'wpdev_login_session' );
function wpdev_login_session( $expire ) {
    return 3600; // 60 minutes in seconds
}

// Load Stripe classes early
function homey_child_load_stripe_early() {
    // First check if Stripe is already loaded
    if (class_exists('\Stripe\Stripe')) {
        return;
    }

    // Try loading from the parent theme/plugin
    if (defined('HOMEY_PLUGIN_PATH')) {
        $stripe_sdk_path = HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php';
        if (file_exists($stripe_sdk_path)) {
            require_once $stripe_sdk_path;
        }
    }
    
    // If still not loaded, try composer's autoload
    if (!class_exists('\Stripe\Stripe')) {
        $composer_autoload = get_stylesheet_directory() . '/vendor/autoload.php';
        if (file_exists($composer_autoload)) {
            require_once $composer_autoload;
        }
    }
    
    // If still not loaded, try direct include
    if (!class_exists('\Stripe\Stripe')) {
        $direct_stripe_path = get_stylesheet_directory() . '/framework/functions/stripe-php/init.php';
        if (file_exists($direct_stripe_path)) {
            require_once $direct_stripe_path;
        }
    }
    
    // Always load our initialization
    if (file_exists(get_stylesheet_directory() . '/framework/functions/stripe-init.php')) {
        require_once get_stylesheet_directory() . '/framework/functions/stripe-init.php';
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
include_once( get_stylesheet_directory() . '/framework/functions/reservation-actions.php');
include_once( get_stylesheet_directory() . '/framework/functions/renter-stipe-payment.php');
// Load Stripe files in correct order
include_once( get_stylesheet_directory() . '/framework/functions/class-homey-stripe-connect.php');
include_once( get_stylesheet_directory() . '/framework/functions/class-homey-stripe-child.php');
include_once( get_stylesheet_directory() . '/framework/functions/stripe-includes.php');

// Load enhanced payment processor AFTER Stripe classes are loaded
require_once get_stylesheet_directory() . '/framework/functions/class-homey-payment-processor.php';

// ELEMENTOR WIDGET
// include_once( get_stylesheet_directory() . '/framework/widgets/become-host-register-form-child.php');
// add_action('elementor/widgets/register', 'homey_child_register_elementor_widgets');
// function homey_child_register_elementor_widgets($widgets_manager) {
//     $widgets_manager->register(new \Homey_Elementor_Register_Child());
// }
if (!function_exists('homey_become_host_register_form_child_elementor_widget')) {
    function homey_become_host_register_form_child_elementor_widget()
    {
        require_once get_stylesheet_directory() . '/framework/widgets/become-host-register-form-child.php';
    }
}
add_action('elementor/widgets/register', 'homey_become_host_register_form_child_elementor_widget');
require_once get_stylesheet_directory() . '/framework/functions/widget-functions.php';

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


    if(is_page_template('template/dashboard-payment.php')){
        wp_enqueue_script('renter-stripe', get_stylesheet_directory_uri() . '/js/renter-stripe-payment.js', array('jquery'), null, true);
        wp_localize_script('renter-stripe', 'stripe_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('renter_stripe_payment_nonce')
        ));
    }

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
        'template/dashboard-stripe-payment-details.php',


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
        'template/dashboard-stripe-payment-details.php',
        
    ) );
    if ( is_page_template( $files ) ) {

        return true;
    }
    return false;
}

// Change WordPress default email sender name and email
function custom_wp_mail_from_name( $name ) {
    return 'Location Jewel'; // Replace with your site name
}
add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );


// homey_login_child
add_action( 'wp_ajax_homey_login_child', 'homey_login_child' );
add_action( 'wp_ajax_nopriv_homey_login_child', 'homey_login_child' );
if( !function_exists('homey_login_child') ) {
    function homey_login_child() {

        if(is_user_logged_in()){
            echo json_encode( array(
                'success' => false,
                'msg' => esc_html__('You are already logged in, please try to clear the browser\'s cache.', 'homey-login-register')
                ) );

            wp_die();
        }
        
        $allowed_html = array();

        $allowed_html_array = array('strong' => array());
        $username = wp_kses( $_POST['username'], $allowed_html );
        $pass = $_POST['password'];
        
        check_ajax_referer( 'homey_login_nonce', 'homey_login_security' );

        if( isset( $_POST['remember'] ) ) {
            $remember = wp_kses( $_POST['remember'], $allowed_html );
        } else {
            $remember = '';
        }

        if( empty( $username ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username or email field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( empty( $pass ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The password field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( !username_exists( $username ) && !email_exists($username)) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username or email', 'homey-login-register') ) );
            wp_die();
        }

        $enable_reCaptcha = homey_option('enable_reCaptcha');
        if( $enable_reCaptcha == 1 ) {
            homey_google_recaptcha_callback();
        }

        wp_clear_auth_cookie();

        $remember = ($remember == 'on') ? true : false;

        if(is_email($username)) {
            $user = get_user_by( 'email', $username );
            $username = $user->user_login;
        }else{
            $user = get_user_by('login', $username);
        }

        if(empty($user)) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username or email', 'homey-login-register') ) );
            wp_die();
        }

        $creds = array();
        $creds['user_login'] = $username;
        $creds['user_password'] = $pass;
        $creds['remember'] = $remember;

        if(!homey_is_verified_by_email_child($user)){
            echo json_encode( array(
                'success' => false,
                'msg' => __('Account is being verified and will be activated shortly.', 'homey-login-register')
            ) );

            wp_die();
        }else{
            $user = wp_signon( $creds, false );
        }

        if ( is_wp_error( $user ) ) {

            echo json_encode( array(
                'success' => false,
                'msg' => sprintf( wp_kses(__('The password you entered for the username <strong>%s</strong> is incorrect.', 'homey-login-register'), $allowed_html_array), $username )
                ) );

            wp_die();
        } else {

            //wp_set_current_user($user->ID);
            do_action('set_current_user');
            wp_set_auth_cookie( $user->ID, $creds['remember'] );
            
            echo json_encode( array( 'success' => true, 'msg' => esc_html__('Login successful, redirecting...', 'homey-login-register') ) );

        }
        wp_die();
    }
}

function homey_is_verified_by_email_child($user){
    $verification_id = get_user_meta($user->ID, 'verification_id', true);
    $email_verified = get_user_meta($user->ID, 'is_email_verified', true);
    
    if(empty($verification_id) || empty($email_verified)){
        return false;
    }

    return true;
}

add_action('wp_ajax_homey_verify_user_manually', 'homey_verify_user_manually');
function homey_verify_user_manually() {

    // Check if current user has admin rights
    if ( ! current_user_can( 'manage_options' ) ) {
        echo json_encode( array(
            'success' => false,
            'reason'  => esc_html__('Not authorized!', 'homey')
        ));
        wp_die();
    }
    
    $nonce = $_REQUEST['security'];

    if ( ! wp_verify_nonce( $nonce, 'manually_user_approve_nonce' ) ) {
        echo json_encode( array(
            'success' => false,
            'reason'  => esc_html__('Security check failed!', 'homey')
        ));
        wp_die();
    }

    $notification_data = array(
        'success' => false,
        'user_id' => $_POST['user_id'],
        'text'    => esc_html__('Something went wrong! Try again.', 'homey')
    );

    if ( isset( $_POST['user_id'] ) ) {
        // Optionally, you can also use your custom homey_is_admin() check if needed.
        if ( homey_is_admin() ) {
            update_user_meta( $_POST['user_id'], 'verification_id', 1 );
            update_user_meta( $_POST['user_id'], 'is_email_verified', 1 );

            $notification_data = array(
                'success' => true,
                'text'    => esc_html__('Verified', 'homey')
            );
        }
    }

    echo json_encode( $notification_data );
    wp_die();
}

// ============================================================
// DISABLE AUTOMATIC USER DELETION CRON JOB - CRITICAL FIX
// ============================================================
// The parent theme has a cron job that deletes unverified users after 24 hours
// This causes issues with host and renter accounts being deleted automatically
// The code below COMPLETELY disables this functionality using multiple layers of protection

// LAYER 1: Remove the cron job action hook - prevents the function from running even if scheduled
remove_action('homey_delete_spam_users', 'homey_delete_spam_users');

// LAYER 2: Disable the email spam filter that deletes users immediately on registration
remove_filter('wp_mail', 'homey_remove_spam_user_filter_wp_mail');

// LAYER 3: Override the option value to always be 0 (disabled) - prevents scheduling
add_filter('option_homey_options', 'homey_child_force_disable_user_deletion_option', 1, 2);
function homey_child_force_disable_user_deletion_option($value, $option) {
    if (is_array($value) && isset($value['clear_unverified_users'])) {
        $value['clear_unverified_users'] = 0;
    }
    return $value;
}

// LAYER 4: Also filter the homey_option function directly to always return 0 for this option
add_filter('homey_option_clear_unverified_users', 'homey_child_force_disable_option_value', 1);
function homey_child_force_disable_option_value($value) {
    return 0; // Always return 0 to keep it disabled
}

// LAYER 5: Clear any scheduled cron events on multiple hooks to catch it at different stages
function homey_child_disable_user_deletion_cron() {
    // Remove any scheduled events
    $timestamp = wp_next_scheduled('homey_delete_spam_users');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'homey_delete_spam_users');
    }
    
    // Clear all scheduled instances of this event (more aggressive)
    wp_clear_scheduled_hook('homey_delete_spam_users');
    
    // Double-check: Remove the action again in case it was re-added
    remove_action('homey_delete_spam_users', 'homey_delete_spam_users');
}

// Run on multiple hooks to ensure we catch it regardless of when parent theme loads
add_action('plugins_loaded', 'homey_child_disable_user_deletion_cron', 1);
add_action('after_setup_theme', 'homey_child_disable_user_deletion_cron', 1);
add_action('init', 'homey_child_disable_user_deletion_cron', 1);
add_action('wp_loaded', 'homey_child_disable_user_deletion_cron', 1);

// LAYER 6: Replace the deletion function with a safe no-op function (ultimate protection)
add_action('homey_delete_spam_users', 'homey_child_prevent_user_deletion', 999);
function homey_child_prevent_user_deletion() {
    // This function does nothing - it's a safety net in case the action wasn't removed
    // Log for debugging if needed
    error_log('homey_delete_spam_users was called but prevented by child theme');
    return; // Do nothing - users will NOT be deleted
}


// SEND EMAIL
add_action( 'wp_ajax_nopriv_homey_contact_host_child', 'homey_contact_host_child' );
add_action( 'wp_ajax_homey_contact_host_child', 'homey_contact_host_child' );
if( !function_exists( 'homey_contact_host_child' ) ) {
    function homey_contact_host_child() {

        /*$nonce = $_POST['host_detail_ajax_nonce'];
        if (!wp_verify_nonce( $nonce, 'host-contact-nonce') ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Unverified Nonce!', 'homey-core')
            ));
            wp_die();
        }*/

        $sender_phone = sanitize_text_field( $_POST['phone'] );

        $target_email = sanitize_email($_POST['target_email']);
        $target_email = is_email($target_email);
        if (!$target_email) {
            echo json_encode(array(
                'success' => false,
                'msg' => sprintf( esc_html__('%s Target Email address is not properly configured!', 'homey-core'), $target_email )
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

        $sender_email = '';

        // $sender_email = sanitize_email($_POST['email']);
        // $sender_email = is_email($sender_email);
        // if (!$sender_email) {
        //     echo json_encode(array(
        //         'success' => false,
        //         'msg' => esc_html__('Provided Email address is invalid!', 'homey-core')
        //     ));
        //     wp_die();
        // }

        $sender_msg = wp_kses_post( $_POST['message'] );
        if ( empty($sender_msg) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__('Your message empty!', 'homey-core')
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

        $email_subject = sprintf( esc_html__('New message sent by %s using contact form at %s', 'homey-core'), $sender_name, get_bloginfo('name') );

        $email_body = esc_html__("You have received a message from: ", 'homey-core') . $sender_name . " <br/>";
        if (!empty($sender_phone)) {
            $email_body .= esc_html__("Phone Number : ", 'homey-core') . $sender_phone . " <br/>";
        }
        $email_body .= esc_html__("Additional message is as follows.", 'homey-core') . " <br/>";
        $email_body .= wpautop( $sender_msg ) . " <br/>";
        $email_body .= sprintf( esc_html__( 'You can contact %s via email %s', 'homey-core'), $sender_name, $sender_email );


        $header = 'Content-type: text/html; charset=utf-8' . "\r\n";
        //$header .= 'From: ' . $sender_name . " <" . $sender_email . "> \r\n";

        $header  .= "From: $sender_name <$sender_email>\r\n";
        $header .= "MIME-Version: 1.0\r\n";

        if (wp_mail( $target_email, $email_subject, $email_body, $header)) {
            echo json_encode( array(
                'success' => true,
                'msg' => esc_html__("Message Sent Successfully!", 'homey-core')
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


// HOMEY CHILD LOGIN REGISTER
add_action( 'wp_ajax_nopriv_homey_register_child_modal', 'homey_register_child_modal' );
add_action( 'wp_ajax_homey_register_child_modal', 'homey_register_child_modal' );

if( !function_exists('homey_register_child_modal') ) {
    function homey_register_child_modal() {
        //$local = homey_get_localization();

        check_ajax_referer('homey_register_nonce', 'homey_register_security');

        $allowed_html = array();
        homey_google_recaptcha_callback();

        $usermane          = trim( sanitize_text_field( wp_kses( $_POST['username'], $allowed_html ) ));
        $email             = trim( sanitize_text_field( wp_kses( $_POST['useremail'], $allowed_html ) ));
        $term_condition    = wp_kses( $_POST['term_condition'], $allowed_html );
        $enable_password = homey_option('enable_password');

        $response = isset($_POST["g-recaptcha-response"])?$_POST["g-recaptcha-response"]:'';

        $user_role = get_option( 'default_role' );

        if( $user_role == 'administrator' ) {
            $user_role = 'subscriber';
        }

        if( isset( $_POST['role'] ) && $_POST['role'] != '' ){
            $user_role = isset( $_POST['role'] ) ? sanitize_text_field( wp_kses( $_POST['role'], $allowed_html ) ) : $user_role;
        } else {
            $user_role = $user_role;
        }

        if( get_option('users_can_register') != 1 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Access denied.', 'homey-login-register') ) );
            wp_die();
        }

        $term_condition = ( $term_condition == 'on') ? true : false;

        if( !$term_condition ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('You need to agree with terms & conditions.', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The username field is empty.', 'homey-login-register') ) );
            wp_die();
        }
        if( strlen( $usermane ) < 3 ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Minimum 3 characters required', 'homey-login-register') ) );
            wp_die();
        }
        if (preg_match("/^[0-9A-Za-z_]+$/", $usermane) == 0) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid username (do not use special characters or spaces)!', 'homey-login-register') ) );
            wp_die();
        }
        if( username_exists( $usermane ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This username is already registered.', 'homey-login-register') ) );
            wp_die();
        }

        // phone 
        $phone_number = sanitize_text_field( $_POST['reg_form_phone_number'] );
        if( empty( $phone_number ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The phone number field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( empty( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('The email field is empty.', 'homey-login-register') ) );
            wp_die();
        }

        if( email_exists( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This email address is already registered.', 'homey-login-register') ) );
            wp_die();
        }

        if( !is_email( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid email address.', 'homey-login-register') ) );
            wp_die();
        }

        if( $enable_password == 'yes' ){
            $user_pass         = trim( sanitize_text_field(wp_kses( $_POST['register_pass'] ,$allowed_html) ) );
            $user_pass_retype  = trim( sanitize_text_field(wp_kses( $_POST['register_pass_retype'] ,$allowed_html) ) );

            if ($user_pass == '' || $user_pass_retype == '' ) {
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('One of the password field is empty!', 'homey-login-register') ) );
                wp_die();
            }

            if ($user_pass !== $user_pass_retype ){
                echo json_encode( array( 'success' => false, 'msg' => esc_html__('Passwords do not match', 'homey-login-register') ) );
                wp_die();
            }
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

        if($enable_password == 'yes' ) {
            $user_password = $user_pass;
        } else {
            $user_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        }
        $user_id = wp_create_user( $usermane, $user_password, $email );

        if ( is_wp_error($user_id) ) {
            echo json_encode( array( 'success' => false, 'msg' => $user_id ) );
            wp_die();
        } else {

            wp_update_user( array( 'ID' => $user_id, 'role' => $user_role ) );

            if( $enable_password =='yes' ) {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Your account was created and you can login now!', 'homey-login-register') ) );
            } else {
                echo json_encode( array( 'success' => true, 'msg' => esc_html__('Registration complete. Please check your email!', 'homey-login-register') ) );
            }
            homey_wp_new_user_notification( $user_id, $user_password );
        }
        wp_die();

    }
}