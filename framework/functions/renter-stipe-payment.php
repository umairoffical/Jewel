<?php
/**
 * Renter Stripe Payment System
 * Handles payment processing, refunds, and transfers to hosts
 * 
 * USAGE INSTRUCTIONS:
 * 
 * 1. HOST CONFIRMATION:
 *    When host confirms a reservation, call:
 *    $result = homey_transfer_payment_to_host($reservation_id);
 *    
 *    This will:
 *    - Transfer 90% to host's Stripe Connect account
 *    - Keep 10% as platform fee (highlighted in code for easy change)
 *    - Update payment_status to 'transferred_to_host'
 * 
 * 2. HOST CANCELLATION:
 *    When host cancels a reservation, call:
 *    $result = homey_refund_renter_payment($reservation_id);
 *    
 *    Then update reservation status:
 *    update_post_meta($reservation_id, 'reservation_status', 'cancelled');
 * 
 * 3. PLATFORM FEE:
 *    To change the platform fee percentage, modify this line in homey_transfer_payment_to_host():
 *    $platform_fee_percentage = 0.10; // Currently 10% - CHANGE THIS VALUE IF NEEDED
 */

// Create database table on activation
add_action('init', 'homey_create_renter_stripe_payment_table');

if(!function_exists('homey_create_renter_stripe_payment_table')) {
    function homey_create_renter_stripe_payment_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            renter_id bigint(20) NOT NULL,
            host_id bigint(20) NOT NULL,
            reservation_id bigint(20) NOT NULL,
            reservation_status varchar(50) DEFAULT 'under_review',
            payment_status varchar(50) DEFAULT 'paid',
            paid_time datetime NOT NULL,
            refunded_time datetime NULL,
            transferred_time datetime NULL,
            stripe_payment_intent_id varchar(255) NOT NULL,
            stripe_refund_id varchar(255) NULL,
            stripe_transfer_id varchar(255) NULL,
            amount decimal(10,2) NOT NULL,
            refund_amount decimal(10,2) NULL,
            platform_fee decimal(10,2) NULL,
            host_amount decimal(10,2) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY reservation_id (reservation_id),
            KEY renter_id (renter_id),
            KEY host_id (host_id),
            KEY payment_status (payment_status),
            KEY reservation_status (reservation_status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Ensure platform_fee and host_amount columns exist (migration for existing tables)
        homey_ensure_payment_table_columns($table_name);
    }
}

/**
 * Ensure platform_fee and host_amount columns exist in the payment table
 * This is a migration function for tables created before these columns were added
 */
if(!function_exists('homey_ensure_payment_table_columns')) {
    function homey_ensure_payment_table_columns($table_name) {
        global $wpdb;
        
        // Check if table exists first
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        if (!$table_exists) {
            return; // Table doesn't exist, will be created by create function
        }
        
        // Check if columns exist
        $columns = $wpdb->get_col("DESC $table_name", 0);
        
        // Add platform_fee column if it doesn't exist
        if (!in_array('platform_fee', $columns)) {
            $result = $wpdb->query("ALTER TABLE $table_name ADD COLUMN platform_fee decimal(10,2) NULL AFTER refund_amount");
            if ($result !== false) {
                error_log('Added platform_fee column to ' . $table_name);
            } else {
                error_log('Failed to add platform_fee column. Error: ' . $wpdb->last_error);
            }
        }
        
        // Add host_amount column if it doesn't exist
        if (!in_array('host_amount', $columns)) {
            $result = $wpdb->query("ALTER TABLE $table_name ADD COLUMN host_amount decimal(10,2) NULL AFTER platform_fee");
            if ($result !== false) {
                error_log('Added host_amount column to ' . $table_name);
            } else {
                error_log('Failed to add host_amount column. Error: ' . $wpdb->last_error);
            }
        }
    }
}

// AJAX Handler for Renter Stripe Payment
add_action('wp_ajax_renter_stripe_payment', 'renter_stripe_payment');
add_action('wp_ajax_nopriv_renter_stripe_payment', 'renter_stripe_payment');

if(!function_exists('renter_stripe_payment')){
    function renter_stripe_payment() {
        // Clean output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Security check
        check_ajax_referer('renter_stripe_payment_nonce', 'nonce');
        
        try {
            // Validate user is logged in
            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => 'You must be logged in to make a payment.'));
                return;
            }
            
            $current_user_id = get_current_user_id();
            $reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
            $renter_id = isset($_POST['renter_id']) ? intval($_POST['renter_id']) : 0;
            $host_id = isset($_POST['owner_id']) ? intval($_POST['owner_id']) : 0;
            
            // Validate reservation belongs to current user
            if ($current_user_id != $renter_id) {
                wp_send_json_error(array('message' => 'Invalid user.'));
                return;
            }
            
            // Validate required fields
            if (empty($reservation_id) || empty($renter_id) || empty($host_id)) {
                wp_send_json_error(array('message' => 'Missing required parameters.'));
                return;
            }

        // Get Stripe Information
            $stripe_enable = homey_option('enable_stripe');
            if (empty($stripe_enable)) {
                wp_send_json_error(array('message' => 'Stripe payment is not enabled.'));
                return;
            }
            
            $stripe_secret_key = homey_option('stripe_secret_key');
            if (empty($stripe_secret_key)) {
                wp_send_json_error(array('message' => 'Stripe is not properly configured.'));
                return;
            }
            
            // Initialize Stripe
            if (!class_exists('\Stripe\Stripe')) {
                wp_send_json_error(array('message' => 'Stripe SDK is not loaded.'));
                return;
            }
            
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $stripe = new \Stripe\StripeClient($stripe_secret_key);
            
            // Get reservation details
            $reservation = get_post($reservation_id);
            if (!$reservation || $reservation->post_type !== 'homey_reservation') {
                wp_send_json_error(array('message' => 'Invalid reservation.'));
                return;
            }
            
            // Get payment amount - using total_price from the template
            $booking_dates = get_post_meta($reservation_id, 'reservation_booking_dates', true);
            $total_hours = get_post_meta($reservation_id, 'reservation_total_hours', true);
            $guests = get_post_meta($reservation_id, 'reservation_guests', true);
            $extra_options = get_post_meta($reservation_id, 'extra_options', true);
            $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
            
            // Calculate total price

            // Get reservation type
            $reservation_type = get_post_meta($reservation_id, 'reservation_type', true);
            if($reservation_type == 'overtime_policy' && !empty($reservation_type)){
                $total_price = get_post_meta($reservation_id, 'reservation_upfront', true);
            }else{
                if (function_exists('homey_get_prices_child')) {
                    $prices_array = homey_get_prices_child($booking_dates, $total_hours, $listing_id, $guests, $extra_options);
                    $total_price = $prices_array['total_price'];
                } else {
                    $total_price = get_post_meta($reservation_id, 'reservation_upfront', true);
                }
            }
            
            if (empty($total_price) || $total_price <= 0) {
                wp_send_json_error(array('message' => 'Invalid payment amount.'));
                return;
            }
            
            // Get email for Checkout Session
            $payment_email = isset($_POST['payment_email']) ? sanitize_email($_POST['payment_email']) : '';
            
            // Get user email if not provided
            if (empty($payment_email)) {
                $user = get_userdata($renter_id);
                $payment_email = $user ? $user->user_email : '';
            }
            
            if (empty($payment_email)) {
                wp_send_json_error(array('message' => 'Email is required for payment.'));
                return;
            }
            
            // Get listing title for description
            $listing_title = get_the_title($listing_id);
            
            // Get return URL from POST or construct from reservation
            $return_url = isset($_POST['return_url']) ? esc_url_raw($_POST['return_url']) : '';
            
            // If no return URL provided, try to get payment page URL
            if (empty($return_url)) {
                // Try to get the payment page that uses this template
                $payment_pages = get_pages(array(
                    'meta_key' => '_wp_page_template',
                    'meta_value' => 'template/dashboard-payment.php'
                ));
                
                if (!empty($payment_pages)) {
                    $return_url = get_permalink($payment_pages[0]->ID);
                } else {
                    // Fallback to current site URL
                    $return_url = home_url();
                }
            }
            
            // Add reservation_id to return URL if not already present
            $return_url = add_query_arg('reservation_id', $reservation_id, $return_url);
            
            // Build success and cancel URLs
            $success_url = add_query_arg(array(
                'payment' => 'success',
                'session_id' => '{CHECKOUT_SESSION_ID}'
            ), $return_url);
            
            $cancel_url = add_query_arg(array(
                'payment' => 'cancelled'
            ), $return_url);
            
            // Convert amount to cents
            $amount_cents = intval($total_price * 100);
            
            // Create Stripe Checkout Session (hosted payment page)
            try {
                $checkout_session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'mode' => 'payment',
                    'customer_email' => $payment_email,
                    'success_url' => $success_url,
                    'cancel_url' => $cancel_url,
                    'metadata' => [
                        'reservation_id' => $reservation_id,
                        'renter_id' => $renter_id,
                        'host_id' => $host_id,
                        'listing_id' => $listing_id,
                        'payment_type' => 'reservation_payment',
                    ],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Reservation Payment - Reservation ID: ' . $reservation_id,
                                'description' => !empty($listing_title) ? 'Booking for: ' . $listing_title : 'Reservation Payment',
                            ],
                            'unit_amount' => $amount_cents,
                        ],
                        'quantity' => 1,
                    ]],
                    'locale' => 'auto',
                ]);
            } catch (Exception $e) {
                wp_send_json_error(array('message' => 'Failed to create payment session: ' . $e->getMessage()));
                return;
            }
            
            // Return checkout URL for redirect
            wp_send_json_success(array(
                'message' => 'Redirecting to payment...',
                'checkout_url' => $checkout_session->url,
                'session_id' => $checkout_session->id
            ));
            
        } catch (Exception $e) {
            error_log('Renter Stripe Payment Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'An error occurred: ' . $e->getMessage()));
        }
    }
}

