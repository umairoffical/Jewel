<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Homey Dynamic Settings Helper
 * Manages dynamic payment settings from admin panel
 */
class Homey_Dynamic_Settings {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    /**
     * Get platform fee percentage based on listing type
     */
    public function get_platform_fee_percentage($listing_id = null, $booking_type = null) {
        // Check if different fees by listing type is enabled
        $different_fees = homey_option('different_fees_by_listing_type', false);
        
        if ($different_fees && $booking_type) {
            switch ($booking_type) {
                case 'per_hour':
                    return homey_option('hourly_listing_fee', 12);
                case 'per_day_date':
                    return homey_option('daily_listing_fee', 10);
                case 'per_experience':
                    return homey_option('experience_listing_fee', 15);
                default:
                    return homey_option('platform_fee_percentage', 10);
            }
        }
        
        return homey_option('platform_fee_percentage', 10);
    }
    
    /**
     * Calculate platform fee with min/max limits
     */
    public function calculate_platform_fee($amount, $listing_id = null, $booking_type = null) {
        $percentage = $this->get_platform_fee_percentage($listing_id, $booking_type);
        $fee = round($amount * ($percentage / 100), 2);
        
        // Apply minimum fee
        $min_fee = homey_option('minimum_platform_fee', 1.00);
        if ($fee < $min_fee) {
            $fee = $min_fee;
        }
        
        // Apply maximum fee
        $max_fee = homey_option('maximum_platform_fee', '');
        if (!empty($max_fee) && $fee > $max_fee) {
            $fee = $max_fee;
        }
        
        return $fee;
    }
    
    /**
     * Get payment delay hours
     */
    public function get_payment_delay_hours() {
        return homey_option('payment_delay_hours', 0);
    }
    
    /**
     * Check if auto release payment is enabled
     */
    public function is_auto_release_enabled() {
        return homey_option('auto_release_payment', true);
    }
    
    /**
     * Check if manual approval is required
     */
    public function is_manual_approval_required() {
        return homey_option('manual_approval_required', false);
    }
    
    /**
     * Get payment release conditions
     */
    public function get_payment_release_conditions() {
        return homey_option('payment_release_conditions', array('host_verification' => '1'));
    }
    
    /**
     * Get required verification level
     */
    public function get_required_verification_level() {
        return homey_option('required_verification_level', 'complete');
    }
    
    /**
     * Get minimum account balance
     */
    public function get_minimum_account_balance() {
        return homey_option('minimum_account_balance', 0.00);
    }
    
    /**
     * Check if host account approval is required
     */
    public function is_host_account_approval_required() {
        return homey_option('host_account_approval', false);
    }
    
    /**
     * Get required verification documents
     */
    public function get_required_verification_documents() {
        return homey_option('host_verification_documents', array('government_id' => '1', 'bank_statement' => '1'));
    }
    
    /**
     * Get payment retry attempts
     */
    public function get_payment_retry_attempts() {
        return homey_option('payment_retry_attempts', 3);
    }
    
    /**
     * Get payment timeout in minutes
     */
    public function get_payment_timeout_minutes() {
        return homey_option('payment_timeout_minutes', 30);
    }
    
    /**
     * Get refund policy days
     */
    public function get_refund_policy_days() {
        return homey_option('refund_policy_days', 7);
    }
    
    /**
     * Get partial refund percentage
     */
    public function get_partial_refund_percentage() {
        return homey_option('partial_refund_percentage', 50);
    }
    
    /**
     * Check if payment success email is enabled
     */
    public function is_payment_success_email_enabled() {
        return homey_option('payment_success_email', true);
    }
    
    /**
     * Check if payment failed email is enabled
     */
    public function is_payment_failed_email_enabled() {
        return homey_option('payment_failed_email', true);
    }
    
    /**
     * Check if host payment received email is enabled
     */
    public function is_host_payment_received_email_enabled() {
        return homey_option('host_payment_received_email', true);
    }
    
    /**
     * Check if admin payment notification is enabled
     */
    public function is_admin_payment_notification_enabled() {
        return homey_option('admin_payment_notification', false);
    }
    
    /**
     * Check if payment logging is enabled
     */
    public function is_payment_logging_enabled() {
        return homey_option('enable_payment_logging', true);
    }
    
    /**
     * Get custom webhook endpoint
     */
    public function get_stripe_webhook_endpoint() {
        return homey_option('stripe_webhook_endpoint', '');
    }
    
    /**
     * Check if test mode is enabled
     */
    public function is_test_mode_enabled() {
        return homey_option('enable_test_mode', false);
    }
    
    /**
     * Get custom payment message
     */
    public function get_custom_payment_message() {
        return homey_option('custom_payment_message', 'Processing your payment securely...');
    }
    
