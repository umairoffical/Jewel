<?php
if (!defined('ABSPATH')) {
    exit;
}

// Load Stripe classes in the correct order
function homey_child_load_stripe_classes() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Starting Stripe class loading...');
    }

    // First load the parent Stripe class from the plugin
    $parent_stripe_path = WP_PLUGIN_DIR . '/homey-core/classes/class-stripe.php';
    if (file_exists($parent_stripe_path)) {
        require_once $parent_stripe_path;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Parent Stripe class loaded successfully');
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Parent Stripe class file not found at: ' . $parent_stripe_path);
        }
        return;
    }
    
    // Then load the Connect class
    $connect_path = get_stylesheet_directory() . '/framework/functions/class-homey-stripe-connect.php';
    if (file_exists($connect_path)) {
        require_once $connect_path;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Stripe Connect class loaded successfully');
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Stripe Connect class file not found at: ' . $connect_path);
        }
    }
    
    // Then load our child Stripe class
    $child_path = get_stylesheet_directory() . '/framework/functions/class-homey-stripe-child.php';
    if (file_exists($child_path)) {
        require_once $child_path;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Child Stripe class loaded successfully');
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Child Stripe class file not found at: ' . $child_path);
        }
    }
}

// Make sure we load after plugins but before template loading
function homey_child_init_stripe() {
    if (!class_exists('Homey_Stripe')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Parent Homey_Stripe class not found during initialization');
        }
        return;
    }
    
    homey_child_load_stripe_classes();

    // Verify classes are loaded
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Class check after loading - Homey_Stripe: ' . (class_exists('Homey_Stripe') ? 'Yes' : 'No'));
        error_log('Class check after loading - Homey_Stripe_Connect: ' . (class_exists('Homey_Stripe_Connect') ? 'Yes' : 'No'));
        error_log('Class check after loading - Homey_Stripe_Child: ' . (class_exists('Homey_Stripe_Child') ? 'Yes' : 'No'));
    }
}

// Initialize with high priority to ensure it runs after other init actions
add_action('init', 'homey_child_init_stripe', 5);