/**
 * Handle Stripe Checkout return - process payment after successful checkout
 * This runs on init and checks if we're returning from Stripe
 */
add_action('template_redirect', 'homey_process_stripe_checkout_return', 1);

if(!function_exists('homey_process_stripe_checkout_return')) {
    function homey_process_stripe_checkout_return() {
        // Check if returning from Stripe Checkout
        if (!isset($_GET['payment']) || !isset($_GET['session_id'])) {
            return;
        }
        
        $payment_status = sanitize_text_field($_GET['payment']);
        $session_id = sanitize_text_field($_GET['session_id']);
        $reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;
        
        if ($payment_status === 'success' && !empty($session_id)) {
            // Process successful payment
            $processed = homey_process_checkout_session($session_id, $reservation_id);
            
            // If processing failed, redirect with error
            if (!$processed) {
                $redirect_url = remove_query_arg(array('session_id'));
                $redirect_url = add_query_arg('payment', 'error', $redirect_url);
                wp_redirect($redirect_url);
                exit;
            }
            
            // Get the payment page URL
            $payment_pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'template/dashboard-payment.php',
                'number' => 1
            ));
            
            if (!empty($payment_pages)) {
                $payment_page_url = get_permalink($payment_pages[0]->ID);
            } else {
                // Fallback to current URL without query params
                $payment_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                                   "://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
            }
            
            // Build redirect URL with reservation_id and success flag
            $redirect_url = add_query_arg(array(
                'reservation_id' => $reservation_id,
                'payment' => 'success'
            ), $payment_page_url);
            
            wp_redirect($redirect_url);
            exit;
            
        } elseif ($payment_status === 'cancelled') {
            // Payment was cancelled - redirect back to payment page
            $payment_pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'template/dashboard-payment.php',
                'number' => 1
            ));
            
            if (!empty($payment_pages)) {
                $payment_page_url = get_permalink($payment_pages[0]->ID);
            } else {
                $payment_page_url = home_url();
            }
            
            $redirect_url = add_query_arg('reservation_id', $reservation_id, $payment_page_url);
            wp_redirect($redirect_url);
            exit;
        }
    }
}

/**
 * Process Stripe Checkout Session after successful payment
 * Returns true if processed successfully, false otherwise
 */
