<?php 

function homey_create_stripe_dynamic_settings($sections){
    $allowed_html_array = array();
    $sections[]=  array(
        'title' => esc_html__('Stripe Dynamic Settings', 'homey-child' ),
        'icon' => 'dashicons dashicons-admin-settings',
        'heading' => 'Stripe Dynamic Settings',
        'id' => 'stripe-dynamic-settings',
        'fields' =>  [
            // Payment Split Settings Section
            [
                'id'       => 'payment_split_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Payment Split Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Configure how payments are split between platform and hosts', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'platform_fee_percentage',
                'type'     => 'slider',
                'title'    => esc_html__('Platform Fee Percentage', 'homey-child'),
                'subtitle' => esc_html__('Percentage of each payment that goes to the platform', 'homey-child'),
                'desc'     => esc_html__('Set the platform fee percentage (0-50%)', 'homey-child'),
                'default'  => 10,
                'min'      => 0,
                'max'      => 50,
                'step'     => 0.5,
                'resolution' => 0.1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'minimum_platform_fee',
                'type'     => 'text',
                'title'    => esc_html__('Minimum Platform Fee Amount', 'homey-child'),
                'subtitle' => esc_html__('Minimum amount the platform receives per transaction', 'homey-child'),
                'desc'     => esc_html__('Enter amount in your currency (e.g., 2.50)', 'homey-child'),
                'default'  => '1.00',
                'validate' => 'numeric'
            ],
            [
                'id'       => 'maximum_platform_fee',
                'type'     => 'text',
                'title'    => esc_html__('Maximum Platform Fee Amount', 'homey-child'),
                'subtitle' => esc_html__('Maximum amount the platform receives per transaction', 'homey-child'),
                'desc'     => esc_html__('Enter amount in your currency (e.g., 50.00). Leave empty for no limit.', 'homey-child'),
                'default'  => '',
                'validate' => 'numeric'
            ],
            [
                'id'       => 'different_fees_by_listing_type',
                'type'     => 'switch',
                'title'    => esc_html__('Different Fees by Listing Type', 'homey-child'),
                'subtitle' => esc_html__('Enable different platform fees for different listing types', 'homey-child'),
                'default'  => false,
            ],
            [
                'id'       => 'hourly_listing_fee',
                'type'     => 'slider',
                'title'    => esc_html__('Hourly Listing Platform Fee (%)', 'homey-child'),
                'subtitle' => esc_html__('Platform fee for hourly bookings', 'homey-child'),
                'required' => array('different_fees_by_listing_type', '=', '1'),
                'default'  => 12,
                'min'      => 0,
                'max'      => 50,
                'step'     => 0.5,
                'resolution' => 0.1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'daily_listing_fee',
                'type'     => 'slider',
                'title'    => esc_html__('Daily Listing Platform Fee (%)', 'homey-child'),
                'subtitle' => esc_html__('Platform fee for daily bookings', 'homey-child'),
                'required' => array('different_fees_by_listing_type', '=', '1'),
                'default'  => 10,
                'min'      => 0,
                'max'      => 50,
                'step'     => 0.5,
                'resolution' => 0.1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'experience_listing_fee',
                'type'     => 'slider',
                'title'    => esc_html__('Experience Listing Platform Fee (%)', 'homey-child'),
                'subtitle' => esc_html__('Platform fee for experience bookings', 'homey-child'),
                'required' => array('different_fees_by_listing_type', '=', '1'),
                'default'  => 15,
                'min'      => 0,
                'max'      => 50,
                'step'     => 0.5,
                'resolution' => 0.1,
                'display_value' => 'text'
            ],

            // Payment Timing Settings Section
            [
                'id'       => 'payment_timing_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Payment Timing Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Configure when hosts receive their payments', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'payment_delay_hours',
                'type'     => 'slider',
                'title'    => esc_html__('Payment Delay (Hours)', 'homey-child'),
                'subtitle' => esc_html__('Number of hours to hold payment before releasing to host', 'homey-child'),
                'desc'     => esc_html__('Set to 0 for immediate payment release', 'homey-child'),
                'default'  => 0,
                'min'      => 0,
                'max'      => 168, // 7 days
                'step'     => 1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'auto_release_payment',
                'type'     => 'switch',
                'title'    => esc_html__('Auto Release Payment', 'homey-child'),
                'subtitle' => esc_html__('Automatically release payment after delay period', 'homey-child'),
                'default'  => true,
            ],
            [
                'id'       => 'manual_approval_required',
                'type'     => 'switch',
                'title'    => esc_html__('Manual Approval Required', 'homey-child'),
                'subtitle' => esc_html__('Require admin approval before releasing payments', 'homey-child'),
                'default'  => false,
            ],
            [
                'id'       => 'payment_release_conditions',
                'type'     => 'checkbox',
                'title'    => esc_html__('Payment Release Conditions', 'homey-child'),
                'subtitle' => esc_html__('Select conditions that must be met before payment release', 'homey-child'),
                'options'  => array(
                    'guest_checkin' => 'Guest has checked in',
                    'no_disputes' => 'No active disputes',
                    'host_verification' => 'Host account is fully verified',
                    'listing_approved' => 'Listing is approved',
                ),
                'default'  => array('host_verification' => '1'),
            ],

            // Host Account Settings Section
            [
                'id'       => 'host_account_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Host Account Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Configure host account requirements and verification', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'required_verification_level',
                'type'     => 'select',
                'title'    => esc_html__('Required Verification Level', 'homey-child'),
                'subtitle' => esc_html__('Minimum verification level required for hosts', 'homey-child'),
                'options'  => array(
                    'basic' => 'Basic (Email + Phone)',
                    'intermediate' => 'Intermediate (ID + Address)',
                    'advanced' => 'Advanced (Bank Account + Tax Info)',
                    'complete' => 'Complete (All Stripe Requirements)',
                ),
                'default'  => 'complete',
            ],
            [
                'id'       => 'minimum_account_balance',
                'type'     => 'text',
                'title'    => esc_html__('Minimum Account Balance', 'homey-child'),
                'subtitle' => esc_html__('Minimum balance required in host account', 'homey-child'),
                'desc'     => esc_html__('Enter amount in your currency (e.g., 10.00)', 'homey-child'),
                'default'  => '0.00',
                'validate' => 'numeric'
            ],
            [
                'id'       => 'host_account_approval',
                'type'     => 'switch',
                'title'    => esc_html__('Host Account Approval Required', 'homey-child'),
                'subtitle' => esc_html__('Require admin approval for new host accounts', 'homey-child'),
                'default'  => false,
            ],
            [
                'id'       => 'host_verification_documents',
                'type'     => 'checkbox',
                'title'    => esc_html__('Required Verification Documents', 'homey-child'),
                'subtitle' => esc_html__('Select documents required for host verification', 'homey-child'),
                'options'  => array(
                    'government_id' => 'Government ID',
                    'bank_statement' => 'Bank Statement',
                    'tax_document' => 'Tax Document',
                    'business_license' => 'Business License',
                    'insurance_certificate' => 'Insurance Certificate',
                ),
                'default'  => array('government_id' => '1', 'bank_statement' => '1'),
            ],

            // Payment Processing Settings Section
            [
                'id'       => 'payment_processing_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Payment Processing Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Configure payment processing behavior', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'payment_retry_attempts',
                'type'     => 'slider',
                'title'    => esc_html__('Payment Retry Attempts', 'homey-child'),
                'subtitle' => esc_html__('Number of times to retry failed payments', 'homey-child'),
                'default'  => 3,
                'min'      => 0,
                'max'      => 10,
                'step'     => 1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'payment_timeout_minutes',
                'type'     => 'slider',
                'title'    => esc_html__('Payment Timeout (Minutes)', 'homey-child'),
                'subtitle' => esc_html__('Time limit for payment completion', 'homey-child'),
                'default'  => 30,
                'min'      => 5,
                'max'      => 120,
                'step'     => 5,
                'display_value' => 'text'
            ],
            [
                'id'       => 'refund_policy_days',
                'type'     => 'slider',
                'title'    => esc_html__('Refund Policy (Days)', 'homey-child'),
                'subtitle' => esc_html__('Number of days after booking for full refund', 'homey-child'),
                'default'  => 7,
                'min'      => 0,
                'max'      => 30,
                'step'     => 1,
                'display_value' => 'text'
            ],
            [
                'id'       => 'partial_refund_percentage',
                'type'     => 'slider',
                'title'    => esc_html__('Partial Refund Percentage', 'homey-child'),
                'subtitle' => esc_html__('Percentage refunded after policy period', 'homey-child'),
                'default'  => 50,
                'min'      => 0,
                'max'      => 100,
                'step'     => 5,
                'display_value' => 'text'
            ],

            // Notification Settings Section
            [
                'id'       => 'notification_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Notification Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Configure payment-related notifications', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'payment_success_email',
                'type'     => 'switch',
                'title'    => esc_html__('Payment Success Email', 'homey-child'),
                'subtitle' => esc_html__('Send email when payment is successful', 'homey-child'),
                'default'  => true,
            ],
            [
                'id'       => 'payment_failed_email',
                'type'     => 'switch',
                'title'    => esc_html__('Payment Failed Email', 'homey-child'),
                'subtitle' => esc_html__('Send email when payment fails', 'homey-child'),
                'default'  => true,
            ],
            [
                'id'       => 'host_payment_received_email',
                'type'     => 'switch',
                'title'    => esc_html__('Host Payment Received Email', 'homey-child'),
                'subtitle' => esc_html__('Send email to host when payment is received', 'homey-child'),
                'default'  => true,
            ],
            [
                'id'       => 'admin_payment_notification',
                'type'     => 'switch',
                'title'    => esc_html__('Admin Payment Notification', 'homey-child'),
                'subtitle' => esc_html__('Send notification to admin for all payments', 'homey-child'),
                'default'  => false,
            ],

            // Advanced Settings Section
            [
                'id'       => 'advanced_settings',
                'type'     => 'section',
                'title'    => esc_html__( 'Advanced Settings', 'homey-child' ),
                'subtitle' => esc_html__( 'Advanced payment processing options', 'homey-child' ),
                'indent'   => true,
            ],
            [
                'id'       => 'enable_payment_logging',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Payment Logging', 'homey-child'),
                'subtitle' => esc_html__('Log all payment transactions for debugging', 'homey-child'),
                'default'  => true,
            ],
            [
                'id'       => 'stripe_webhook_endpoint',
                'type'     => 'text',
                'title'    => esc_html__('Stripe Webhook Endpoint', 'homey-child'),
                'subtitle' => esc_html__('Custom webhook endpoint for Stripe events', 'homey-child'),
                'desc'     => esc_html__('Leave empty to use default endpoint', 'homey-child'),
                'default'  => '',
            ],
            [
                'id'       => 'enable_test_mode',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Test Mode', 'homey-child'),
                'subtitle' => esc_html__('Use Stripe test mode for all transactions', 'homey-child'),
                'default'  => false,
            ],
            [
                'id'       => 'custom_payment_message',
                'type'     => 'editor',
                'title'    => esc_html__('Custom Payment Message', 'homey-child'),
                'subtitle' => esc_html__('Custom message shown during payment process', 'homey-child'),
                'default'  => 'Processing your payment securely...',
                'args' => array(
                    'teeny' => true,
                    'wpautop' => false,
                    'media_buttons' => false,
                    'textarea_rows' => 3
                )
            ],
        ],
    );

    return $sections;

}
add_filter("redux/options/homey_options/sections", 'homey_create_stripe_dynamic_settings');
