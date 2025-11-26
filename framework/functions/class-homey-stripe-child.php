<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Homey_Stripe')) {
    // Try to load the parent class manually
    $parent_class_path = WP_PLUGIN_DIR . '/homey-core/classes/class-stripe.php';
    if (file_exists($parent_class_path)) {
        require_once $parent_class_path;
    }
    
    if (!class_exists('Homey_Stripe')) {
        return;
    }
}

class Homey_Stripe_Child extends Homey_Stripe {
    private $stripe_connect;
    protected $currency;
    protected $payment_intent;
    protected $payment_intent_secret;

    function __construct($userID) {
        parent::__construct($userID);
        $this->stripe_connect = Homey_Stripe_Connect::getInstance();
        $this->currency = esc_html(homey_option('payment_currency', 'USD'));
    }

    /**
     * Override parent method to handle connected accounts
     */
    function homey_stripe_paymenet_intent($amount, $metadata, $description) {
        if ($this->currency == 'JPY') {
            $amount = $amount;
        } else {
            $amount = $amount * 100;
        }

        try {
            // If this is a reservation payment, route it to the host's connected account
            if (isset($metadata['payment_type']) && $metadata['payment_type'] == 'reservation_fee') {
                // Get listing_id from metadata or try to get it from reservation_id
                $listing_id = '';
                if (isset($metadata['listing_id']) && !empty($metadata['listing_id'])) {
                    $listing_id = $metadata['listing_id'];
                } elseif (isset($metadata['reservation_id_for_stripe'])) {
                    // Fallback: try to get listing_id from reservation
                    $listing_id = get_post_meta($metadata['reservation_id_for_stripe'], 'reservation_listing_id', true);
                }
                
                // Validate that listing_id exists
                if (empty($listing_id)) {
                    throw new Exception('Listing ID is required for reservation payments');
                }
                
                // Get the listing's author (host)
                $host_id = get_post_field('post_author', $listing_id);
                
                // Validate that host_id exists
                if (!$host_id) {
                    throw new Exception('Host not found for listing');
                }
                
                // Calculate platform fee (you can adjust this calculation)
                $platform_fee = $this->calculate_platform_fee($amount);

                // Create payment intent with connected account
                $payment_intent = $this->stripe_connect->create_connected_payment_intent(
                    $amount,
                    $this->currency,
                    $host_id,
                    $platform_fee
                );
            } else {
                // For non-reservation payments (like featured listings), use regular payment intent
                $payment_intent = \Stripe\PaymentIntent::create([
                    "amount" => $amount,
                    "currency" => $this->currency,
                    "payment_method_types" => ["card"],
                    "description" => $description,
                    "metadata" => $metadata
                ]);
            }

            $this->payment_intent = $payment_intent->id;
            $this->payment_intent_secret = $payment_intent->client_secret;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Calculate platform fee
     */
    private function calculate_platform_fee($amount) {
        // Use dynamic settings for platform fee calculation
        $dynamic_settings = Homey_Dynamic_Settings::getInstance();
        return $dynamic_settings->calculate_platform_fee($amount);
    }

    /**
     * Get payment intent secret
     */
    public function get_payment_intent_secret() {
        return $this->payment_intent_secret;
    }

    /**
     * Get payment intent ID
     */
    public function get_payment_intent() {
        return $this->payment_intent;
    }

    /**
     * Get currency
     */
    public function get_currency() {
        return $this->currency;
    }
}