if(!function_exists('homey_process_checkout_session')) {
    function homey_process_checkout_session($session_id, $reservation_id) {
        try {
            $stripe_secret_key = homey_option('stripe_secret_key');
            if (empty($stripe_secret_key)) {
                return false;
            }
            
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $stripe = new \Stripe\StripeClient($stripe_secret_key);
            
            // Retrieve the checkout session
            $session = $stripe->checkout->sessions->retrieve($session_id);
            
            if ($session->payment_status !== 'paid') {
                return false;
            }
            
            // Get metadata from session
            $metadata = $session->metadata;
            $session_reservation_id = isset($metadata->reservation_id) ? intval($metadata->reservation_id) : 0;
            $renter_id = isset($metadata->renter_id) ? intval($metadata->renter_id) : 0;
            $host_id = isset($metadata->host_id) ? intval($metadata->host_id) : 0;
            
            // Use reservation_id from metadata if not in URL
            if (empty($reservation_id) && !empty($session_reservation_id)) {
                $reservation_id = $session_reservation_id;
            }
            
            if (empty($reservation_id) || empty($renter_id) || empty($host_id)) {
                error_log('Missing reservation details in Stripe Checkout session');
                return false;
            }
            
            // Check if payment already processed
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
            
            $existing_payment = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE reservation_id = %d AND payment_status = 'paid'",
                $reservation_id
            ));
            
            if ($existing_payment) {
                // Payment already processed, return success
                return true;
            }
            
            // Get payment intent from session
            $payment_intent_id = $session->payment_intent;
            
            // Get amount from session
            $amount = $session->amount_total / 100; // Convert from cents
            
            // Save payment record to database
            $paid_time = current_time('mysql');
            
            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'renter_id' => $renter_id,
                    'host_id' => $host_id,
                    'reservation_id' => $reservation_id,
                    'reservation_status' => 'under_review',
                    'payment_status' => 'paid',
                    'paid_time' => $paid_time,
                    'stripe_payment_intent_id' => $payment_intent_id,
                    'amount' => $amount,
                ),
                array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%f')
            );
            
            // Update reservation status
            update_post_meta($reservation_id, 'reservation_status', 'under_review');
            update_post_meta($reservation_id, 'stripe_payment_intent_id', $payment_intent_id);
            update_post_meta($reservation_id, 'payment_date', $paid_time);
            update_post_meta($reservation_id, 'reservation_payment_status', 'paid');
            
            // Schedule cron job for 24-hour refund check
            $refund_timestamp = strtotime($paid_time) + (24 * 60 * 60); // 24 hours from payment time
            wp_schedule_single_event($refund_timestamp, 'homey_check_reservation_timeout', array($reservation_id));
            
            // Store success message in transient for display after redirect
            set_transient('renter_payment_success_' . $reservation_id, array(
                'amount' => $amount,
                'reservation_id' => $reservation_id
            ), 300); // 5 minutes
            
            // Fire action hook after successful payment - you can add your custom function here
            /**
             * Action hook fired after successful renter payment
             * 
             * @param int $reservation_id Reservation ID
             * @param int $renter_id Renter/guest user ID
             * @param int $host_id Host/owner user ID
             * @param float $amount Payment amount
             * @param string $payment_intent_id Stripe payment intent ID
             * @param string $paid_time Payment timestamp
             * 
             * Usage example:
             * add_action('homey_renter_payment_success', 'your_custom_function', 10, 6);
             * function your_custom_function($reservation_id, $renter_id, $host_id, $amount, $payment_intent_id, $paid_time) {
             *     // Your custom code here
             * }
             */
            do_action('homey_renter_payment_success', $reservation_id, $renter_id, $host_id, $amount, $payment_intent_id, $paid_time);
            
            return true;
            
        } catch (Exception $e) {
            error_log('Stripe Checkout Processing Error: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * Transfer payment to host after confirmation
 * Platform fee: Gets from homey_option('host_fee'), defaults to 15% if empty
 * Host receives: Remaining amount after platform fee deduction
 */
if(!function_exists('homey_transfer_payment_to_host')) {
    function homey_transfer_payment_to_host($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
        
        // Get payment record
        $payment_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE reservation_id = %d AND payment_status = 'paid'",
            $reservation_id
        ), ARRAY_A);
        
        if (!$payment_record) {
            return array('success' => false, 'message' => 'Payment record not found.');
        }
        
        // Check if already transferred
        if ($payment_record['payment_status'] === 'transferred_to_host') {
            return array('success' => false, 'message' => 'Payment already transferred to host.');
        }
        
        try {
            $stripe_secret_key = homey_option('stripe_secret_key');
            if (empty($stripe_secret_key)) {
                return array('success' => false, 'message' => 'Stripe is not configured.');
            }
            
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $stripe = new \Stripe\StripeClient($stripe_secret_key);
            
            // Get host Stripe Connect account ID
            $stripe_connect = Homey_Stripe_Connect::getInstance();
            $host_stripe_account_id = $stripe_connect->get_stripe_account_id($payment_record['host_id']);
            
            if (empty($host_stripe_account_id)) {
                return array('success' => false, 'message' => 'Host does not have a connected Stripe account.');
            }
            
            $total_amount = floatval($payment_record['amount']);
            
            // STEP 1: Calculate and deduct TAXES first
            $taxes_percent_global = homey_option('taxes_percent');
            $tax_percentage = 0;
            
            if (!empty($taxes_percent_global) && $taxes_percent_global !== '0' && $taxes_percent_global !== 0) {
                // Convert percentage number to decimal (e.g., 7 -> 0.07)
                $tax_percentage = floatval($taxes_percent_global) / 100;
            }
            
            $tax_amount = $total_amount * $tax_percentage;
            $amount_after_tax = $total_amount - $tax_amount;
            
            // STEP 2: Calculate PLATFORM FEE from amount after tax
            $host_fee = homey_option('host_fee');
            
            // If host_fee is empty or not set, default to 15%
            // host_fee is stored as percentage number (e.g., 15 = 15%, not 0.15)
            if (empty($host_fee) || $host_fee === '0' || $host_fee === 0) {
                $platform_fee_percentage = 0.15; // Default 15%
            } else {
                // Convert percentage number to decimal (e.g., 15 -> 0.15)
                $platform_fee_percentage = floatval($host_fee) / 100;
            }
            
            // Platform fee is calculated from amount AFTER tax
            $platform_fee = $amount_after_tax * $platform_fee_percentage;
            
            // STEP 3: Calculate host amount (remaining after tax and platform fee)
            $host_amount = $amount_after_tax - $platform_fee;
            
            // Validate calculations
            if ($tax_amount < 0) {
                error_log('WARNING: Tax amount is negative! Tax: $' . $tax_amount);
            }
            if ($amount_after_tax <= 0) {
                error_log('WARNING: Amount after tax is 0 or negative! Amount After Tax: $' . $amount_after_tax);
            }
            if ($platform_fee <= 0) {
                error_log('WARNING: Platform fee is 0 or negative! Platform Fee: ' . $platform_fee . ', Percentage: ' . ($platform_fee_percentage * 100) . '%');
            }
            if ($host_amount >= $amount_after_tax) {
                error_log('WARNING: Host amount equals or exceeds amount after tax! Host: $' . $host_amount . ', After Tax: $' . $amount_after_tax);
            }
            if (($tax_amount + $platform_fee + $host_amount) > ($total_amount + 0.01)) {
                error_log('WARNING: Sum of deductions exceeds total! Tax: $' . $tax_amount . ', Platform: $' . $platform_fee . ', Host: $' . $host_amount . ', Total: $' . $total_amount);
            }
            
            // Log the fee calculation for debugging
            error_log('=== PAYMENT CALCULATION (Tax + Platform Fee) ===');
            error_log('Reservation ID: ' . $reservation_id);
            error_log('Total Amount: $' . number_format($total_amount, 2));
            error_log('---');
            error_log('Tax Percentage: ' . ($tax_percentage * 100) . '% (from taxes_percent option: ' . var_export($taxes_percent_global, true) . ')');
            error_log('Tax Amount (deducted first): $' . number_format($tax_amount, 2));
            error_log('Amount After Tax: $' . number_format($amount_after_tax, 2));
            error_log('---');
            error_log('Platform Fee Percentage: ' . ($platform_fee_percentage * 100) . '% (from host_fee option: ' . var_export($host_fee, true) . ')');
            error_log('Platform Fee (from amount after tax): $' . number_format($platform_fee, 2));
            error_log('Host Amount (remaining after tax & platform fee): $' . number_format($host_amount, 2));
            error_log('---');
            error_log('Verification: Tax + Platform Fee + Host Amount = $' . number_format($tax_amount + $platform_fee + $host_amount, 2) . ' (should equal Total: $' . number_format($total_amount, 2) . ')');
            error_log('===============================================');
            
            // Ensure columns exist before processing
            homey_ensure_payment_table_columns($table_name);
            
            // Ensure values are properly formatted as decimals BEFORE transfer
            $platform_fee_value = round(floatval($platform_fee), 2);
            $host_amount_value = round(floatval($host_amount), 2);
            
            // Log values before transfer
            error_log('BEFORE TRANSFER - Reservation: ' . $reservation_id . 
                     ', Platform Fee Value: ' . $platform_fee_value . 
                     ', Host Amount Value: ' . $host_amount_value . 
                     ', Payment Record ID: ' . $payment_record['id']);
            
            // Convert to cents for Stripe
            $host_amount_cents = intval(round($host_amount_value * 100));
            
            // Get the payment intent ID for the transfer source
            $payment_intent_id = isset($payment_record['stripe_payment_intent_id']) ? $payment_record['stripe_payment_intent_id'] : '';
            
            if (empty($payment_intent_id)) {
                error_log('ERROR: No payment intent ID found for reservation ' . $reservation_id);
                return array('success' => false, 'message' => 'Payment intent ID not found.');
            }
            
            // Retrieve the charge ID from the payment intent
            try {
                $payment_intent = $stripe->paymentIntents->retrieve($payment_intent_id);
                $charge_id = null;
                
                // Get the charge ID from the payment intent
                if (!empty($payment_intent->latest_charge)) {
                    $charge_id = is_string($payment_intent->latest_charge) ? $payment_intent->latest_charge : $payment_intent->latest_charge->id;
                } elseif (!empty($payment_intent->charges->data)) {
                    $charge_id = $payment_intent->charges->data[0]->id;
                }
                
                if (empty($charge_id)) {
                    error_log('ERROR: Could not find charge ID for payment intent: ' . $payment_intent_id);
                    // Fallback: try to create transfer without source_transaction (may not work in all cases)
                    $transfer_params = [
                        'amount' => $host_amount_cents,
                        'currency' => 'usd',
                        'destination' => $host_stripe_account_id,
                        'metadata' => [
                            'reservation_id' => $reservation_id,
                            'type' => 'reservation_payment',
                            'payment_intent_id' => $payment_intent_id,
                        ],
                    ];
                } else {
                    // Use source_transaction with charge ID (best practice)
                    $transfer_params = [
                        'amount' => $host_amount_cents,
                        'currency' => 'usd',
                        'destination' => $host_stripe_account_id,
                        'source_transaction' => $charge_id,
                        'metadata' => [
                            'reservation_id' => $reservation_id,
                            'type' => 'reservation_payment',
                            'payment_intent_id' => $payment_intent_id,
                        ],
                    ];
                }
                
                // IMPORTANT: Verify we're transferring the correct amount (host_amount, not total)
                // Host amount should be less than amount after tax (since platform fee was deducted)
                $amount_after_tax_cents = intval(round($amount_after_tax * 100));
                if ($host_amount_cents >= $amount_after_tax_cents) {
                    error_log('ERROR: Transfer amount (' . $host_amount_cents . ') >= Amount after tax (' . $amount_after_tax_cents . '). Platform fee not deducted!');
                    return array('success' => false, 'message' => 'Transfer amount error: Platform fee not deducted correctly.');
                }
                if ($host_amount_cents >= intval($total_amount * 100)) {
                    error_log('ERROR: Transfer amount (' . $host_amount_cents . ') >= Total amount (' . intval($total_amount * 100) . '). Tax and platform fee not deducted!');
                    return array('success' => false, 'message' => 'Transfer amount error: Tax and platform fee not deducted correctly.');
                }
                
                // Transfer to host account using Stripe Transfer
                $transfer = $stripe->transfers->create($transfer_params);
                
                error_log('=== STRIPE TRANSFER CREATED ===');
                error_log('Transfer ID: ' . $transfer->id);
                error_log('Total Original Amount: $' . number_format($total_amount, 2));
                error_log('Tax Deducted: $' . number_format($tax_amount, 2) . ' (' . ($tax_percentage * 100) . '%)');
                error_log('Amount After Tax: $' . number_format($amount_after_tax, 2));
                error_log('Platform Fee Kept: $' . number_format($platform_fee_value, 2) . ' (' . ($platform_fee_percentage * 100) . '% of amount after tax)');
                error_log('Transfer Amount to Host: $' . number_format($host_amount_value, 2) . ' (' . $host_amount_cents . ' cents)');
                error_log('Charge ID: ' . ($charge_id ?? 'N/A'));
                error_log('================================');
                
            } catch (Exception $transfer_exception) {
                error_log('Stripe Transfer Error: ' . $transfer_exception->getMessage());
                return array('success' => false, 'message' => 'Transfer failed: ' . $transfer_exception->getMessage());
            }
            
            // Update payment record with transfer details
            $transferred_time = current_time('mysql');
            
            // Prepare update data
            $update_data = array(
                'payment_status' => 'transferred_to_host',
                'transferred_time' => $transferred_time,
                'stripe_transfer_id' => $transfer->id,
                'platform_fee' => $platform_fee_value,
                'host_amount' => $host_amount_value,
            );
            
            // Use direct SQL update to ensure values are saved correctly
            // This is more reliable than wpdb->update for decimal values
            $update_sql = $wpdb->prepare(
                "UPDATE $table_name SET 
                    payment_status = %s,
                    transferred_time = %s,
                    stripe_transfer_id = %s,
                    platform_fee = %f,
                    host_amount = %f
                WHERE id = %d",
                'transferred_to_host',
                $transferred_time,
                $transfer->id,
                $platform_fee_value,
                $host_amount_value,
                $payment_record['id']
            );
            
            $update_result = $wpdb->query($update_sql);
            
            // Log the SQL for debugging
            error_log('Database Update SQL: ' . $update_sql);
            error_log('Update Result: ' . var_export($update_result, true) . ', Rows Affected: ' . $wpdb->rows_affected);
            
            // Check if update was successful
            if ($update_result === false) {
                error_log('SQL update failed. Error: ' . $wpdb->last_error);
                
                // Try individual updates as fallback
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET platform_fee = %f WHERE id = %d", $platform_fee_value, $payment_record['id']));
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET host_amount = %f WHERE id = %d", $host_amount_value, $payment_record['id']));
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET payment_status = %s WHERE id = %d", 'transferred_to_host', $payment_record['id']));
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET transferred_time = %s WHERE id = %d", $transferred_time, $payment_record['id']));
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET stripe_transfer_id = %s WHERE id = %d", $transfer->id, $payment_record['id']));
                
                error_log('Attempted individual column updates as fallback');
            }
            
            // Verify the values were saved correctly - REQUIRED CHECK
            $verify_record = $wpdb->get_row($wpdb->prepare(
                "SELECT platform_fee, host_amount, payment_status FROM $table_name WHERE id = %d",
                $payment_record['id']
            ), ARRAY_A);
            
            if ($verify_record) {
                $saved_platform_fee = isset($verify_record['platform_fee']) ? floatval($verify_record['platform_fee']) : 0;
                $saved_host_amount = isset($verify_record['host_amount']) ? floatval($verify_record['host_amount']) : 0;
                
                error_log('VERIFICATION AFTER UPDATE - Platform Fee: ' . $saved_platform_fee . 
                         ', Host Amount: ' . $saved_host_amount . 
                         ', Payment Status: ' . var_export($verify_record['payment_status'], true));
                
                // If values are still NULL or 0, force update with raw SQL (no prepared statement for values)
                if (empty($saved_platform_fee) || empty($saved_host_amount) || $saved_platform_fee == 0 || $saved_host_amount == 0) {
                    error_log('ERROR: Platform fee or host amount is NULL/0 after update. Forcing update with raw values...');
                    
                    // Force update with direct value insertion (careful with SQL injection, but values are already validated)
                    $forced_update = $wpdb->query(
                        "UPDATE $table_name SET 
                            platform_fee = " . floatval($platform_fee_value) . ",
                            host_amount = " . floatval($host_amount_value) . ",
                            payment_status = 'transferred_to_host',
                            transferred_time = '" . esc_sql($transferred_time) . "',
                            stripe_transfer_id = '" . esc_sql($transfer->id) . "'
                        WHERE id = " . intval($payment_record['id'])
                    );
                    
                    error_log('Forced update result: ' . var_export($forced_update, true));
                    
                    // Verify again
                    $verify_again = $wpdb->get_row($wpdb->prepare(
                        "SELECT platform_fee, host_amount FROM $table_name WHERE id = %d",
                        $payment_record['id']
                    ), ARRAY_A);
                    
                    if ($verify_again) {
                        error_log('AFTER FORCED UPDATE - Platform Fee: ' . var_export($verify_again['platform_fee'], true) . 
                                 ', Host Amount: ' . var_export($verify_again['host_amount'], true));
                    }
                }
            } else {
                error_log('ERROR: Could not verify update - record not found with ID: ' . $payment_record['id']);
            }
            
            error_log('Payment transfer completed - Reservation: ' . $reservation_id . 
                     ', Platform Fee: $' . number_format($platform_fee_value, 2) . 
                     ', Host Amount: $' . number_format($host_amount_value, 2) . 
                     ', Transfer ID: ' . $transfer->id);
            
            return array(
                'success' => true,
                'message' => 'Payment transferred to host successfully.',
                'transfer_id' => $transfer->id,
                'host_amount' => $host_amount_value,
                'platform_fee' => $platform_fee_value,
                'platform_fee_percentage' => ($platform_fee_percentage * 100)
            );
            
        } catch (Exception $e) {
            error_log('Transfer to Host Error: ' . $e->getMessage());
            return array('success' => false, 'message' => 'Transfer failed: ' . $e->getMessage());
        }
    }
}

