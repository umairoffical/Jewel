(function($) {
    "use strict";

    var stripe = null;
    var elements = null;
    var card = null;
    var cardNumber = null;
    var cardExpiry = null;
    var cardCvc = null;
    var paymentProcessor = null;

    // Initialize payment processor
    function initPaymentProcessor() {
        paymentProcessor = {
            reservationId: null,
            listingId: null,
            hostId: null,
            amount: 0,
            currency: 'USD',
            isProcessing: false
        };
    }

    // Validate host account before showing payment form
    function validateHostAccount(listingId) {
        
        return $.ajax({
            url: HOMEY_CHILD_stripe_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'homey_validate_host_account',
                listing_id: listingId,
                nonce: HOMEY_CHILD_stripe_vars.nonce
            }
        });
    }

    // Initialize Stripe with enhanced error handling
    function initStripe() {
        if (typeof HOMEY_CHILD_stripe_vars === 'undefined' || !HOMEY_CHILD_stripe_vars.stripe_publishable_key) {
            console.error('Stripe configuration not found');
            return false;
        }

        if (typeof Stripe === 'undefined') {
            console.error('Stripe library not loaded');
            return false;
        }

        try {
            stripe = Stripe(HOMEY_CHILD_stripe_vars.stripe_publishable_key);
            
            elements = stripe.elements({
                fonts: [{
                    cssSrc: 'https://fonts.googleapis.com/css?family=Roboto',
                }],
                locale: 'auto'
            });

            return true;
        } catch (error) {
            console.error('Stripe initialization failed:', error);
            return false;
        }
    }

    // Create card element with enhanced styling
    function createCardElement() {
        if (!elements) return null;

        var style = {
            base: {
                color: "#32325d",
                fontFamily: "-apple-system, BlinkMacSystemFont, sans-serif",
                fontSmoothing: "antialiased",
                fontSize: "16px",
                "::placeholder": {
                    color: "#aab7c4"
                }
            },
            invalid: {
                color: "#fa755a",
                iconColor: "#fa755a"
            }
        };

        // Create the combined card element (simplified approach)
        card = elements.create('card', {
            iconStyle: 'solid',
            style: style,
            hidePostalCode: false
        });

        return card;
    }

    // Mount card element with error handling
    function mountCardElement() {
        if (!card) {
            console.error('Card element not created');
            return false;
        }

        try {
            // Mount to the standard card container
            var cardContainer = document.getElementById('homey_stripe_card');
            if (!cardContainer) {
                console.error('Card container #homey_stripe_card not found');
                return false;
            }

            
            // Clear any existing content
            cardContainer.innerHTML = '';
            
            // Mount the card element
            card.mount(cardContainer);
            
            // Add change event listener
            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (displayError) {
                    if (event.error) {
                        displayError.textContent = event.error.message;
                        displayError.style.display = 'block';
                    } else {
                        displayError.textContent = '';
                        displayError.style.display = 'none';
                    }
                }
            });
            
            
            // Test if the card element is editable
            setTimeout(function() {
                var cardInputs = cardContainer.querySelectorAll('input');
                if (cardInputs.length > 0) {
                } else {
                    console.warn('Card element may not be properly mounted');
                }
            }, 500);
            
            return true;
            
        } catch (error) {
            console.error('Card mounting failed:', error);
            return false;
        }
    }

    // Validate payment before processing
    function validatePayment(reservationId) {
        
        return $.ajax({
            url: HOMEY_CHILD_stripe_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'homey_validate_payment',
                reservation_id: reservationId,
                nonce: HOMEY_CHILD_stripe_vars.nonce
            }
        });
    }

    // Process payment success
    function processPaymentSuccess(paymentIntentId, reservationId) {
        
        return $.ajax({
            url: HOMEY_CHILD_stripe_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'homey_process_payment_success',
                payment_intent_id: paymentIntentId,
                reservation_id: reservationId,
                nonce: HOMEY_CHILD_stripe_vars.nonce
            }
        });
    }

    // Get user billing information
    function getBillingInformation() {
        var cardholderName = document.getElementById('stripe_cardholder_name');
        var cardholderEmail = document.getElementById('stripe_cardholder_email');
        
        return {
            name: cardholderName ? cardholderName.value : '',
            email: cardholderEmail ? cardholderEmail.value : '',
            address: {
                line1: '510 Townsend St', // You can make this dynamic
                postal_code: '98140',
                city: 'San Francisco',
                state: 'CA',
                country: 'US'
            }
        };
    }

    // Show error message
    function showError(message, containerId = 'homey_stripe_message') {
        var container = $('#' + containerId);
        container.empty().show().html(
            '<div class="alert alert-danger alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button>' +
            message +
            '</div>'
        );
    }

    // Show success message
    function showSuccess(message, containerId = 'homey_stripe_message') {
        var container = $('#' + containerId);
        container.empty().show().html(
            '<div class="alert alert-success alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button>' +
            message +
            '</div>'
        );
    }

    // Set button loading state
    function setButtonLoading(buttonId, isLoading) {
        var button = $('#' + buttonId);
        if (isLoading) {
            button.prop('disabled', true);
            button.children('i').remove();
            button.prepend('<i class="homey-icon homey-icon-loading-half fa-spinner"></i>');
        } else {
            button.prop('disabled', false);
            button.children('i').remove();
        }
    }

    // Handle payment result
    function handlePaymentResult(result, reservationId) {
        if (result.error) {
            // Payment failed
            setButtonLoading('homey_stripe_submit_btn', false);
            showError('Payment failed: ' + result.error.message);
            paymentProcessor.isProcessing = false;
        } else {
            // Payment succeeded
            showSuccess('Payment successful! Processing...');
            
            // Step 3: Process payment success on server
            processPaymentSuccess(result.paymentIntent.id, reservationId)
                .done(function(successResponse) {
                    if (successResponse.success) {
                        showSuccess('Payment processed successfully! Redirecting...');
                        
                        // Redirect after success
                        setTimeout(function() {
                            var redirectType = document.getElementById('redirect_type');
                            if (redirectType) {
                                if (redirectType.value === 'reservation_detail_link') {
                                    var resReturnLink = HOMEY_CHILD_stripe_vars.reservation_return_link;
                                    if (HOMEY_CHILD_stripe_vars.is_experience_template == 1) {
                                        resReturnLink = HOMEY_CHILD_stripe_vars.reservation_exp_return_link;
                                    }
                                    window.location.href = resReturnLink;
                                }
                            }
                        }, 2000);
                    } else {
                        showError('Payment processing failed: ' + successResponse.data);
                        setButtonLoading('homey_stripe_submit_btn', false);
                    }
                    paymentProcessor.isProcessing = false;
                })
                .fail(function() {
                    showError('Failed to process payment. Please contact support.');
                    setButtonLoading('homey_stripe_submit_btn', false);
                    paymentProcessor.isProcessing = false;
                });
        }
    }

    // Handle payment processing
    function handlePayment(ev) {
        ev.preventDefault();
        ev.stopPropagation();

        if (paymentProcessor.isProcessing) {
            return;
        }

        var cardButton = document.getElementById('homey_stripe_submit_btn');
        var reservationId = $('#reservation_id').val();
        
        if (!reservationId) {
            showError('Reservation ID not found');
            return;
        }

        paymentProcessor.isProcessing = true;
        setButtonLoading('homey_stripe_submit_btn', true);

        // Step 1: Validate payment
        validatePayment(reservationId)
            .done(function(response) {
                if (response.success) {
                    var clientSecret = response.data.client_secret;
                    var billingInfo = getBillingInformation();

                    // Step 2: Process payment with Stripe
                    stripe.handleCardPayment(clientSecret, card, {
                        payment_method_data: {
                            billing_details: billingInfo
                        }
                    }).then(function(result) {
                        handlePaymentResult(result, reservationId);
                    });
                } else {
                    // Validation failed
                    setButtonLoading('homey_stripe_submit_btn', false);
                    showError(response.data || 'Payment validation failed');
                    paymentProcessor.isProcessing = false;
                }
            })
            .fail(function() {
                setButtonLoading('homey_stripe_submit_btn', false);
                showError('Payment validation failed. Please try again.');
                paymentProcessor.isProcessing = false;
            });
    }

    // Initialize payment gateway selection
    function initPaymentGatewaySelection() {
        $(".homey_check_gateway").on('click', function() {
            var paymentGateway = $(this).val();
            selectPaymentGateway(paymentGateway);
        });

        // Initialize with current selection
        var currentGateway = $(".homey_check_gateway:checked").val();
        if (currentGateway) {
            selectPaymentGateway(currentGateway);
        }
    }

    // Select payment gateway
    function selectPaymentGateway(paymentGateway) {
        if (paymentGateway === 'stripe') {
            $('#without_stripe').hide();
            $('#stripe_main_wrap').show();
            
            // Initialize Stripe payment when selected
            waitForStripe(function() {
                setTimeout(function() {
                    initEnhancedStripePayment();
                }, 100);
            });
            
            // Validate host account when Stripe is selected
            var listingId = $('#reservation_id').data('listing-id') || getListingIdFromReservation();
            if (listingId) {
                validateHostAccount(listingId)
                    .done(function(response) {
                        if (!response.success) {
                            showError('Host account validation failed: ' + response.data);
                            $('#homey_stripe_submit_btn').prop('disabled', true);
                        } else {
                            $('#homey_stripe_submit_btn').prop('disabled', false);
                        }
                    })
                    .fail(function() {
                        showError('Failed to validate host account');
                        $('#homey_stripe_submit_btn').prop('disabled', true);
                    });
            }
        } else {
            $('#without_stripe').show();
            $('#stripe_main_wrap').hide();
        }
    }

    // Get listing ID from reservation
    function getListingIdFromReservation() {
        var reservationId = $('#reservation_id').val();
        if (reservationId) {
            // You might need to make an AJAX call to get listing ID
            // For now, return null and handle in validation
            return null;
        }
        return null;
    }

    // Initialize enhanced Stripe payment
    function initEnhancedStripePayment() {
        
        if (!initStripe()) {
            console.error('Stripe initialization failed');
            return;
        }

        var cardElement = createCardElement();
        if (!cardElement) {
            console.error('Card element creation failed');
            return;
        }


        // Mount the card element
        if (!mountCardElement()) {
            console.error('Card mounting failed');
            return;
        }

        // Add payment button event listener
        var cardButton = document.getElementById('homey_stripe_submit_btn');
        if (cardButton) {
            cardButton.addEventListener('click', handlePayment);
        } else {
            console.warn('Payment button not found');
        }
    }

    // Wait for Stripe to be loaded
    function waitForStripe(callback, maxAttempts = 10) {
        var attempts = 0;
        var checkStripe = function() {
            attempts++;
            if (typeof Stripe !== 'undefined') {
                callback();
            } else if (attempts < maxAttempts) {
                setTimeout(checkStripe, 200);
            } else {
                console.error('Stripe failed to load after', maxAttempts, 'attempts');
            }
        };
        checkStripe();
    }

    // Initialize everything
    function init() {
        console.log('Initializing payment system...');
        initPaymentProcessor();
        initPaymentGatewaySelection();
        
        // Initialize Stripe payment when Stripe is selected
        var selectedGateway = $(".homey_check_gateway:checked").val();
        console.log('Selected payment gateway:', selectedGateway);
        
        if (selectedGateway === 'stripe') {
            // Wait for Stripe to be loaded
            waitForStripe(function() {
                setTimeout(function() {
                    initEnhancedStripePayment();
                }, 100);
            });
        }
    }

    // Start when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);