    /**
     * Check if payment release conditions are met
     */
    public function check_payment_release_conditions($reservation_id) {
        $conditions = $this->get_payment_release_conditions();
        $all_met = true;
        
        foreach ($conditions as $condition => $enabled) {
            if ($enabled) {
                switch ($condition) {
                    case 'guest_checkin':
                        $checkin_status = get_post_meta($reservation_id, 'guest_checkin_status', true);
                        if ($checkin_status !== 'checked_in') {
                            $all_met = false;
                        }
                        break;
                        
                    case 'no_disputes':
                        $disputes = get_post_meta($reservation_id, 'active_disputes', true);
                        if (!empty($disputes)) {
                            $all_met = false;
                        }
                        break;
                        
                    case 'host_verification':
                        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
                        $host_id = get_post_field('post_author', $listing_id);
                        $account_status = $this->get_host_account_status($host_id);
                        if ($account_status !== 'complete') {
                            $all_met = false;
                        }
                        break;
                        
                    case 'listing_approved':
                        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
                        $listing_status = get_post_status($listing_id);
                        if ($listing_status !== 'publish') {
                            $all_met = false;
                        }
                        break;
                }
            }
        }
        
        return $all_met;
    }
    
    /**
     * Get host account status
     */
    private function get_host_account_status($host_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
        
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT account_status FROM $table_name WHERE user_id = %d",
            $host_id
        ));
        
        return $status ?: 'pending';
    }
    
    /**
     * Log payment transaction if logging is enabled
     */
    public function log_payment_transaction($message, $data = array()) {
        if ($this->is_payment_logging_enabled()) {
            $log_message = '[' . current_time('Y-m-d H:i:s') . '] ' . $message;
            if (!empty($data)) {
                $log_message .= ' | Data: ' . json_encode($data);
            }
            error_log($log_message);
        }
    }
    
    /**
     * Check if current user has completed Stripe account
     */
    public function is_current_user_stripe_completed() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        return $this->is_user_stripe_completed($user_id);
    }
    
    /**
     * Check if specific user has completed Stripe account
     */
    public function is_user_stripe_completed($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
        
        $account_status = $wpdb->get_var($wpdb->prepare(
            "SELECT account_status FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return $account_status === 'complete';
    }
    
    /**
     * Get current user's Stripe account status
     */
    public function get_current_user_stripe_status() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return 'not_logged_in';
        }
        
        return $this->get_user_stripe_status($user_id);
    }
    
    /**
     * Get specific user's Stripe account status
     */
    public function get_user_stripe_status($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
        
        $account_status = $wpdb->get_var($wpdb->prepare(
            "SELECT account_status FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return $account_status ?: 'not_connected';
    }
    
    /**
     * Get current user's Stripe account ID
     */
    public function get_current_user_stripe_account_id() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        return $this->get_user_stripe_account_id($user_id);
    }
    
    /**
     * Get specific user's Stripe account ID
     */
    public function get_user_stripe_account_id($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT stripe_account_id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
    }
    
    /**
     * Check if current user can accept payments
     */
    public function can_current_user_accept_payments() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        // Check if account is complete
        if (!$this->is_user_stripe_completed($user_id)) {
            return false;
        }
        
        // Additional check with Stripe API
        try {
            $account_id = $this->get_user_stripe_account_id($user_id);
            if (!$account_id) {
                return false;
            }
            
            $stripe = new \Stripe\StripeClient(homey_option('stripe_secret_key'));
            $account = $stripe->accounts->retrieve($account_id);
            
            return $account->charges_enabled;
            
        } catch (Exception $e) {
            error_log('Error checking Stripe account: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all dynamic settings as array
     */
    public function get_all_settings() {
        return array(
            'platform_fee_percentage' => $this->get_platform_fee_percentage(),
            'minimum_platform_fee' => homey_option('minimum_platform_fee', 1.00),
            'maximum_platform_fee' => homey_option('maximum_platform_fee', ''),
            'different_fees_by_listing_type' => homey_option('different_fees_by_listing_type', false),
            'hourly_listing_fee' => homey_option('hourly_listing_fee', 12),
            'daily_listing_fee' => homey_option('daily_listing_fee', 10),
            'experience_listing_fee' => homey_option('experience_listing_fee', 15),
            'payment_delay_hours' => $this->get_payment_delay_hours(),
            'auto_release_payment' => $this->is_auto_release_enabled(),
            'manual_approval_required' => $this->is_manual_approval_required(),
            'payment_release_conditions' => $this->get_payment_release_conditions(),
            'required_verification_level' => $this->get_required_verification_level(),
            'minimum_account_balance' => $this->get_minimum_account_balance(),
            'host_account_approval' => $this->is_host_account_approval_required(),
            'host_verification_documents' => $this->get_required_verification_documents(),
            'payment_retry_attempts' => $this->get_payment_retry_attempts(),
            'payment_timeout_minutes' => $this->get_payment_timeout_minutes(),
            'refund_policy_days' => $this->get_refund_policy_days(),
            'partial_refund_percentage' => $this->get_partial_refund_percentage(),
            'payment_success_email' => $this->is_payment_success_email_enabled(),
            'payment_failed_email' => $this->is_payment_failed_email_enabled(),
            'host_payment_received_email' => $this->is_host_payment_received_email_enabled(),
            'admin_payment_notification' => $this->is_admin_payment_notification_enabled(),
            'enable_payment_logging' => $this->is_payment_logging_enabled(),
            'stripe_webhook_endpoint' => $this->get_stripe_webhook_endpoint(),
            'enable_test_mode' => $this->is_test_mode_enabled(),
            'custom_payment_message' => $this->get_custom_payment_message(),
        );
    }
}

// Initialize the dynamic settings
add_action('init', function() {
    Homey_Dynamic_Settings::getInstance();
}, 5);