/**
 * Refund payment to renter
 * 
 * This function processes the actual refund via Stripe API
 * The money is automatically returned to the renter's original payment method (card)
 * 
 * @param int $reservation_id Reservation ID
 * @return array Array with success status, message, and refund details
 */
if(!function_exists('homey_refund_renter_payment')) {
    function homey_refund_renter_payment($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
        
        // Validate reservation ID
        if (empty($reservation_id) || !is_numeric($reservation_id)) {
            return array('success' => false, 'message' => 'Invalid reservation ID.');
        }
        
        // Get payment record - don't filter by payment_status here, check it after
        $payment_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE reservation_id = %d",
            $reservation_id
        ), ARRAY_A);
        
        if (!$payment_record) {
            return array('success' => false, 'message' => 'Payment record not found for this reservation.');
        }
        
        // Check payment status - only refund if status is 'paid'
        $payment_status = $payment_record['payment_status'];
        if ($payment_status !== 'paid') {
            if ($payment_status === 'refunded') {
                return array('success' => false, 'message' => 'Payment has already been refunded.');
            } elseif ($payment_status === 'transferred_to_host') {
                return array('success' => false, 'message' => 'Payment has already been transferred to host. Cannot refund automatically.');
            } else {
                return array('success' => false, 'message' => 'Payment status is: ' . $payment_status . '. Cannot refund.');
            }
        }
        
        // Verify we have required data
        if (empty($payment_record['stripe_payment_intent_id'])) {
            return array('success' => false, 'message' => 'Payment intent ID not found.');
        }
        
        if (empty($payment_record['amount']) || floatval($payment_record['amount']) <= 0) {
            return array('success' => false, 'message' => 'Invalid payment amount.');
        }
        
        try {
            $stripe_secret_key = homey_option('stripe_secret_key');
            if (empty($stripe_secret_key)) {
                return array('success' => false, 'message' => 'Stripe is not configured.');
            }
            
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $stripe = new \Stripe\StripeClient($stripe_secret_key);
            
            // Retrieve the payment intent to verify it exists
            $payment_intent = $stripe->paymentIntents->retrieve($payment_record['stripe_payment_intent_id']);
            
            // Verify payment intent is valid
            if (!$payment_intent || $payment_intent->status !== 'succeeded') {
                return array('success' => false, 'message' => 'Payment intent not found or not successful.');
            }
            
            // ==========================================
            // ACTUAL REFUND PROCESSING - MONEY GOES BACK TO RENTER'S CARD
            // ==========================================
            // Convert amount to cents for Stripe
            $refund_amount_cents = intval(floatval($payment_record['amount']) * 100);
            
            // Create refund via Stripe API - This refunds money back to renter's original payment method
            // Stripe automatically returns the money to the card used for the original payment
            $refund = $stripe->refunds->create([
                'payment_intent' => $payment_record['stripe_payment_intent_id'],
                'amount' => $refund_amount_cents, // Full refund amount
                'metadata' => [
                    'reservation_id' => $reservation_id,
                    'reason' => 'reservation_timeout_or_cancellation',
                ],
            ]);
            
            // After this line, the money has been refunded to renter's card
            // Stripe handles the actual transfer back to the renter's payment method
            
            // Verify refund was successful and has an ID
            if (empty($refund->id)) {
                error_log('Stripe refund created but no refund ID returned for reservation: ' . $reservation_id);
                return array('success' => false, 'message' => 'Refund created but could not verify refund ID.');
            }
            
            // Verify refund status
            if ($refund->status !== 'succeeded' && $refund->status !== 'pending') {
                error_log('Stripe refund not in expected status. Reservation: ' . $reservation_id . ', Status: ' . $refund->status);
                // Continue anyway as refund was created
            }
            
            // Update payment record with refund details
            $refunded_time = current_time('mysql');
            
            // Get current reservation status to maintain consistency (could be 'cancelled' or 'declined')
            $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
            $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
            
            $update_data = array(
                'payment_status' => 'refunded',
                'reservation_status' => $status_to_set, // Update to match current reservation status
                'refunded_time' => $refunded_time,
                'stripe_refund_id' => $refund->id,
                'refund_amount' => $payment_record['amount'],
            );
            
            $update_result = $wpdb->update(
                $table_name,
                $update_data,
                array('id' => $payment_record['id']),
                array('%s', '%s', '%s', '%s', '%f'),
                array('%d')
            );
            
            if ($update_result === false) {
                error_log('CRITICAL: Failed to update payment record after refund. Reservation: ' . $reservation_id . ', Error: ' . $wpdb->last_error . ', Refund ID: ' . $refund->id);
                // Even if database update fails, refund was processed successfully in Stripe
                // Try to update again as a safety measure
                $wpdb->update(
                    $table_name,
                    $update_data,
                    array('id' => $payment_record['id']),
                    array('%s', '%s', '%s', '%s', '%f'),
                    array('%d')
                );
            }
            
            // Verify the update was successful
            $verify_update = $wpdb->get_var($wpdb->prepare(
                "SELECT payment_status FROM $table_name WHERE id = %d",
                $payment_record['id']
            ));
            
            if ($verify_update !== 'refunded') {
                error_log('WARNING: Payment status may not have updated correctly. Reservation: ' . $reservation_id . ', Status: ' . $verify_update);
            } else {
                // Update reservation post meta as well for consistency
                update_post_meta($reservation_id, 'reservation_payment_status', 'refunded');
                update_post_meta($reservation_id, 'refund_date', $refunded_time);
                update_post_meta($reservation_id, 'stripe_refund_id', $refund->id);
            }
            
            // Log successful refund with all details
            error_log('SUCCESS: Payment refunded - Reservation: ' . $reservation_id . ', Refund ID: ' . $refund->id . ', Amount: $' . number_format($payment_record['amount'], 2) . ', Refunded Time: ' . $refunded_time);
            
            return array(
                'success' => true,
                'message' => 'Payment refunded successfully.',
                'refund_id' => $refund->id,
                'refund_amount' => $payment_record['amount'],
                'refunded_time' => $refunded_time
            );
            
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            error_log('Stripe Refund Error (Invalid Request): ' . $e->getMessage() . ' - Reservation: ' . $reservation_id);
            return array('success' => false, 'message' => 'Refund failed: ' . $e->getError()->message);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Refund Error (API Error): ' . $e->getMessage() . ' - Reservation: ' . $reservation_id);
            return array('success' => false, 'message' => 'Refund failed: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('Refund Error (General): ' . $e->getMessage() . ' - Reservation: ' . $reservation_id);
            return array('success' => false, 'message' => 'Refund failed: ' . $e->getMessage());
        }
    }
}

