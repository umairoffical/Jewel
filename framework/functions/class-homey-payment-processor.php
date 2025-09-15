<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Homey Payment Processor - Robust Payment Handling System
 * Handles Stripe Connect payments with proper validation and error handling
 */
class Homey_Payment_Processor {
    
    private static $instance = null;
    private $stripe_connect;
    private $stripe_child;
    private $dynamic_settings;
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->stripe_connect = Homey_Stripe_Connect::getInstance();
        $this->stripe_child = null;
        $this->dynamic_settings = Homey_Dynamic_Settings::getInstance();
        
        // Add AJAX handlers
        add_action('wp_ajax_homey_validate_payment', array($this, 'validate_payment_ajax'));
        add_action('wp_ajax_nopriv_homey_validate_payment', array($this, 'validate_payment_ajax'));
        
        add_action('wp_ajax_homey_process_payment_success', array($this, 'process_payment_success_ajax'));
        add_action('wp_ajax_nopriv_homey_process_payment_success', array($this, 'process_payment_success_ajax'));
        
        add_action('wp_ajax_homey_validate_host_account', array($this, 'validate_host_account_ajax'));
        add_action('wp_ajax_nopriv_homey_validate_host_account', array($this, 'validate_host_account_ajax'));
    }
    
    /**
     * Validate payment before processing
     */
    public function validate_payment_ajax() {
        try {
            // Security check
            if (!wp_verify_nonce($_POST['nonce'], 'homey_stripe_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }
            
            $reservation_id = intval($_POST['reservation_id']);
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                wp_send_json_error('User not logged in');
                return;
            }
            
            // Validate reservation exists and belongs to user
            $reservation = get_post($reservation_id);
            if (!$reservation || $reservation->post_type !== 'homey_reservation') {
                wp_send_json_error('Invalid reservation');
                return;
            }
            
            // Get host information
            $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
            $host_id = get_post_field('post_author', $listing_id);
            
            if (!$host_id) {
                wp_send_json_error('Host not found');
                return;
            }
            
            // Validate host has Stripe Connect account
            $host_validation = $this->validate_host_stripe_account($host_id);
            if (!$host_validation['valid']) {
                wp_send_json_error($host_validation['message']);
                return;
            }
            
            // Validate payment amount
            $upfront_payment = get_post_meta($reservation_id, 'reservation_upfront', true);
            if (!$upfront_payment || $upfront_payment <= 0) {
                wp_send_json_error('Invalid payment amount');
                return;
            }
            
            // Create payment intent with proper validation
            $payment_intent = $this->create_payment_intent($reservation_id, $upfront_payment, $host_id);
            
            if (!$payment_intent) {
                wp_send_json_error('Failed to create payment intent');
                return;
            }
            
            wp_send_json_success(array(
                'client_secret' => $payment_intent['client_secret'],
                'payment_intent_id' => $payment_intent['id'],
                'amount' => $upfront_payment,
                'currency' => $payment_intent['currency']
            ));
            
        } catch (Exception $e) {
            error_log('Payment validation error: ' . $e->getMessage());
            wp_send_json_error('Payment validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate host Stripe Connect account
     */
    public function validate_host_stripe_account($host_id) {
        try {
            // Check if host has Stripe Connect account
            $account_id = $this->stripe_connect->get_stripe_account_id($host_id);
            
            if (!$account_id) {
                return array(
                    'valid' => false,
                    'message' => 'Host has not connected their Stripe account. Please ask the host to connect their account first.'
                );
            }
            
            // Check account status
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
            $account_status = $wpdb->get_var($wpdb->prepare(
                "SELECT account_status FROM $table_name WHERE user_id = %d",
                $host_id
            ));
            
            if ($account_status !== 'complete') {
                return array(
                    'valid' => false,
                    'message' => 'Host Stripe account is not fully verified. Status: ' . $account_status
                );
            }
            
            // Verify account with Stripe
            $stripe = new \Stripe\StripeClient(homey_option('stripe_secret_key'));
            $account = $stripe->accounts->retrieve($account_id);
            
            if (!$account->charges_enabled) {
                return array(
                    'valid' => false,
                    'message' => 'Host Stripe account cannot accept payments yet. Please complete verification.'
                );
            }
            
            return array(
                'valid' => true,
                'message' => 'Host account is valid',
                'account_id' => $account_id,
                'account_details' => $account
            );
            
        } catch (Exception $e) {
            error_log('Host validation error: ' . $e->getMessage());
            return array(
                'valid' => false,
                'message' => 'Error validating host account: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Create payment intent with proper routing
     */
    private function create_payment_intent($reservation_id, $amount, $host_id) {
        try {
            // Initialize Stripe Child class
            $this->stripe_child = new Homey_Stripe_Child(get_current_user_id());
            
            // Prepare metadata
            $metadata = array(
                'reservation_id_for_stripe' => $reservation_id,
                'userID' => get_current_user_id(),
                'host_id' => $host_id,
                'payment_type' => 'reservation_fee',
                'listing_id' => get_post_meta($reservation_id, 'reservation_listing_id', true),
                'message' => 'Reservation Payment'
            );
            
            $description = 'Reservation ID ' . $reservation_id;
            
            // Create payment intent with connected account
            $this->stripe_child->homey_stripe_paymenet_intent($amount, $metadata, $description);
            
            return array(
                'id' => $this->stripe_child->get_payment_intent(),
                'client_secret' => $this->stripe_child->get_payment_intent_secret(),
                'currency' => $this->stripe_child->get_currency()
            );
            
        } catch (Exception $e) {
            error_log('Payment intent creation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process successful payment
     */
    public function process_payment_success_ajax() {
        try {
            // Security check
            if (!wp_verify_nonce($_POST['nonce'], 'homey_stripe_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }
            
            $payment_intent_id = sanitize_text_field($_POST['payment_intent_id']);
            $reservation_id = intval($_POST['reservation_id']);
            
            // Verify payment with Stripe
            $stripe = new \Stripe\StripeClient(homey_option('stripe_secret_key'));
            $payment_intent = $stripe->paymentIntents->retrieve($payment_intent_id);
            
            if ($payment_intent->status !== 'succeeded') {
                wp_send_json_error('Payment not successful');
                return;
            }
            
            // Update reservation status
            $this->update_reservation_after_payment($reservation_id, $payment_intent);
            
            // Add earnings to host
            $this->add_host_earnings($reservation_id, $payment_intent);
            
            // Send confirmation emails
            $this->send_payment_confirmation_emails($reservation_id);
            
            wp_send_json_success(array(
                'message' => 'Payment processed successfully',
                'reservation_id' => $reservation_id
            ));
            
        } catch (Exception $e) {
            error_log('Payment success processing error: ' . $e->getMessage());
            wp_send_json_error('Payment processing failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Update reservation after successful payment
     */
    private function update_reservation_after_payment($reservation_id, $payment_intent) {
        // Update reservation status
        update_post_meta($reservation_id, 'reservation_status', 'booked');
        update_post_meta($reservation_id, 'reservation_payment_status', 'paid');
        update_post_meta($reservation_id, 'stripe_payment_intent_id', $payment_intent->id);
        update_post_meta($reservation_id, 'payment_date', current_time('mysql'));
        
        // Update post status
        wp_update_post(array(
            'ID' => $reservation_id,
            'post_status' => 'publish'
        ));
        
        // Move dates to booked
        homey_move_dates_to_book($reservation_id, 1);
    }
    
    /**
     * Add earnings to host
     */
    private function add_host_earnings($reservation_id, $payment_intent) {
        // Get listing details for dynamic fee calculation
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $booking_type = homey_booking_type_by_id($listing_id);
        
        // Calculate earnings using dynamic settings
        $total_amount = $payment_intent->amount / 100; // Convert from cents
        $platform_fee = $this->dynamic_settings->calculate_platform_fee($total_amount, $listing_id, $booking_type);
        $host_earnings = $total_amount - $platform_fee;
        
        // Get host ID
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        $host_id = get_post_field('post_author', $listing_id);
        
        // Add to earnings table
        global $wpdb;
        $earnings_table = $wpdb->prefix . 'homey_earnings';
        
        $wpdb->insert(
            $earnings_table,
            array(
                'user_id' => $host_id,
                'reservation_id' => $reservation_id,
                'total_amount' => $total_amount,
                'platform_fee' => $platform_fee,
                'net_earnings' => $host_earnings,
                'date' => current_time('mysql'),
                'status' => 'completed'
            ),
            array('%d', '%d', '%f', '%f', '%f', '%s', '%s')
        );
    }
    
    /**
     * Send payment confirmation emails
     */
    private function send_payment_confirmation_emails($reservation_id) {
        // Get reservation details
        $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
        $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
        
        // Get user emails
        $guest_id = get_post_field('post_author', $reservation_id);
        $host_id = get_post_field('post_author', $listing_id);
        
        $guest_email = get_userdata($guest_id)->user_email;
        $host_email = get_userdata($host_id)->user_email;
        
        // Send emails
        $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));
        homey_email_composer($guest_email, 'booked_reservation', $email_args);
        homey_email_composer($host_email, 'admin_booked_reservation', $email_args);
    }
    
    /**
     * Validate host account AJAX endpoint
     */
    public function validate_host_account_ajax() {
        try {
            // Security check
            if (!wp_verify_nonce($_POST['nonce'], 'homey_stripe_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }
            
            $listing_id = intval($_POST['listing_id']);
            $host_id = get_post_field('post_author', $listing_id);
            
            $validation = $this->validate_host_stripe_account($host_id);
            
            if ($validation['valid']) {
                wp_send_json_success($validation);
            } else {
                wp_send_json_error($validation['message']);
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Validation failed: ' . $e->getMessage());
        }
    }
}

// Initialize the payment processor
add_action('init', function() {
    if (class_exists('Homey_Stripe_Connect')) {
        Homey_Payment_Processor::getInstance();
    }
}, 10);
