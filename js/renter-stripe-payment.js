jQuery(document).ready(function($) {
    
    // Remove error highlighting on input
    $(document).on('input', '.payment-field', function() {
        removeFieldError($(this));
    });
    
    // Also remove errors on select change
    $(document).on('change', '.payment-field', function() {
        removeFieldError($(this));
    });

    // Card formatting removed - Stripe Checkout handles card input securely on their hosted page

    // Form submission handler
    $('#stripe-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearAllErrors();
        
        // Validate all fields
        var isValid = validateForm();
        
        if (isValid) {
            // All validations passed, send AJAX request
            console.log('All validations passed. Sending payment form data...');
            submitPaymentForm();
        } else {
            console.log('Validation failed. Please fix the errors above.');
            // Show general error message
            var errorCount = $('.field-has-error').length;
            if (errorCount > 0) {
                $('#payment-form-errors').text('Please fix ' + errorCount + ' error(s) before submitting.').show();
            }
        }
    });

    function validateForm() {
        var isValid = true;
        
        // Validate Email
        var email = $('#payment_email').val().trim();
        if (!email) {
            showFieldError('#payment_email', 'Email is required');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showFieldError('#payment_email', 'Please enter a valid email address');
            isValid = false;
        }
        
        // Validate Full Name
        var fullName = $('#full_name').val().trim();
        if (!fullName) {
            showFieldError('#full_name', 'Full name is required');
            isValid = false;
        } else if (fullName.length < 2) {
            showFieldError('#full_name', 'Full name must be at least 2 characters');
            isValid = false;
        }
        
        // Validate Country
        var country = $('#country').val().trim();
        if (!country) {
            showFieldError('#country', 'Country is required');
            isValid = false;
        }
        
        // Validate Address Line 1
        var addressLine1 = $('#address_line1').val().trim();
        if (!addressLine1) {
            showFieldError('#address_line1', 'Address line 1 is required');
            isValid = false;
        }
        
        // Validate City
        var city = $('#city').val().trim();
        if (!city) {
            showFieldError('#city', 'City is required');
            isValid = false;
        }
        
        // Validate State
        var state = $('#state').val().trim();
        if (!state) {
            showFieldError('#state', 'State is required');
            isValid = false;
        }
        
        // Validate ZIP
        var zip = $('#zip').val().trim();
        if (!zip) {
            showFieldError('#zip', 'ZIP code is required');
            isValid = false;
        } else if (zip.length < 3) {
            showFieldError('#zip', 'ZIP code must be at least 3 characters');
            isValid = false;
        }
        
        // Card validation removed - using Stripe Checkout hosted page instead
        
        return isValid;
    }

    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidCardNumber(cardNumber) {
        // Remove spaces
        cardNumber = cardNumber.replace(/\s/g, '');
        
        // Check if it's all digits and between 13-19 digits
        if (!/^\d+$/.test(cardNumber) || cardNumber.length < 13 || cardNumber.length > 19) {
            return false;
        }
        
        // Luhn algorithm validation
        var sum = 0;
        var isEven = false;
        
        for (var i = cardNumber.length - 1; i >= 0; i--) {
            var digit = parseInt(cardNumber[i]);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return sum % 10 === 0;
    }

    function isValidExpiryDate(expiry) {
        // Format: MM / YY
        var cleaned = expiry.trim();
        if (!cleaned) {
            return false;
        }
        
        var parts = cleaned.split('/').map(function(part) {
            return part.trim();
        });
        
        if (parts.length !== 2) {
            return false;
        }
        
        var month = parseInt(parts[0]);
        var yearStr = parts[1];
        
        // Validate month (1-12)
        if (month < 1 || month > 12 || isNaN(month) || parts[0].length !== 2) {
            return false;
        }
        
        // Validate year (should be 2 digits)
        if (isNaN(parseInt(yearStr)) || yearStr.length !== 2) {
            return false;
        }
        
        var year = parseInt(yearStr);
        
        // Get current date
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth() + 1;
        var currentYearShort = currentYear % 100;
        
        // Convert YY to YYYY
        // For card expiry dates, assume 2-digit years represent 2000-2099
        // This is standard for credit card expiry dates
        var fullYear = 2000 + year;
        
        // Check if card is expired
        if (fullYear < currentYear) {
            return false;
        }
        
        if (fullYear === currentYear && month < currentMonth) {
            return false;
        }
        
        return true;
    }

    function showFieldError(fieldSelector, message) {
        var $field = $(fieldSelector);
        var $formGroup = $field.closest('.form-group');
        var $errorSpan = $formGroup.find('.field-error');
        
        // Add error class to field
        $field.addClass('field-has-error');
        
        // Highlight field with red border
        if ($field.is('input')) {
            if ($field.attr('style') && $field.attr('style').indexOf('border-bottom') !== -1) {
                $field.css('border-bottom-color', '#dc3545');
            } else {
                $field.css('border-color', '#dc3545');
            }
        }
        
        // Show error message
        if ($errorSpan.length) {
            $errorSpan.text(message).show();
        } else {
            $formGroup.append('<span class="field-error" style="display: block; color: #dc3545; font-size: 12px; margin-top: 5px;">' + message + '</span>');
        }
        
        // Scroll to first error
        if ($('.field-has-error').length === 1) {
            $('html, body').animate({
                scrollTop: $field.offset().top - 100
            }, 500);
        }
    }

    function removeFieldError($field) {
        $field.removeClass('field-has-error');
        var $formGroup = $field.closest('.form-group');
        $formGroup.find('.field-error').hide().text('');
        // Remove error styling
        if ($field.is('input')) {
            if ($field.attr('style') && $field.attr('style').indexOf('border-bottom') !== -1) {
                $field.css('border-bottom-color', '#ddd');
            } else {
                $field.css('border-color', '#ddd');
            }
        }
    }

    function clearAllErrors() {
        $('.payment-field').removeClass('field-has-error').each(function() {
            var $field = $(this);
            if ($field.is('input')) {
                if ($field.attr('style') && $field.attr('style').indexOf('border-bottom') !== -1) {
                    $field.css('border-bottom-color', '#ddd');
                } else {
                    $field.css('border-color', '#ddd');
                }
            }
        });
        $('.field-error').hide().text('');
        $('#payment-form-errors').hide().text('');
    }

    function submitPaymentForm() {
        var formData = {
            action: 'renter_stripe_payment',
            nonce: stripe_vars.nonce,
            payment_email: $('#payment_email').val().trim(),
            reservation_id: $('input[name="reservation_id"]').val(),
            renter_id: $('input[name="renter_id"]').val(),
            owner_id: $('input[name="owner_id"]').val(),
            return_url: window.location.origin + window.location.pathname
        };

        // Disable submit button
        var $submitBtn = $('.renter-pay-reservation-amount');
        var originalBtnText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Processing...');

        console.log('Sending AJAX request with form data:', formData);
        
        $.ajax({
            url: stripe_vars.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('AJAX response received:', response);
                if (response.success && response.data.checkout_url) {
                    // Redirect to Stripe Checkout hosted payment page
                    console.log('Redirecting to Stripe Checkout:', response.data.checkout_url);
                    window.location.href = response.data.checkout_url;
                } else {
                    // Error from server
                    var errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred. Please try again.';
                    $('#payment-form-errors').text(errorMsg).show();
                    $('html, body').animate({
                        scrollTop: $('#payment-form-errors').offset().top - 100
                    }, 500);
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr);
                var errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }
                $('#payment-form-errors').text(errorMsg).show();
                $('html, body').animate({
                    scrollTop: $('#payment-form-errors').offset().top - 100
                }, 500);
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).text(originalBtnText);
                console.log('AJAX request completed');
            }
        });
    }
});