/**
 * Cron job: Check for reservations that need refunding after 24 hours
 */
add_action('homey_check_reservation_timeout', 'homey_check_reservation_timeout_handler');

if(!function_exists('homey_check_reservation_timeout_handler')) {
    function homey_check_reservation_timeout_handler($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
        
        // Get payment record
        $payment_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE reservation_id = %d",
            $reservation_id
        ), ARRAY_A);
        
        if (!$payment_record) {
            return;
        }
        
        // Check if payment is still paid and not transferred
        if ($payment_record['payment_status'] !== 'paid') {
            return;
        }
        
        // Check if 24 hours have passed since payment
        $paid_time = strtotime($payment_record['paid_time']);
        $current_time = current_time('timestamp');
        $hours_passed = ($current_time - $paid_time) / 3600;
        
        if ($hours_passed < 24) {
            // Reschedule for later
            $remaining_seconds = (24 * 60 * 60) - ($current_time - $paid_time);
            wp_schedule_single_event($current_time + $remaining_seconds, 'homey_check_reservation_timeout', array($reservation_id));
            return;
        }
        
        // Check reservation status - if still pending/under_review, refund
        $reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        
        if ($reservation_status === 'under_review' || $reservation_status === 'available') {
            // Refund the payment
            $refund_result = homey_refund_renter_payment($reservation_id);
            
            if ($refund_result['success']) {
                // Update reservation status to cancelled
                update_post_meta($reservation_id, 'reservation_status', 'cancelled');
                update_post_meta($reservation_id, 'res_cancel_reason', 'Reservation Time Out - 24 hours passed');
                
                // Update payment table
                $wpdb->update(
                    $table_name,
                    array('reservation_status' => 'cancelled'),
                    array('id' => $payment_record['id']),
                    array('%s'),
                    array('%d')
                );
                
                error_log('Reservation ' . $reservation_id . ' timed out and refund processed.');
            }
        }
    }
}

