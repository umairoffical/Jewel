# 🔍 Stripe User Conditions - Usage Examples

## ✅ **SIMPLE FUNCTIONS YOU CAN USE ANYWHERE**

### **1. Check if Current User Has Completed Stripe Account**

```php
// Simple boolean check
if (homey_is_stripe_completed()) {
    echo "Your Stripe account is ready!";
} else {
    echo "Please complete your Stripe verification.";
}
```

### **2. Check Specific User's Stripe Status**

```php
$user_id = 123;
if (homey_is_user_stripe_completed($user_id)) {
    echo "User $user_id can accept payments!";
} else {
    echo "User $user_id needs to complete Stripe verification.";
}
```

### **3. Get Detailed Status Information**

```php
$status = homey_get_stripe_status();
// Returns: 'not_logged_in', 'not_connected', 'pending', 'under_review', 'complete', 'rejected'

switch ($status) {
    case 'complete':
        echo "Account is fully verified!";
        break;
    case 'pending':
        echo "Account created, verification pending.";
        break;
    case 'not_connected':
        echo "No Stripe account connected.";
        break;
    default:
        echo "Status: " . $status;
}
```

### **4. Check if User Can Accept Payments**

```php
if (homey_can_accept_payments()) {
    echo "You can start accepting payments!";
} else {
    echo "Complete your Stripe verification to accept payments.";
}
```

### **5. Display Status Badge**

```php
echo homey_get_stripe_status_badge();
// Outputs: <span class="badge badge-success">Complete</span>
```

### **6. Display Status Message**

```php
echo homey_get_stripe_status_message();
// Outputs: "Your Stripe account is fully verified and ready to accept payments."
```

### **7. Check if Verification is Needed**

```php
if (homey_needs_stripe_verification()) {
    echo "Please complete your Stripe verification.";
    echo '<a href="' . homey_get_stripe_connect_url() . '">Connect Now</a>';
}
```

### **8. Display Complete Status Widget**

```php
echo homey_display_stripe_status_widget();
// Outputs a complete status widget with badge and message
```

## 🎯 **REAL-WORLD USAGE EXAMPLES**

### **Example 1: In Listing Template**

```php
// Check if listing owner can accept payments
$listing_owner_id = get_post_field('post_author', $listing_id);

if (homey_is_user_stripe_completed($listing_owner_id)) {
    echo '<div class="payment-available">Payments accepted via Stripe</div>';
} else {
    echo '<div class="payment-unavailable">Payments temporarily unavailable</div>';
}
```

### **Example 2: In Dashboard Menu**

```php
// Show different menu items based on Stripe status
if (homey_is_stripe_completed()) {
    echo '<li><a href="/dashboard-payments">View Payments</a></li>';
    echo '<li><a href="/dashboard-earnings">View Earnings</a></li>';
} else {
    echo '<li><a href="/dashboard-host-stripe">Connect Stripe Account</a></li>';
}
```

### **Example 3: In Payment Form**

```php
// Only show payment form if host can accept payments
$host_id = get_post_field('post_author', $listing_id);

if (homey_can_user_accept_payments($host_id)) {
    // Show payment form
    echo '<form class="payment-form">...</form>';
} else {
    echo '<div class="alert alert-warning">';
    echo homey_display_stripe_status_widget($host_id);
    echo '</div>';
}
```

### **Example 4: In User Profile**

```php
// Show Stripe status in user profile
echo '<div class="stripe-status-section">';
echo '<h3>Payment Account Status</h3>';
echo homey_display_stripe_status_widget();
echo '</div>';
```

### **Example 5: Conditional Content**

```php
// Show different content based on status
$status = homey_get_stripe_status();

if ($status === 'complete') {
    echo '<div class="success-message">Your account is ready for payments!</div>';
} elseif ($status === 'pending') {
    echo '<div class="warning-message">Please check your email for verification instructions.</div>';
} elseif ($status === 'not_connected') {
    echo '<div class="info-message">Connect your Stripe account to start accepting payments.</div>';
    echo '<a href="' . homey_get_stripe_connect_url() . '" class="btn btn-primary">Connect Now</a>';
}
```

## 🔧 **ADVANCED USAGE**

### **Check Multiple Users at Once**

```php
$hosts = get_users(array('role' => 'subscriber'));
$verified_hosts = array();

foreach ($hosts as $host) {
    if (homey_is_user_stripe_completed($host->ID)) {
        $verified_hosts[] = $host;
    }
}

echo "Found " . count($verified_hosts) . " verified hosts.";
```

### **Get All Stripe Account Information**

```php
$user_id = get_current_user_id();
$account_id = homey_get_user_stripe_account_id($user_id);
$status = homey_get_user_stripe_status($user_id);
$can_accept = homey_can_user_accept_payments($user_id);

echo "Account ID: " . $account_id;
echo "Status: " . $status;
echo "Can Accept Payments: " . ($can_accept ? 'Yes' : 'No');
```

## 📋 **ALL AVAILABLE FUNCTIONS**

| Function | Purpose | Returns |
|----------|---------|---------|
| `homey_is_stripe_completed()` | Check if current user is complete | `bool` |
| `homey_is_user_stripe_completed($user_id)` | Check if specific user is complete | `bool` |
| `homey_get_stripe_status()` | Get current user's status | `string` |
| `homey_get_user_stripe_status($user_id)` | Get specific user's status | `string` |
| `homey_get_stripe_account_id()` | Get current user's account ID | `string\|false` |
| `homey_get_user_stripe_account_id($user_id)` | Get specific user's account ID | `string\|false` |
| `homey_can_accept_payments()` | Check if current user can accept payments | `bool` |
| `homey_can_user_accept_payments($user_id)` | Check if specific user can accept payments | `bool` |
| `homey_get_stripe_status_badge($status)` | Get status badge HTML | `string` |
| `homey_get_stripe_status_message($status)` | Get status message | `string` |
| `homey_needs_stripe_verification($user_id)` | Check if verification needed | `bool` |
| `homey_get_stripe_connect_url()` | Get connect URL | `string\|false` |
| `homey_display_stripe_status_widget($user_id)` | Display complete widget | `string` |

## 🚀 **READY TO USE!**

All these functions are now available throughout your WordPress site. Use them in templates, shortcodes, widgets, or anywhere you need to check Stripe account status!
