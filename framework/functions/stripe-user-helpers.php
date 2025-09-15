<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple helper functions to check Stripe account status
 * Use these functions anywhere in your code
 */

/**
 * Check if current user has completed Stripe account
 * @return bool
 */
function homey_is_stripe_completed() {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->is_current_user_stripe_completed();
}

/**
 * Check if specific user has completed Stripe account
 * @param int $user_id
 * @return bool
 */
function homey_is_user_stripe_completed($user_id) {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->is_user_stripe_completed($user_id);
}

/**
 * Get current user's Stripe account status
 * @return string (not_logged_in, not_connected, pending, under_review, complete, rejected)
 */
function homey_get_stripe_status() {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->get_current_user_stripe_status();
}

/**
 * Get specific user's Stripe account status
 * @param int $user_id
 * @return string
 */
function homey_get_user_stripe_status($user_id) {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->get_user_stripe_status($user_id);
}

/**
 * Get current user's Stripe account ID
 * @return string|false
 */
function homey_get_stripe_account_id() {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->get_current_user_stripe_account_id();
}

/**
 * Get specific user's Stripe account ID
 * @param int $user_id
 * @return string|false
 */
function homey_get_user_stripe_account_id($user_id) {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->get_user_stripe_account_id($user_id);
}

/**
 * Check if current user can accept payments
 * @return bool
 */
function homey_can_accept_payments() {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->can_current_user_accept_payments();
}

/**
 * Check if specific user can accept payments
 * @param int $user_id
 * @return bool
 */
function homey_can_user_accept_payments($user_id) {
    $dynamic_settings = Homey_Dynamic_Settings::getInstance();
    return $dynamic_settings->is_user_stripe_completed($user_id);
}

/**
 * Get Stripe account status badge HTML
 * @param string $status
 * @return string
 */
function homey_get_stripe_status_badge($status = null) {
    if ($status === null) {
        $status = homey_get_stripe_status();
    }
    
    $badges = array(
        'not_logged_in' => '<span class="badge badge-secondary">Not Logged In</span>',
        'not_connected' => '<span class="badge badge-warning">Not Connected</span>',
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'under_review' => '<span class="badge badge-info">Under Review</span>',
        'complete' => '<span class="badge badge-success">Complete</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
    );
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Get Stripe account status message
 * @param string $status
 * @return string
 */
function homey_get_stripe_status_message($status = null) {
    if ($status === null) {
        $status = homey_get_stripe_status();
    }
    
    $messages = array(
        'not_logged_in' => 'Please log in to check your Stripe account status.',
        'not_connected' => 'You have not connected your Stripe account yet.',
        'pending' => 'Your Stripe account is created but verification is pending.',
        'under_review' => 'Your Stripe account is under review by Stripe.',
        'complete' => 'Your Stripe account is fully verified and ready to accept payments.',
        'rejected' => 'Your Stripe account was rejected. Please contact support.',
    );
    
    return $messages[$status] ?? 'Unknown account status.';
}

/**
 * Check if user needs to complete Stripe verification
 * @param int $user_id
 * @return bool
 */
function homey_needs_stripe_verification($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $status = homey_get_user_stripe_status($user_id);
    return in_array($status, ['not_connected', 'pending', 'under_review']);
}

/**
 * Get Stripe connect URL for current user
 * @return string|false
 */
function homey_get_stripe_connect_url() {
    if (!homey_is_stripe_completed()) {
        return home_url('/dashboard-host-stripe');
    }
    
    return false;
}

/**
 * Display Stripe account status widget
 * @param int $user_id
 * @return string
 */
function homey_display_stripe_status_widget($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return '<div class="alert alert-warning">Please log in to view Stripe status.</div>';
    }
    
    $status = homey_get_user_stripe_status($user_id);
    $badge = homey_get_stripe_status_badge($status);
    $message = homey_get_stripe_status_message($status);
    
    $alert_class = 'alert-info';
    if ($status === 'complete') {
        $alert_class = 'alert-success';
    } elseif (in_array($status, ['rejected', 'not_connected'])) {
        $alert_class = 'alert-danger';
    } elseif (in_array($status, ['pending', 'under_review'])) {
        $alert_class = 'alert-warning';
    }
    
    return sprintf(
        '<div class="alert %s">
            <h5>Stripe Account Status %s</h5>
            <p>%s</p>
        </div>',
        $alert_class,
        $badge,
        $message
    );
}