// Also schedule cron check on init for safety (checks all pending payments)
add_action('init', 'homey_check_all_pending_refunds', 99);

if(!function_exists('homey_check_all_pending_refunds')) {
    function homey_check_all_pending_refunds() {
        // Only run occasionally to avoid performance issues (check every hour)
        $last_check = get_transient('homey_last_refund_check');
        if ($last_check !== false) {
            return;
        }
        
        set_transient('homey_last_refund_check', time(), 3600); // Cache for 1 hour
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_renter_stripe_payment';
        
        // Get all paid payments older than 24 hours that haven't been transferred
        $cutoff_time = date('Y-m-d H:i:s', current_time('timestamp') - (24 * 60 * 60));
        
        $pending_payments = $wpdb->get_results($wpdb->prepare(
            "SELECT reservation_id FROM $table_name 
            WHERE payment_status = 'paid' 
            AND paid_time <= %s 
            AND reservation_id > 0",
            $cutoff_time
        ));
        
        foreach ($pending_payments as $payment) {
            $reservation_status = get_post_meta($payment->reservation_id, 'reservation_status', true);
            if ($reservation_status === 'under_review' || $reservation_status === 'available') {
                // Schedule immediate check
                wp_schedule_single_event(time(), 'homey_check_reservation_timeout', array($payment->reservation_id));
            }
        }
    }
}

add_action('homey_renter_payment_success', 'homey_renter_payment_success_handler', 10, 6);
if(!function_exists('homey_renter_payment_success_handler')) {
    function homey_renter_payment_success_handler($reservation_id, $renter_id, $host_id, $amount, $payment_intent_id, $paid_time) {
        $date = current_time('Y-m-d H:i:s');
        if(!empty($reservation_id)){
            
            // UPDATE RESERVATION STATUS TO PUBLISH
            wp_update_post(array(
                'ID' => $reservation_id,
                'post_status' => 'publish'
            ));

            // GENERATE INVOICE
            homey_generate_invoice('reservation', 'one_time', $reservation_id, $date, $renter_id, 0, 0, '', 'Self', $amount);
        }
    }
}

/**
 * Handle refund when reservation is cancelled or declined
 * This function checks payment status and processes refund if applicable
 * Used for both cancellation and decline scenarios
 * 
 * @param int $reservation_id Reservation ID
 * @return array Returns array with 'refund_processed' (bool) and 'message' (string)
 */
if(!function_exists('homey_handle_cancellation_refund')) {
    function homey_handle_cancellation_refund($reservation_id) {
        global $wpdb;
        
        $refund_processed = false;
        $refund_message = '';
        
        // Validate reservation ID
        if (empty($reservation_id) || !is_numeric($reservation_id)) {
            return array(
                'refund_processed' => false,
                'message' => 'Invalid reservation ID'
            );
        }
        
        // Check if payment exists in our custom payment table
        $payment_table = $wpdb->prefix . 'homey_renter_stripe_payment';
        $payment_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $payment_table WHERE reservation_id = %d",
            $reservation_id
        ), ARRAY_A);
        
        // If no payment record exists, nothing to refund
        if (!$payment_record) {
            return array(
                'refund_processed' => false,
                'message' => 'No payment record found for this reservation'
            );
        }
        
        // Check payment status and handle accordingly
        $payment_status = $payment_record['payment_status'];
        
        // Case 1: Payment is still 'paid' - eligible for refund
        if ($payment_status === 'paid') {
            // ==========================================
            // PROCESS REFUND - Money goes back to renter's card
            // This calls homey_refund_renter_payment() which uses Stripe API
            // to refund the payment back to the renter's original payment method
            // ==========================================
            $refund_result = homey_refund_renter_payment($reservation_id);
            
            if ($refund_result['success']) {
                $refund_processed = true;
                $refund_message = 'Payment refunded successfully. Refund ID: ' . $refund_result['refund_id'];
                
                // Get current reservation status to maintain consistency (cancelled or declined)
                $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
                $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
                
                // Note: Reservation status is already updated in homey_refund_renter_payment()
                // But we update it here as well for safety and to ensure it matches
                $wpdb->update(
                    $payment_table,
                    array('reservation_status' => $status_to_set),
                    array('reservation_id' => $reservation_id),
                    array('%s'),
                    array('%d')
                );
                
                error_log('Reservation ' . $reservation_id . ' ' . $status_to_set . ' - Refund processed successfully. Refund ID: ' . $refund_result['refund_id'] . ', Amount: ' . $refund_result['refund_amount']);
            } else {
                $refund_message = 'Refund attempted but failed: ' . $refund_result['message'];
                
                // Get current reservation status
                $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
                $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
                
                error_log('Reservation ' . $reservation_id . ' ' . $status_to_set . ' - Refund failed: ' . $refund_result['message']);
                
                // Even if refund failed, update reservation status
                $wpdb->update(
                    $payment_table,
                    array('reservation_status' => $status_to_set),
                    array('reservation_id' => $reservation_id),
                    array('%s'),
                    array('%d')
                );
            }
        }
        // Case 2: Payment already transferred to host - cannot refund automatically
        elseif ($payment_status === 'transferred_to_host') {
            $refund_message = 'Payment has already been transferred to host. Refund may need manual processing.';
            error_log('Reservation ' . $reservation_id . ' cancelled - Payment already transferred to host');
            
            // Get current reservation status to maintain consistency (cancelled or declined)
            $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
            $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
            
            // Still update the reservation status in payment table
            $wpdb->update(
                $payment_table,
                array('reservation_status' => $status_to_set),
                array('reservation_id' => $reservation_id),
                array('%s'),
                array('%d')
            );
        }
        // Case 3: Payment already refunded
        elseif ($payment_status === 'refunded') {
            $refund_message = 'Payment was already refunded previously.';
            
            // Get current reservation status to maintain consistency
            $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
            $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
            
            // Update reservation status in payment table
            $wpdb->update(
                $payment_table,
                array('reservation_status' => $status_to_set),
                array('reservation_id' => $reservation_id),
                array('%s'),
                array('%d')
            );
        }
        // Case 4: Unknown or other payment status
        else {
            $refund_message = 'Payment status is: ' . $payment_status . '. Refund may need manual review.';
            error_log('Reservation ' . $reservation_id . ' cancelled/declined - Unknown payment status: ' . $payment_status);
            
            // Get current reservation status to maintain consistency
            $current_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
            $status_to_set = ($current_reservation_status === 'declined') ? 'declined' : 'cancelled';
            
            // Update reservation status in payment table
            $wpdb->update(
                $payment_table,
                array('reservation_status' => $status_to_set),
                array('reservation_id' => $reservation_id),
                array('%s'),
                array('%d')
            );
        }
        
        return array(
            'refund_processed' => $refund_processed,
            'message' => $refund_message,
            'payment_status' => $payment_status
        );
    }
}

