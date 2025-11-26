<?php
/**
 * Template Name: Dashboard Host Stripe Connect
 */
if (!defined('ABSPATH')) {
    exit;
}


get_header();

global $current_user, $post;
wp_get_current_user();
$userID = $current_user->ID;

// Check if user is logged in and is a host
if (!is_user_logged_in() || !homey_is_host()) {
    wp_redirect(home_url());
    exit;
}

// Initialize variables
$account_id = '';
$account_status = '';
$account_details = null;
$stripe_connect = null;

// Handle Stripe callback after verification
if ((isset($_GET['setup']) && $_GET['setup'] === 'complete') || (isset($_GET['refresh']) && $_GET['refresh'] === 'true')) {
    // Security validation: Check if user has a legitimate Stripe account
    if (!$userID || !homey_is_host()) {
        $dashboard_url = homey_get_template_link_dash('template/dashboard-host-stripe.php');
        wp_redirect($dashboard_url);
        exit;
    }
    
    // Force load Stripe classes if not already loaded
    if (!class_exists('Homey_Stripe_Connect')) {
        require_once get_stylesheet_directory() . '/framework/functions/class-homey-stripe-connect.php';
    }
    
    if (class_exists('Homey_Stripe_Connect')) {
        $stripe_connect = Homey_Stripe_Connect::getInstance();
        $account_id = $stripe_connect ? $stripe_connect->get_stripe_account_id($userID) : '';
        
        // Security check: Only process if user actually has a Stripe account
        if (!$account_id) {
            $dashboard_url = homey_get_template_link_dash('template/dashboard-host-stripe.php');
            wp_redirect($dashboard_url);
            exit;
        }
        
        // Additional security: Validate the callback is legitimate
        if (!$stripe_connect->validate_stripe_callback($userID)) {
            $dashboard_url = homey_get_template_link_dash('template/dashboard-host-stripe.php');
            wp_redirect($dashboard_url);
            exit;
        }
        
        // Rate limiting: Check if we've already processed this recently
        $last_processed = get_user_meta($userID, 'stripe_callback_last_processed', true);
        if ($last_processed && (time() - $last_processed) < 60) { // 1 minute cooldown
            $dashboard_url = homey_get_template_link_dash('template/dashboard-host-stripe.php');
            wp_redirect($dashboard_url);
            exit;
        }
        
        // Update last processed time
        update_user_meta($userID, 'stripe_callback_last_processed', time());
        
        // Check account status from Stripe and update database
        $new_status = $stripe_connect->check_and_update_account_status($userID);
        
        if ($new_status) {
            // Account status updated
        }
        
        // Redirect to remove the query parameter - use dynamic URL
        $dashboard_url = homey_get_template_link_dash('template/dashboard-host-stripe.php');
        wp_redirect($dashboard_url);
        exit;
    }
}

// Force load Stripe classes if not already loaded
if (!class_exists('Homey_Stripe_Connect')) {
    require_once get_stylesheet_directory() . '/framework/functions/class-homey-stripe-connect.php';
}

// Check if Stripe Connect class exists and initialize it
if (class_exists('Homey_Stripe_Connect')) {
    $stripe_connect = Homey_Stripe_Connect::getInstance();
    $account_id = $stripe_connect ? $stripe_connect->get_stripe_account_id($userID) : '';
    
    // Get account status and details
    if ($account_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_stripe_connect_accounts';
        $account_status = $wpdb->get_var($wpdb->prepare(
            "SELECT account_status FROM $table_name WHERE user_id = %d",
            $userID
        ));
        
        // Get account details from Stripe if status is complete
        if ($account_status === 'complete') {
            try {
                $stripe = new \Stripe\StripeClient(homey_option('stripe_secret_key'));
                $account_details = $stripe->accounts->retrieve($account_id);
            } catch (Exception $e) {
                // Error retrieving account details
            }
        }
    }
}
?>

