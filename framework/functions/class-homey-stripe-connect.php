<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Homey_Stripe_Connect')) {
    class Homey_Stripe_Connect {
        private static $instance = null;
        private $stripe = null;

        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // Initialize Stripe with your secret key
            if (class_exists('Stripe\Stripe')) {
                try {
                    $stripe_secret_key = homey_option('stripe_secret_key');
                    if (empty($stripe_secret_key)) {
                        throw new Exception('Stripe secret key is not configured');
                    }

                    \Stripe\Stripe::setApiKey($stripe_secret_key);
                    $this->stripe = new \Stripe\StripeClient($stripe_secret_key);
                    
                } catch (Exception $e) {
                    // Stripe initialization failed
                }
            }

            // Add necessary hooks
            add_action('init', array($this, 'init_stripe_connect'));
            add_action('wp_ajax_homey_create_stripe_connect_account', array($this, 'create_connect_account'));
            add_action('wp_ajax_nopriv_stripe_webhook', array($this, 'handle_stripe_webhook'));
            add_action('wp_ajax_stripe_webhook', array($this, 'handle_stripe_webhook'));
        }

        public function init_stripe_connect() {
            $this->create_stripe_connect_table();
        }

        private function create_stripe_connect_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
            
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                stripe_account_id varchar(255) NOT NULL,
                account_status varchar(50) DEFAULT 'pending',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_id (user_id)
            )";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function create_connect_account() {
            try {
                // Basic checks
                if (!wp_verify_nonce($_POST['nonce'], 'homey_stripe_connect_nonce')) {
                    wp_send_json_error('Invalid nonce');
                    return;
                }

                $user_id = get_current_user_id();
                if (!$user_id) {
                    wp_send_json_error('User not logged in');
                    return;
                }

                // Get user email
                $user = get_userdata($user_id);
                
                // Create Express account
                $account = $this->stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'US',
                    'email' => $user->user_email,
                    'capabilities' => [
                        'transfers' => ['requested' => true]
                    ]
                ]);

                // Save to database
                $this->save_stripe_account($user_id, $account->id);

                $dashboard_host_stripe = homey_get_template_link_dash('template/dashboard-host-stripe.php');

                // Create onboarding link
                $account_link = $this->stripe->accountLinks->create([
                    'account' => $account->id,
                    'refresh_url' => $dashboard_host_stripe . '?refresh=true',
                    'return_url' => $dashboard_host_stripe . '?setup=complete',
                    'type' => 'account_onboarding',
                ]);

                wp_send_json_success(['url' => $account_link->url]);

            } catch (Exception $e) {
                wp_send_json_error('Failed to create Stripe account: ' . $e->getMessage());
            }
        }

        private function save_stripe_account($user_id, $account_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
            
            $wpdb->replace(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'stripe_account_id' => $account_id,
                    'account_status' => 'pending'
                ),
                array('%d', '%s', '%s')
            );
        }

        public function get_stripe_account_id($user_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
            
            return $wpdb->get_var($wpdb->prepare(
                "SELECT stripe_account_id FROM $table_name WHERE user_id = %d",
                $user_id
            ));
        }

        /**
         * Handle Stripe webhook events
         */
        public function handle_stripe_webhook() {
            $input = @file_get_contents('php://input');
            $event = json_decode($input);
            
            if (!$event) {
                http_response_code(400);
                exit('Invalid JSON');
            }
            
            // Handle account.updated event
            if ($event->type === 'account.updated') {
                $account_id = $event->data->object->id;
                $charges_enabled = $event->data->object->charges_enabled;
                $details_submitted = $event->data->object->details_submitted;
                
                // Update account status based on Stripe data
                $status = 'pending';
                if ($charges_enabled && $details_submitted) {
                    $status = 'complete';
                } elseif ($details_submitted) {
                    $status = 'under_review';
                }
                
                $this->update_account_status($account_id, $status);
            }
            
            http_response_code(200);
            exit('OK');
        }
        
        /**
         * Update account status in database
         */
        private function update_account_status($account_id, $status) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
            
            $wpdb->update(
                $table_name,
                array('account_status' => $status),
                array('stripe_account_id' => $account_id),
                array('%s'),
                array('%s')
            );
        }

        /**
         * Check and update account status from Stripe API
         */
        public function check_and_update_account_status($user_id) {
            try {
                $account_id = $this->get_stripe_account_id($user_id);
                if (!$account_id) {
                    return false;
                }

                $account = $this->stripe->accounts->retrieve($account_id);
                
                // Determine status based on Stripe account details
                $status = 'pending';
                if ($account->charges_enabled && $account->details_submitted) {
                    $status = 'complete';
                } elseif ($account->details_submitted) {
                    $status = 'under_review';
                }
                
                // Update database with new status
                global $wpdb;
                $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
                $wpdb->update(
                    $table_name,
                    array('account_status' => $status),
                    array('user_id' => $user_id),
                    array('%s'),
                    array('%d')
                );
                
                return $status;
                
            } catch (Exception $e) {
                return false;
            }
        }

        /**
         * Validate if a Stripe callback is legitimate
         */
        public function validate_stripe_callback($user_id) {
            try {
                $account_id = $this->get_stripe_account_id($user_id);
                if (!$account_id) {
                    return false;
                }

                $account = $this->stripe->accounts->retrieve($account_id);
                
                // Check if account exists and is valid
                if (!$account || !$account->id) {
                    return false;
                }
                
                // Check if account has any meaningful progress
                // Allow accounts that are in the process of being set up
                // Only reject if account is very old (24+ hours) with no progress
                $created_time = $account->created;
                $time_since_creation = time() - $created_time;
                
                // If account is older than 24 hours and still has no details, might be suspicious
                if ($time_since_creation > 86400 && !$account->details_submitted && !$account->charges_enabled) {
                    return false;
                }
                
                // Allow accounts that are being processed or have any progress
                return true;
                
                return true;
                
        } catch (Exception $e) {
            return false;
        }
        }

        /**
         * Create a payment intent for a connected account
         */
        public function create_connected_payment_intent($amount, $currency, $host_id, $application_fee_amount = 0) {
            if (!$this->stripe) {
                throw new Exception('Stripe is not properly configured');
            }

            try {
                $account_id = $this->get_stripe_account_id($host_id);
                if (!$account_id) {
                    throw new Exception('Host does not have a connected Stripe account');
                }

                $intent = $this->stripe->paymentIntents->create([
                    'amount' => $amount,
                    'currency' => $currency,
                    'application_fee_amount' => $application_fee_amount,
                    'transfer_data' => [
                        'destination' => $account_id,
                    ],
                ]);

                return $intent;

            } catch (Exception $e) {
                throw $e;
            }
        }
    }
}

// Initialize the class
add_action('init', function() {
    Homey_Stripe_Connect::getInstance();
}, 5);