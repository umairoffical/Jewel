<?php
if (!defined('ABSPATH')) {
    exit;
}

// Override the main Stripe class with our child class
function homey_child_override_stripe_class($class_name) {
    if ($class_name === 'Homey_Stripe') {
        return 'Homey_Stripe_Child';
    }
    return $class_name;
}
add_filter('homey_stripe_class', 'homey_child_override_stripe_class');

// Add Stripe Connect specific scripts
function homey_child_stripe_connect_scripts() {
    global $post;

    // Debug information
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Checking if should load Stripe Connect scripts');
        error_log('Current template: ' . get_page_template_slug($post->ID));
        error_log('Is dashboard function exists: ' . (function_exists('homey_is_dashboard') ? 'Yes' : 'No'));
        error_log('Current URL: ' . $_SERVER['REQUEST_URI']);
    }

    // Check if we're on the Stripe Connect page
    $is_stripe_page = false;
    
    // Check if we're on the dashboard page with the correct slug
    if (function_exists('homey_is_dashboard') && homey_is_dashboard()) {
        if (strpos($_SERVER['REQUEST_URI'], 'dashboard-host-stripe') !== false || 
            get_page_template_slug($post->ID) === 'template/dashboard-host-stripe.php') {
            $is_stripe_page = true;
        }
    }

    // If not on the Stripe page, don't load scripts
    if (!$is_stripe_page) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Not on Stripe Connect page, skipping script load');
        }
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Loading Stripe Connect scripts');
    }

    // Enqueue Stripe JS library first
    wp_enqueue_script(
        'stripe-js',
        'https://js.stripe.com/v3/',
        array(),
        null,
        true
    );

    // Then enqueue our custom script
    wp_enqueue_script(
        'homey-stripe-connect',
        get_stylesheet_directory_uri() . '/js/stripe-connect.js',
        array('jquery', 'stripe-js'),
        time(), // Use time() for development to prevent caching
        true
    );

    // Add our localized variables
    wp_localize_script('homey-stripe-connect', 'HOMEY_stripe_connect_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('homey_stripe_connect_nonce'),
        'connect_success' => esc_html__('Successfully connected with Stripe!', 'homey'),
        'connect_error' => esc_html__('Error connecting with Stripe. Please try again.', 'homey'),
        'publishable_key' => homey_option('stripe_publishable_key'),
        'stripe_mode' => homey_option('stripe_api', 'sandbox'),
    ));

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Stripe Connect scripts enqueued successfully');
    }
}
add_action('wp_enqueue_scripts', 'homey_child_stripe_connect_scripts', 100);

// Add menu item to host dashboard for Stripe Connect
function homey_child_add_stripe_connect_menu($dashboard_menu) {
    $new_item = array(
        'stripe-connect' => array(
            'icon' => 'homey-icon homey-icon-payment',
            'name' => esc_html__('Stripe Connect', 'homey'),
            'link' => home_url('dashboard-host-stripe')
        )
    );
    
    // Insert before the last item (logout)
    $last = array_pop($dashboard_menu);
    $dashboard_menu = array_merge($dashboard_menu, $new_item, array('logout' => $last));
    
    return $dashboard_menu;
}
add_filter('homey_host_dashboard_menu', 'homey_child_add_stripe_connect_menu');