<section class="body-area">
    <div class="dashboard-page-title" style="top: 81px;">
        <h1>Stripe Connect</h1>
    </div>

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div class="block">
                                <div class="">
                                    <div class="block-title">
                                        <h2><?php echo esc_html__('Stripe Connect Settings', 'homey'); ?></h2>
                                    </div>

                                    <div class="dashboard-content-block-wrap">
                                        <div id="homey-stripe-connect-message"></div>
                                        <div id="homey-stripe-connect-status"></div>

                                        <?php
                                        if (!class_exists('Homey_Stripe_Connect')) {
                                            ?>
                                            <div class="alert alert-warning">
                                                <?php echo esc_html__('Stripe Connect functionality is not available. Please contact the administrator.', 'homey'); ?>
                                            </div>
                                            <?php
                                        } else {
                                            if (!$account_id) {
                                                // Show connect button if no account exists
                                                ?>
                                                <div class="block-body">
                                                    <div class="stripe-connect-info">
                                                        <h3><?php echo esc_html__('Accept Payments with Stripe', 'homey'); ?></h3>
                                                        <p><?php echo esc_html__('Connect your Stripe account to receive payments directly from guests. The process is simple:', 'homey'); ?></p>
                                                        <ol class="stripe-steps">
                                                            <li><?php echo esc_html__('Click the Connect button below', 'homey'); ?></li>
                                                            <li><?php echo esc_html__('Complete Stripe\'s secure verification process', 'homey'); ?></li>
                                                            <li><?php echo esc_html__('Start accepting payments from guests', 'homey'); ?></li>
                                                        </ol>
                                                        
                                                        <div class="access-info">
                                                            <h4><i class="homey-icon homey-icon-check-circle-1"></i> <?php echo esc_html__('What You\'ll Get Access To:', 'homey'); ?></h4>
                                                            <ul class="access-list">
                                                                <li><i class="homey-icon homey-icon-home"></i> <?php echo esc_html__('Create and manage your property listings', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-calendar"></i> <?php echo esc_html__('Accept bookings and reservations from guests', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-payment"></i> <?php echo esc_html__('Receive payments directly to your bank account', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-chart"></i> <?php echo esc_html__('Track your earnings and payout history', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-settings"></i> <?php echo esc_html__('Manage your host dashboard and settings', 'homey'); ?></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="profile-requirement">
                                                            <div class="alert alert-info">
                                                                <h5><i class="homey-icon homey-icon-info"></i> <?php echo esc_html__('Profile Completion Required', 'homey'); ?></h5>
                                                                <p><?php echo esc_html__('Before you can start accepting bookings and payments, you must complete your host profile with all required information. This includes:', 'homey'); ?></p>
                                                                <ul>
                                                                    <li><?php echo esc_html__('Personal and business information', 'homey'); ?></li>
                                                                    <li><?php echo esc_html__('Tax identification details', 'homey'); ?></li>
                                                                    <li><?php echo esc_html__('Bank account information for payouts', 'homey'); ?></li>
                                                                    <li><?php echo esc_html__('Identity verification documents', 'homey'); ?></li>
                                                                </ul>
                                                                <p><strong><?php echo esc_html__('Note:', 'homey'); ?></strong> <?php echo esc_html__('Your Stripe account must be fully verified before you can receive any payments from guests.', 'homey'); ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <p><small><?php echo esc_html__('Stripe handles all sensitive data collection and verification securely.', 'homey'); ?></small></p>
                                                    </div>
                                                    <button id="homey-stripe-connect-btn" class="btn btn-primary btn-full-width">
                                                        <i class="homey-icon homey-icon-payment"></i>
                                                        <?php echo esc_html__('Connect with Stripe', 'homey'); ?>
                                                    </button>
                                                </div>
                                                <style>
                                                    .stripe-connect-info { margin-bottom: 20px; }
                                                    .stripe-steps { margin: 15px 0; padding-left: 20px; }
                                                    .stripe-steps li { margin-bottom: 10px; }
                                                    .access-info { margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745; }
                                                    .access-info h4, .access-info h5 { color: #28a745; margin-bottom: 15px; }
                                                    .access-list { margin: 15px 0; padding-left: 0; list-style: none; }
                                                    .access-list li { margin-bottom: 0px; padding: 4px 0; display: flex; align-items: center; }
                                                    .access-list li i { margin-right: 12px; color: #28a745; font-size: 16px; width: 20px; }
                                                    .profile-requirement { margin: 25px 0; }
                                                    .profile-requirement .alert { border-left: 4px solid #17a2b8; }
                                                    .profile-requirement h5, .profile-requirement h6 { color: #17a2b8; margin-bottom: 10px; }
                                                    .profile-requirement ul { margin: 10px 0; padding-left: 20px; }
                                                    .profile-requirement li { margin-bottom: 8px; }
                                                    .btn-full-width { width: 100%; padding: 12px; font-size: 16px; }
                                                </style>
                                                <?php
                                            } elseif ($account_status === 'pending') {
                                                // Show pending status - account created but not verified
                                                ?>
                                                <div class="block-body">
                                                    <div class="stripe-account-pending">
                                                        <div class="alert alert-warning">
                                                            <h4><i class="homey-icon homey-icon-warning"></i> <?php echo esc_html__('Account Verification Required', 'homey'); ?></h4>
                                                            <p><?php echo esc_html__('Your Stripe account has been created but is not yet verified. You need to complete the verification process to start accepting payments.', 'homey'); ?></p>
                                                        </div>
                                                        
                                                        <div class="verification-steps">
                                                            <h5><?php echo esc_html__('Next Steps:', 'homey'); ?></h5>
                                                            <ol>
                                                                <li><?php echo esc_html__('Check your email for verification instructions from Stripe', 'homey'); ?></li>
                                                                <li><?php echo esc_html__('Complete your business information and tax details', 'homey'); ?></li>
                                                                <li><?php echo esc_html__('Upload required verification documents', 'homey'); ?></li>
                                                                <li><?php echo esc_html__('Wait for Stripe to review and approve your account', 'homey'); ?></li>
                                                            </ol>
                                                        </div>
                                                        
                                                        <div class="access-info">
                                                            <h5><i class="homey-icon homey-icon-check-circle-1"></i> <?php echo esc_html__('Once Verified, You\'ll Get Access To:', 'homey'); ?></h5>
                                                            <ul class="access-list">
                                                                <li><i class="homey-icon homey-icon-home"></i> <?php echo esc_html__('Create and manage your property listings', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-calendar"></i> <?php echo esc_html__('Accept bookings and reservations from guests', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-payment"></i> <?php echo esc_html__('Receive payments directly to your bank account', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-chart"></i> <?php echo esc_html__('Track your earnings and payout history', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-settings"></i> <?php echo esc_html__('Manage your host dashboard and settings', 'homey'); ?></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="profile-requirement">
                                                            <div class="alert alert-warning">
                                                                <h6><i class="homey-icon homey-icon-warning"></i> <?php echo esc_html__('Important:', 'homey'); ?></h6>
                                                                <p><?php echo esc_html__('You must complete your Stripe verification before you can start accepting bookings and payments. Your profile is currently incomplete and needs to be finished.', 'homey'); ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="account-actions">
                                                            <button id="homey-stripe-connect-btn" class="btn btn-primary btn-full-width">
                                                                <i class="homey-icon homey-icon-payment"></i>
                                                                <?php echo esc_html__('Complete Verification', 'homey'); ?>
                                                            </button>
                                                            <p class="text-center">
                                                                <small><?php echo esc_html__('This will take you back to Stripe to complete your verification.', 'homey'); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <style>
                                                    .stripe-account-pending { padding: 20px; }
                                                    .verification-steps { margin: 20px 0; }
                                                    .verification-steps ol { margin: 10px 0; padding-left: 20px; }
                                                    .verification-steps li { margin-bottom: 8px; }
                                                    .access-info { margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745; }
                                                    .access-info h4, .access-info h5 { color: #28a745; margin-bottom: 15px; }
                                                    .access-list { margin: 15px 0; padding-left: 0; list-style: none; }
                                                    .access-list li { margin-bottom: 12px; padding: 8px 0; display: flex; align-items: center; }
                                                    .access-list li i { margin-right: 12px; color: #28a745; font-size: 16px; width: 20px; }
                                                    .profile-requirement { margin: 25px 0; }
                                                    .profile-requirement .alert { border-left: 4px solid #ffc107; }
                                                    .profile-requirement h5, .profile-requirement h6 { color: #856404; margin-bottom: 10px; }
                                                    .profile-requirement ul { margin: 10px 0; padding-left: 20px; }
                                                    .profile-requirement li { margin-bottom: 8px; }
                                                    .account-actions { margin: 20px 0; }
                                                    .btn-full-width { width: 100%; padding: 12px; font-size: 16px; }
                                                </style>
                                                <?php
                                            } elseif ($account_status === 'under_review') {
                                                // Show under review status - account submitted but not yet approved
                                                ?>
                                                <div class="block-body">
                                                    <div class="stripe-account-under-review">
                                                        <div class="alert alert-info">
                                                            <h4><i class="homey-icon homey-icon-clock"></i> <?php echo esc_html__('Account Under Review', 'homey'); ?></h4>
                                                            <p><?php echo esc_html__('Your Stripe account has been submitted and is currently being reviewed by Stripe. This process usually takes a few minutes to a few hours.', 'homey'); ?></p>
                                                        </div>
                                                        
                                                        <div class="review-info">
                                                            <h5><?php echo esc_html__('What\'s Happening:', 'homey'); ?></h5>
                                                            <ul>
                                                                <li><?php echo esc_html__('Stripe is reviewing your submitted information', 'homey'); ?></li>
                                                                <li><?php echo esc_html__('Your identity and business details are being verified', 'homey'); ?></li>
                                                                <li><?php echo esc_html__('You\'ll receive an email notification when the review is complete', 'homey'); ?></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="access-info">
                                                            <h5><i class="homey-icon homey-icon-check-circle-1"></i> <?php echo esc_html__('Once Approved, You\'ll Get Access To:', 'homey'); ?></h5>
                                                            <ul class="access-list">
                                                                <li><i class="homey-icon homey-icon-home"></i> <?php echo esc_html__('Create and manage your property listings', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-calendar"></i> <?php echo esc_html__('Accept bookings and reservations from guests', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-payment"></i> <?php echo esc_html__('Receive payments directly to your bank account', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-chart"></i> <?php echo esc_html__('Track your earnings and payout history', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-settings"></i> <?php echo esc_html__('Manage your host dashboard and settings', 'homey'); ?></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="account-actions">
                                                            <button id="homey-stripe-connect-btn" class="btn btn-primary btn-full-width">
                                                                <i class="homey-icon homey-icon-refresh"></i>
                                                                <?php echo esc_html__('Check Status', 'homey'); ?>
                                                            </button>
                                                            <p class="text-center">
                                                                <small><?php echo esc_html__('This will refresh your account status from Stripe.', 'homey'); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <style>
                                                    .stripe-account-under-review { padding: 20px; }
                                                    .review-info { margin: 20px 0; }
                                                    .review-info ul { margin: 10px 0; padding-left: 20px; }
                                                    .review-info li { margin-bottom: 8px; }
                                                    .access-info { margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8; }
                                                    .access-info h4, .access-info h5 { color: #17a2b8; margin-bottom: 15px; }
                                                    .access-list { margin: 15px 0; padding-left: 0; list-style: none; }
                                                    .access-list li { margin-bottom: 12px; padding: 8px 0; display: flex; align-items: center; }
                                                    .access-list li i { margin-right: 12px; color: #17a2b8; font-size: 16px; width: 20px; }
                                                    .account-actions { margin: 20px 0; }
                                                    .btn-full-width { width: 100%; padding: 12px; font-size: 16px; }
                                                </style>
                                                <?php
                                            } elseif ($account_status === 'complete' && $account_details) {
                                                // Show fully connected account with details
                                                ?>
                                                <div class="block-body">
                                                    <div class="stripe-account-status">
                                                        <div class="alert alert-success">
                                                            <h4><i class="homey-icon homey-icon-check-circle-1"></i> <?php echo esc_html__('Account Fully Connected', 'homey'); ?></h4>
                                                            <p><?php echo esc_html__('Your Stripe account is verified and ready to accept payments from guests.', 'homey'); ?></p>
                                                        </div>
                                                        
                                                        <div class="account-details">
                                                            <h5><?php echo esc_html__('Account Information', 'homey'); ?></h5>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong><?php echo esc_html__('Account ID:', 'homey'); ?></strong> <?php echo esc_html(substr($account_id, 0, 20) . '...'); ?></p>
                                                                    <p><strong><?php echo esc_html__('Status:', 'homey'); ?></strong> 
                                                                        <span class="badge badge-success"><?php echo esc_html(ucfirst($account_status)); ?></span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong><?php echo esc_html__('Country:', 'homey'); ?></strong> <?php echo esc_html(ucfirst($account_details->country ?? 'N/A')); ?></p>
                                                                    <p><strong><?php echo esc_html__('Charges Enabled:', 'homey'); ?></strong> 
                                                                        <span class="badge <?php echo ($account_details->charges_enabled ?? false) ? 'badge-success' : 'badge-warning'; ?>">
                                                                            <?php echo ($account_details->charges_enabled ?? false) ? esc_html__('Yes', 'homey') : esc_html__('No', 'homey'); ?>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="access-info">
                                                            <h5><i class="homey-icon homey-icon-check-circle-1"></i> <?php echo esc_html__('You Now Have Access To:', 'homey'); ?></h5>
                                                            <ul class="access-list">
                                                                <li><i class="homey-icon homey-icon-home"></i> <?php echo esc_html__('Create and manage your property listings', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-calendar"></i> <?php echo esc_html__('Accept bookings and reservations from guests', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-payment"></i> <?php echo esc_html__('Receive payments directly to your bank account', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-chart"></i> <?php echo esc_html__('Track your earnings and payout history', 'homey'); ?></li>
                                                                <li><i class="homey-icon homey-icon-settings"></i> <?php echo esc_html__('Manage your host dashboard and settings', 'homey'); ?></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="account-actions">
                                                            <a href="https://dashboard.stripe.com/connect/accounts/<?php echo esc_attr($account_id); ?>" target="_blank" class="btn btn-primary btn-full-width">
                                                                <i class="homey-icon homey-icon-dashboard"></i>
                                                                <?php echo esc_html__('Go to Stripe Dashboard', 'homey'); ?>
                                                            </a>
                                                            <p class="text-center">
                                                                <small><?php echo esc_html__('Manage your payouts, view transactions, and update account settings.', 'homey'); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <style>
                                                    .stripe-account-status { padding: 20px; }
                                                    .account-details { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
                                                    .account-details h5 { margin-bottom: 15px; color: #333; }
                                                    .account-details p { margin-bottom: 8px; }
                                                    .access-info { margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745; }
                                                    .access-info h4, .access-info h5 { color: #28a745; margin-bottom: 15px; }
                                                    .access-list { margin: 15px 0; padding-left: 0; list-style: none; }
                                                    .access-list li { margin-bottom: 12px; padding: 8px 0; display: flex; align-items: center; }
                                                    .access-list li i { margin-right: 12px; color: #28a745; font-size: 16px; width: 20px; }
                                                    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                                                    .badge-success { background-color: #28a745; color: white; }
                                                    .badge-warning { background-color: #ffc107; color: #212529; }
                                                    .account-actions { margin: 20px 0; }
                                                    .btn-full-width { width: 100%; padding: 12px; font-size: 16px; }
                                                </style>
                                                <?php
                                            } else {
                                                // Show error status
                                                ?>
                                                <div class="block-body">
                                                    <div class="stripe-account-error">
                                                        <div class="alert alert-danger">
                                                            <h4><i class="homey-icon homey-icon-close"></i> <?php echo esc_html__('Account Error', 'homey'); ?></h4>
                                                            <p><?php echo esc_html__('There was an issue with your Stripe account. Please try connecting again.', 'homey'); ?></p>
                                                            <?php if ($account_status) { ?>
                                                                <p><strong><?php echo esc_html__('Status:', 'homey'); ?></strong> <?php echo esc_html(ucfirst($account_status)); ?></p>
                                                            <?php } ?>
                                                        </div>
                                                        
                                                        <div class="account-actions">
                                                            <button id="homey-stripe-connect-btn" class="btn btn-primary btn-full-width">
                                                                <i class="homey-icon homey-icon-payment"></i>
                                                                <?php echo esc_html__('Reconnect with Stripe', 'homey'); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <style>
                                                    .stripe-account-error { padding: 20px; }
                                                    .account-actions { margin: 20px 0; }
                                                    .btn-full-width { width: 100%; padding: 12px; font-size: 16px; }
                                                </style>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>