// AJAX Handler for Reservation Cancellation
add_action('wp_ajax_homey_cancelled_reservation', 'homey_cancelled_reservation');
function homey_cancelled_reservation() {
    
    global $current_user;
    $current_user = wp_get_current_user();
    $userID = $current_user->ID;
    $local = homey_get_localization();

    $reservation_id = intval($_POST['reservation_id']);
    $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
    $reason = sanitize_text_field($_POST['reason']);
    $host_cancel = sanitize_text_field($_POST['host_cancel']);

    $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

    //cancellation date is expired check
    $num_hours_before_cancel = homey_option('num_0f_hours_before_checkin_remove_resrv');
    $cancel_before_date = strtotime(date('d-m-Y')) + $num_hours_before_cancel * 60 * 60;
    $check_in_date = strtotime(date('d-m-Y', custom_strtotime(get_post_meta($reservation_id, "reservation_checkin_date", true))));


    if (($listing_renter != $userID) && ($listing_owner != $userID) && !homey_is_admin()) {
        echo json_encode(
            array(
                'success' => false,
                'message' => $local['listing_renter_text']
            )
        );
        wp_die();
    }

    if (empty($reason)) {
        echo json_encode(
            array(
                'success' => false,
                'message' => $local['reason_text_req']
            )
        );
        wp_die();
    }

    // Set reservation status from under_review to available
    update_post_meta($reservation_id, 'reservation_status', 'cancelled');
    update_post_meta($reservation_id, 'res_cancel_reason', $reason);

    //Remove Pending Dates
    $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

    //Remove Booked Dates
    $booked_dates_array = homey_remove_booking_booked_days($listing_id, $reservation_id);
    update_post_meta($listing_id, 'reservation_dates', $booked_dates_array);

    // ==========================================
    // HANDLE REFUND TO RENTER IF APPLICABLE
    // ==========================================
    $refund_result = homey_handle_cancellation_refund($reservation_id);
    $refund_processed = isset($refund_result['refund_processed']) ? $refund_result['refund_processed'] : false;
    $refund_message = isset($refund_result['message']) ? $refund_result['message'] : '';
    
    // Update reservation payment status meta if refund was processed
    if ($refund_processed) {
        update_post_meta($reservation_id, 'reservation_payment_status', 'refunded');
        
        // Final verification - log all status updates for debugging
        $final_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        $final_payment_status = get_post_meta($reservation_id, 'reservation_payment_status', true);
        
        error_log('Cancellation Complete - Reservation: ' . $reservation_id . 
                  ', Reservation Status: ' . $final_reservation_status . 
                  ', Payment Status: ' . $final_payment_status . 
                  ', Refund: Success');
    } else {
        // Log when refund wasn't processed (might be normal if payment wasn't made or already transferred)
        error_log('Cancellation Complete - Reservation: ' . $reservation_id . 
                  ', Refund Not Processed: ' . $refund_message);
    }

    if ($host_cancel == 'cancelled_by_host') {
        $renter = homey_usermeta($listing_renter);
        $to_email = $renter['email'];
    } else {
        $owner = homey_usermeta($listing_owner);
        $to_email = $owner['email'];
    }

    $host_earning = homey_get_earning_by_reservation_id($reservation_id);
    if (!empty($host_earning)) {
        $host_id = $host_earning->user_id;
        $deduct_amount = $host_earning->net_earnings;
        homey_adjust_host_available_balance_2($host_id, $deduct_amount);
    }


    // Prepare success message
    $success_message = esc_html__('Reservation cancelled successfully', 'homey');
    if ($refund_processed) {
        $success_message .= '. ' . esc_html__('Payment has been refunded to the renter.', 'homey');
    } elseif (!empty($refund_message)) {
        // Include refund status in message (for debugging/logging)
        error_log('Cancellation refund status: ' . $refund_message);
    }

    echo json_encode(
        array(
            'success' => true,
            'message' => $success_message,
            'refund_processed' => $refund_processed,
            'refund_message' => $refund_message
        )
    );

    $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));

    homey_email_composer($to_email, 'cancelled_reservation', $email_args);
    if( homey_option('cancel_reser_notify_to_admin', false)){
        $admin_email = get_option( 'admin_email' );
        homey_email_composer( $admin_email, 'cancelled_reservation', $email_args );
    }

    wp_die();
}

add_action('wp_ajax_homey_decline_reservation', 'homey_decline_reservation');
function homey_decline_reservation() {
    global $current_user;
    $current_user = wp_get_current_user();
    $userID = $current_user->ID;
    $local = homey_get_localization();

    $reservation_id = intval($_POST['reservation_id']);
    $listing_id = get_post_meta($reservation_id, 'reservation_listing_id', true);
    $reason = sanitize_text_field($_POST['reason']);

    $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);

    $renter = homey_usermeta($listing_renter);
    $renter_email = $renter['email'];

    // if ($listing_owner != $userID && !homey_is_admin()) {
    //     echo json_encode(
    //         array(
    //             'success' => false,
    //             'message' => $local['listing_owner_text']
    //         )
    //     );
    //     wp_die();
    // }

    // Set reservation status from under_review to declined
    update_post_meta($reservation_id, 'reservation_status', 'declined');
    update_post_meta($reservation_id, 'res_decline_reason', $reason);

    //Remove Pending Dates
    $pending_dates_array = homey_remove_booking_pending_days($listing_id, $reservation_id, true);
    update_post_meta($listing_id, 'reservation_pending_dates', $pending_dates_array);

    // ==========================================
    // HANDLE REFUND TO RENTER IF APPLICABLE
    // ==========================================
    $refund_result = homey_handle_cancellation_refund($reservation_id);
    $refund_processed = isset($refund_result['refund_processed']) ? $refund_result['refund_processed'] : false;
    $refund_message = isset($refund_result['message']) ? $refund_result['message'] : '';
    
    // Update reservation payment status meta if refund was processed
    if ($refund_processed) {
        update_post_meta($reservation_id, 'reservation_payment_status', 'refunded');
        
        // Final verification - log all status updates for debugging
        $final_reservation_status = get_post_meta($reservation_id, 'reservation_status', true);
        $final_payment_status = get_post_meta($reservation_id, 'reservation_payment_status', true);
        
        error_log('Reservation Declined - Reservation: ' . $reservation_id . 
                  ', Reservation Status: ' . $final_reservation_status . 
                  ', Payment Status: ' . $final_payment_status . 
                  ', Refund: Success');
    } else {
        // Log when refund wasn't processed
        error_log('Reservation Declined - Reservation: ' . $reservation_id . 
                  ', Refund Not Processed: ' . $refund_message);
    }

    // Prepare success message
    $success_message = esc_html__('Reservation declined successfully', 'homey');
    if ($refund_processed) {
        $success_message .= '. ' . esc_html__('Payment has been refunded to the renter.', 'homey');
    } elseif (!empty($refund_message)) {
        // Include refund status in message (for debugging/logging)
        error_log('Decline refund status: ' . $refund_message);
    }

    echo json_encode(
        array(
            'success' => true,
            'message' => $success_message,
            'refund_processed' => $refund_processed,
            'refund_message' => $refund_message
        )
    );

    $email_args = array('reservation_detail_url' => reservation_detail_link($reservation_id));
    homey_email_composer($renter_email, 'declined_reservation', $email_args);
    wp_die();
}

