(function($) {
    "use strict";

    if (typeof HOMEY_stripe_connect_vars === 'undefined') {
        console.error('Stripe Connect variables not found');
        return;
    }

    // Handle Stripe Connect account creation
    $('#homey-stripe-connect-btn').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $message = $('#homey-stripe-connect-message');

        $button.prop('disabled', true);
        $button.find('i').removeClass().addClass('homey-icon homey-icon-loading-half fa-spinner');

        $.ajax({
            url: HOMEY_stripe_connect_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'homey_create_stripe_connect_account',
                nonce: HOMEY_stripe_connect_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.url;
                } else {
                    $message.html('<div class="alert alert-danger">' + (response.data || 'Connection failed') + '</div>');
                    $button.prop('disabled', false);
                    $button.find('i').removeClass();
                }
            },
            error: function() {
                $message.html('<div class="alert alert-danger">Connection failed. Please try again.</div>');
                $button.prop('disabled', false);
                $button.find('i').removeClass();
            }
        });
    });

})(jQuery);