// CONFIRM RESERVATION - Override parent theme function
// Remove parent theme action first, then add our own
remove_action('wp_ajax_homey_confirm_reservation', 'homey_confirm_reservation');
add_action('wp_ajax_homey_confirm_reservation', 'homey_confirm_reservation_child');
function homey_confirm_reservation_child() {
    global $current_user, $wpdb;
    $current_user = wp_get_current_user();
    $userID = $current_user->ID;
    $local = homey_get_localization();
    $no_upfront = homey_option('reservation_payment');

    // Clean output buffer
    if (ob_get_level()) {
        ob_clean();
    }

    $date = date('Y-m-d G:i:s', current_time('timestamp', 0));

    // Validate and sanitize input
    $reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
    
    if (empty($reservation_id)) {
        wp_send_json_error(array('message' => 'Invalid reservation ID.'));
        return;
    }

    $listing_owner = get_post_meta($reservation_id, 'listing_owner', true);
    $listing_renter = get_post_meta($reservation_id, 'listing_renter', true);
    $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

    // Validate user permissions
    if (empty($listing_owner) || ($listing_owner != $userID && !homey_is_admin())) {
        wp_send_json_error(array(
            'success' => false,
            'message' => homey_get_reservation_notification('not_owner')
        ));
        return;
    }

    $renter = homey_usermeta($listing_renter);
    $renter_email = isset($renter['email']) ? $renter['email'] : '';

    // If no upfront option select then book at this step
    if ($no_upfront == 'no_upfront') {
        if ($is_hourly == 'yes') {
            homey_hourly_booking_with_no_upfront($reservation_id);
        } else {
            homey_booking_with_no_upfront($reservation_id);
        }

        wp_send_json_success(array(
            'success' => true,
            'message' => homey_get_reservation_notification('booked')
        ));
        return;
    }

    // ==========================================
    // HANDLE PAYMENT TRANSFER TO HOST
    // ==========================================
    $transfer_result = array('success' => false, 'message' => '');
    $payment_transferred = false;
    
    // Check if there's a payment record for this reservation
    $payment_table = $wpdb->prefix . 'homey_renter_stripe_payment';
    
    // Ensure columns exist
    homey_ensure_payment_table_columns($payment_table);
    
    $payment_record = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $payment_table WHERE reservation_id = %d",
        $reservation_id
    ), ARRAY_A);
    
    // If payment record exists and status is 'paid', transfer to host
    if ($payment_record && isset($payment_record['payment_status']) && $payment_record['payment_status'] === 'paid') {
        // Cancel the scheduled refund cron job since host confirmed
        if (!empty($payment_record['paid_time'])) {
            $refund_timestamp = strtotime($payment_record['paid_time']) + (24 * 60 * 60);
            wp_unschedule_event($refund_timestamp, 'homey_check_reservation_timeout', array($reservation_id));
            wp_clear_scheduled_hook('homey_check_reservation_timeout', array($reservation_id));
            error_log('Reservation ' . $reservation_id . ' confirmed - Cancelled scheduled refund cron job');
        }
        
        // Transfer payment to host (percentage based on host_fee option, default 15% platform fee)
        $transfer_result = homey_transfer_payment_to_host($reservation_id);
        
        if ($transfer_result && isset($transfer_result['success']) && $transfer_result['success']) {
            $payment_transferred = true;
            
            // Update reservation payment status meta
            update_post_meta($reservation_id, 'reservation_payment_status', 'transferred_to_host');
            update_post_meta($reservation_id, 'payment_transfer_date', $date);
            if (isset($transfer_result['transfer_id'])) {
                update_post_meta($reservation_id, 'stripe_transfer_id', $transfer_result['transfer_id']);
            }
            if (isset($transfer_result['platform_fee'])) {
                update_post_meta($reservation_id, 'platform_fee', $transfer_result['platform_fee']);
            }
            if (isset($transfer_result['host_amount'])) {
                update_post_meta($reservation_id, 'host_amount', $transfer_result['host_amount']);
            }
            
            // Verify database values were saved
            $verify_payment = $wpdb->get_row($wpdb->prepare(
                "SELECT platform_fee, host_amount FROM $payment_table WHERE reservation_id = %d",
                $reservation_id
            ), ARRAY_A);
            
            if ($verify_payment) {
                error_log('Reservation ' . $reservation_id . ' confirmed - Payment transferred. ' .
                            'DB Platform Fee: ' . var_export($verify_payment['platform_fee'], true) . 
                            ', DB Host Amount: ' . var_export($verify_payment['host_amount'], true));
                
                if (empty($verify_payment['platform_fee']) || empty($verify_payment['host_amount'])) {
                    error_log('ERROR: Platform fee or host amount is empty in database after transfer!');
                    // Try to update directly
                    if (isset($transfer_result['platform_fee']) && isset($transfer_result['host_amount'])) {
                        $wpdb->query($wpdb->prepare(
                            "UPDATE $payment_table SET platform_fee = %f, host_amount = %f WHERE reservation_id = %d",
                            $transfer_result['platform_fee'],
                            $transfer_result['host_amount'],
                            $reservation_id
                        ));
                    }
                }
            }
            
            // Log successful transfer
            error_log('Reservation ' . $reservation_id . ' confirmed - Payment transferred to host. ' .
                        'Transfer ID: ' . (isset($transfer_result['transfer_id']) ? $transfer_result['transfer_id'] : 'N/A') . 
                        ', Host Amount: $' . (isset($transfer_result['host_amount']) ? number_format($transfer_result['host_amount'], 2) : '0.00') . 
                        ', Platform Fee: $' . (isset($transfer_result['platform_fee']) ? number_format($transfer_result['platform_fee'], 2) : '0.00'));
        } else {
            // Log transfer failure but continue with reservation confirmation
            $error_msg = isset($transfer_result['message']) ? $transfer_result['message'] : 'Unknown error';
            error_log('Reservation ' . $reservation_id . ' confirmed - Payment transfer failed: ' . $error_msg);
        }
    } elseif ($payment_record && isset($payment_record['payment_status']) && $payment_record['payment_status'] === 'transferred_to_host') {
        // Payment already transferred
        $payment_transferred = true;
        error_log('Reservation ' . $reservation_id . ' confirmed - Payment already transferred previously');
    } elseif (!$payment_record) {
        // No payment record found - reservation might not have payment required
        error_log('Reservation ' . $reservation_id . ' confirmed - No payment record found (may be no upfront payment)');
    }
    
    // change available to booked
    update_post_meta($reservation_id, 'reservation_status', 'booked');
    update_post_meta($reservation_id, 'reservation_confirm_date_time', $date);
    
    // Update reservation status in payment table if payment record exists
    if ($payment_record) {
        $wpdb->update(
            $payment_table,
            array('reservation_status' => 'available'),
            array('reservation_id' => $reservation_id),
            array('%s'),
            array('%d')
        );
    }

    // Prepare success message
    $success_message = homey_get_reservation_notification('available');
    if ($payment_transferred) {
        $success_message .= '. ' . esc_html__('Payment has been transferred to host.', 'homey');
    }

    // Send email notification
    $allowded_html = array();
    $reservation_meta = get_post_meta($reservation_id, 'reservation_meta', true);
    
    if (!empty($reservation_meta) && !empty($renter_email)) {
        $check_in_date = isset($reservation_meta['check_in_date']) ? wp_kses($reservation_meta['check_in_date'], $allowded_html) : '';
        $check_out_date = isset($reservation_meta['check_out_date']) ? wp_kses($reservation_meta['check_out_date'], $allowded_html) : '';
        $guests = isset($reservation_meta['guests']) ? intval($reservation_meta['guests']) : 0;
        $adult_guest = isset($reservation_meta['adult_guest']) ? intval($reservation_meta['adult_guest']) : 0;
        $child_guest = isset($reservation_meta['child_guest']) ? intval($reservation_meta['child_guest']) : 0;
        $upfront_payment = isset($reservation_meta['upfront']) ? $reservation_meta['upfront'] : '';
        $balance = isset($reservation_meta['balance']) ? $reservation_meta['balance'] : '';
        $total_price = isset($reservation_meta['total']) ? $reservation_meta['total'] : '';

        $email_args = array(
            'reservation_detail_url' => reservation_detail_link($reservation_id),
            'check_in_date' => $check_in_date,
            'check_out_date' => $check_out_date,
            'guests' => $guests,
            'adult_guests' => $adult_guest,
            'child_guests' => $child_guest,
            'upfront_payment' => $upfront_payment,
            'balance' => $balance,
            'total_price' => $total_price,
        );
        homey_email_composer($renter_email, 'confirm_reservation', $email_args);
    }

    // Send JSON response
    wp_send_json_success(array(
        'success' => true,
        'message' => $success_message,
        'payment_transferred' => $payment_transferred,
        'transfer_details' => $payment_transferred && isset($transfer_result) ? array(
            'host_amount' => isset($transfer_result['host_amount']) ? $transfer_result['host_amount'] : 0,
            'platform_fee' => isset($transfer_result['platform_fee']) ? $transfer_result['platform_fee'] : 0,
        ) : null
    ));
}
?>