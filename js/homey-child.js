jQuery(document).ready(function($) {

    $( '#add_more_extra_services_child' ).on('click', function( e ){
        e.preventDefault();

        var numVal = $(this).data("increment") + 1;
        $(this).data('increment', numVal);
        $(this).attr({
            "data-increment" : numVal
        });

        var newOption = '' +
            '<div class="more_extra_services_wrap" style="margin-top:10px;">'+
                '<div class="row">'+
                    '<div class="col-sm-6 col-xs-12">'+
                        '<div class="form-group">'+
                            '<label for="name">Enter Add on</label>'+
                            '<input type="text" name="extra_price['+numVal+'][name]" class="form-control" placeholder="Enter Add On">'+
                        '</div>'+
                    '</div>'+
                    '<div class="col-sm-6 col-xs-12">'+
                        '<div class="form-group">'+
                            '<label for="price"> Price Per Day (USD)</label>'+
                            '<input type="text" name="extra_price['+numVal+'][price]" class="form-control" placeholder="Enter Price Per Day">'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-sm-12 col-xs-12">'+
                        '<button type="button" data-remove="'+numVal+'" class="remove-extra-services-child btn btn-primary btn-slim">Delete</button>'+
                    '</div>'+
                '</div>'+
            '</div>';


        $( '#more_extra_services_main').append( newOption );
        $('.type-select-picker').selectpicker('refresh');
        removeExtraServices();
    });

    var removeExtraServices = function (){

        $( '.remove-extra-services-child').on('click', function( event ){
            event.preventDefault();
            var $this = $( this );
            $this.closest( '.more_extra_services_wrap' ).remove();
        });
    }
    removeExtraServices();

    // Additional Rules
    $('.btn-single-rule').on('click',function(e){
        e.preventDefault();
        var rulesText = $('#listing_rules_add_rule').val();

        if(rulesText != ''){
            $.ajax({
                type: 'post',
                url: homey_child_ajax.ajax_url,
                dataType: 'json',
                data: {
                    'action' : 'generate_listing_rules',
                    'rulesText' : rulesText,
                },
                beforeSend: function() {
                    $('.btn-single-rule').text('Processing...');
                },
                success: function(data) {
                    if(data.success){
                        // alert(data.message);
                        $(".listing-rules-row").append(data.rule_html);
                    }

                    $('.remove-btn-single-rule').on('click',function(e){
                        e.preventDefault();
                        $(this).closest('.single-rules-row').remove();
                    });
                },
                error: function(errorThrown) {},
                complete: function(){
                    $('.btn-single-rule').text('+ Add More');
                    $('#listing_rules_add_rule').val('');
                }
            });
        }else{
            alert('Please enter a rule');
        }
    });

    $('.remove-btn-single-rule').on('click',function(e){
        e.preventDefault();
        $(this).closest('.single-rules-row').remove();
    });

    $('.radio_check_avaialable').on('click',function(e){
        var checkedVal = $(this).val();
        var parent = $(this).closest('.timeperiod-single-day');
        var timeperiod = parent.find('.check_is_available');

        if(checkedVal == 'yes'){
            $(timeperiod.find('input')).attr('required','required');
            timeperiod.show();
        }else{
            $(timeperiod.find('input')).attr('required','');
            timeperiod.hide();
        }
    });

    $("#listing_size").on('change',function(e){
        $('#total-size').text($(this).val());
        $('#size-prefix').text('Sqft');
        $('.sidebar-item-size').show();
    });

    $(".homey_profile_save_child").on('click', function(e) {
        e.preventDefault();

        var $this = $(this);

        var gdpr_agreement;

        // if($('#gdpr_agreement').length > 0 ) {
        //     if(!$('#gdpr_agreement').is(":checked")) {
        //         jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ gdpr_agree_text +'</div>');
        //         $('html,body').animate({
        //             scrollTop: $(".user-dashboard-right").offset().top
        //         }, 'slow');

        //         return false;
        //     } else {
        //         gdpr_agreement = 'checked';
        //     }
        // } 


        var firstname   = $("#firstname").val(),
            lastname    = $("#lastname").val(),
            profile_pic_id  = $("#profile-pic-id").val(),
            useremail    = $("#useremail").val(),
            display_name    = $('select[name="display_name"] option:selected').val(),
            native_language   = $('#native_language').val(),
            other_language       = $("#other_language").val(),
            bio       = $("#bio").val(),
            street_address       = $("#street_address").val(),
            apt_suit       = $("#apt_suit").val(),
            city       = $("#city").val(),
            state       = $("#state").val(),
            zipcode       = $("#zipcode").val(),
            neighborhood       = $("#neighborhood").val(),
            country       = $("#country").val(),

            facebook  = $("#facebook").val(),
            twitter  = $("#twitter").val(),
            linkedin  = $("#linkedin").val(),
            googleplus  = $("#googleplus").val(),
            instagram  = $("#instagram").val(),
            pinterest  = $("#pinterest").val(),
            youtube  = $("#youtube").val(),
            vimeo  = $("#vimeo").val(),
            airbnb  = $("#airbnb").val(),
            trip_advisor  = $("#trip_advisor").val(),

            em_contact_name  = $("#em_contact_name").val(),
            em_relationship  = $("#em_relationship").val(),
            em_email  = $("#em_email").val(),
            em_phone  = $("#em_phone").val(),

            securityprofile = $('#homey_profile_security').val(),
            user_role    = $('select[name="role"] option:selected').val();

        if( firstname.trim().length <= 0 ){
            jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+first_name_req_text+'</div>');

            $('html,body').animate({
                scrollTop: $(".user-dashboard-right").offset().top
            }, 'slow');

            return false;
        }

        if( lastname.trim().length <= 0  && 1==2){
            jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+last_name_req_text+'</div>');

            $('html,body').animate({
                scrollTop: $(".user-dashboard-right").offset().top
            }, 'slow');

            return false;
        }

        if( bio.trim().length <= 0  && 1==2){
            jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+tell_about_req_text+'</div>');

            $('html,body').animate({
                scrollTop: $(".user-dashboard-right").offset().top
            }, 'slow');

            return false;
        }

        // if( em_relationship.trim().length <= 0 && 1==2){
        //     jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+mobile_num_req_text+'</div>');

        //     $('html,body').animate({
        //         scrollTop: $(".user-dashboard-right").offset().top
        //     }, 'slow');

        //     return false;
        // }

        // if( em_phone.trim().length <= 0  && 1==2){
        //     jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+phone_num_req_text+'</div>');

        //     $('html,body').animate({
        //         scrollTop: $(".user-dashboard-right").offset().top
        //     }, 'slow');

        //     return false;
        // }

        $.ajax({
            type: 'POST',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                'action'          : 'homey_save_profile',
                'firstname'       : firstname,
                'profile_pic_id'  : profile_pic_id,
                'lastname'        : lastname,
                'useremail'       : useremail,
                'display_name'    : display_name,
                'role'            : user_role,
                'native_language' : native_language,
                'other_language'  : other_language,
                'bio'             : bio,
                'street_address'  : street_address,
                'apt_suit'        : apt_suit,
                'city'            : city,
                'state'           : state,
                'zipcode'         : zipcode,
                'neighborhood'    : neighborhood,
                'country'         : country,
                'facebook'        : facebook,
                'twitter'         : twitter,
                'linkedin'        : linkedin,
                'googleplus'      : googleplus,
                'instagram'       : instagram,
                'pinterest'       : pinterest,
                'youtube'         : youtube,
                'vimeo'           : vimeo,
                'airbnb'          : airbnb,
                'trip_advisor'    : trip_advisor,
                'em_contact_name' : em_contact_name,
                'em_relationship' : em_relationship,
                'em_email'        : em_email,
                'em_phone'        : em_phone,
                'gdpr_agreement': gdpr_agreement,
                'security'        : securityprofile,
            },
            beforeSend: function( ) {
                $this.children('i').remove();
                $this.prepend('<i class=" '+homey_child_ajax.process_loader_spinner+'"></i>');
            },
            success: function(data) {
                if( data.success ) {
                    jQuery('#profile_message').empty().append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                    $('html,body').animate({
                        scrollTop: $(".user-dashboard-right").offset().top
                    }, 'slow');

                    window.location.reload(true);
                } else {
                    jQuery('#profile_message').empty().append('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+ data.msg +'</div>');
                    $('html,body').animate({
                        scrollTop: $(".user-dashboard-right").offset().top
                    }, 'slow');
                }
            },
            error: function(errorThrown) {

            },
            complete: function(){
                $this.children('i').removeClass(homey_child_ajax.process_loader_spinner);
                $this.children('i').addClass(homey_child_ajax.success_icon);
            }
        });

    });

    // Allow only numbers in number input fields
    $('input[type="number"]').on('keypress', function(e) {
        // Allow only numbers and prevent other characters
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        }
    }); 

    // Prevent paste of non-numeric values
    $('input[type="number"]').on('paste', function(e) {
        var pastedData = e.originalEvent.clipboardData.getData('text');
        if (!/^\d*$/.test(pastedData)) {
            e.preventDefault();
        }
    });
    
    // multiple single prices
    $('.listing-price-per-hour').on('change', function() {
        var price = $(this).val();
        if (price < 0) {
            $(this).val(0);
        }else{
            var guestRange = $(this).attr('name').match(/\[(.*?)\]/)[1];
            $('#'+guestRange+'_guest').prop('checked', true);
        }
        
        // Get all price values and find minimum
        var minPrice = null;
        $('.listing-price-per-hour').each(function() {
            var val = parseInt($(this).val()) || 0;
            if(val > 0) {
                if(minPrice === null || val < minPrice) {
                    minPrice = val;
                }
            }
        });

        $('#day_date_price').val(minPrice);
    });

    // Countdown timer
    var timer = setInterval(function() {
        var countdown = $('#countdown-timer');
        var endTime = parseInt(countdown.data('end')); 
        var now = Math.floor(Date.now() / 1000);
        var timeLeft = endTime - now;
        
        var hours = Math.floor(timeLeft / 3600);
        var minutes = Math.floor((timeLeft % 3600) / 60);
        var seconds = timeLeft % 60;
        
        countdown.find('.counter-timer-hours').text(('0' + hours).slice(-2) + ' hours');
        countdown.find('.counter-timer-minutes').text(('0' + minutes).slice(-2) + ' minutes');
        countdown.find('.counter-timer-seconds').text(('0' + seconds).slice(-2) + ' seconds');

        if(timeLeft <= 0){
            var reservation_id = $('#resrv_id').val();
            $.ajax({
                type: 'post',
                url: homey_child_ajax.ajax_url,
                dataType: 'json',
                data: {
                    'action' : 'expire_reservation_timeout',
                    'reservation_id' : reservation_id,
                },
                success: function(data) {
                    if(data.success){
                        window.location.reload();
                    }
                },
                error: function(errorThrown) {},
                complete: function(){
                    $('.btn-single-rule').text('+ Add More');
                    $('#listing_rules_add_rule').val('');
                }
            });
        }

    }, 1000);

    $('#overtime_hours, #price_per_hour').on('change', function(){
        if($('#overtime_hours').val() > 0 && $('#price_per_hour').val() > 0){
            var overtime_hours = $('#overtime_hours').val();
            var price_per_hour = $('#price_per_hour').val();
            var total_amount = overtime_hours * price_per_hour;
            $('#overtime_total_amount').text(total_amount);
        }else{
            $('#overtime_total_amount').text('0');
        }
    });
    

    $('.send_overtime_policy').on('click', function(e){
        e.preventDefault();

        var overtime_hours = $('#overtime_hours').val();
        var price_per_hour = $('#price_per_hour').val();
        var thread_id = $('#thread_id').val();
        var start_thread_message_form_ajax = $('#start_thread_message_form_ajax').val();

        if(!start_thread_message_form_ajax){
            alert('Invalid nonce');
            return false;
        }

        if(!overtime_hours || !price_per_hour){
            alert('Please fill all fields');
            return false;
        }

        $.ajax({
            type: 'post',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                'action' : 'send_overtime_policy',
                'overtime_hours' : overtime_hours,
                'price_per_hour' : price_per_hour,
                'thread_id' : thread_id,
                'start_thread_message_form_ajax' : start_thread_message_form_ajax,
            },
            success: function(data) {
                // var data = JSON.parse(data);
                if(data.success){
                    $('#overtimeModal').modal('hide');
                    alert(data.msg);
                    window.location.reload();
                }
            },
        });
    });

    // Reject overtime message
    $('.reject-overtime-message').on('click', function(e){
        e.preventDefault();
        var message_id = $(this).data('message-id');
        var thread_id = $(this).data('thread-id');
        var start_thread_message_form_ajax = $('#start_thread_message_form_ajax').val();

        if(!start_thread_message_form_ajax){
            alert('Invalid nonce');
            return false;
        }

        $.ajax({
            type: 'post',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                'action' : 'reject_overtime_message',
                'message_id' : message_id,
                'thread_id' : thread_id,
                'start_thread_message_form_ajax' : start_thread_message_form_ajax,
            },
            success: function(data) {
                if(data.success){
                    alert(data.msg);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.msg);
                }
            },
        });
        
    });

    // Approve overtime message
    $('.approve-overtime-message').on('click', function(e){
        e.preventDefault();
        var message_id = $(this).data('message-id');
        var thread_id = $(this).data('thread-id');
        var start_thread_message_form_ajax = $('#start_thread_message_form_ajax').val();

        if(!start_thread_message_form_ajax){
            alert('Invalid nonce');
            return false;
        }

        $.ajax({
            type: 'post',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                'action' : 'approve_overtime_message',
                'message_id' : message_id,
                'thread_id' : thread_id,
                'start_thread_message_form_ajax' : start_thread_message_form_ajax,
            },
            success: function(data) {
                if(data.success){
                    alert(data.msg);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.msg);
                }
            },
        });

    });

    // $("textarea#message").on('input', function(){
    //     // Remove any numbers from the text
    //     var text = $(this).val();
    //     text = text.replace(/[0-9]/g, '');
    //     $(this).val(text);
    // });

    // ============================================================
    // Utility: Show inline modal message
    // ============================================================
    function hcShowModalMessage(targetSelector, type, message) {
        $(targetSelector).html(
            '<div class="alert alert-' + type + ' alert-dismissible hc-alert" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button>' + message + '</div>'
        );
        $(targetSelector).get(0) && $(targetSelector)[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hcSetBtnLoading(btn, loading) {
        if (loading) {
            btn.find('.hc-btn-text').hide();
            btn.find('.hc-btn-loading').show();
            btn.prop('disabled', true);
        } else {
            btn.find('.hc-btn-text').show();
            btn.find('.hc-btn-loading').hide();
            btn.prop('disabled', false);
        }
    }

    // ============================================================
    // SUBMIT A REVIEW — Open Modal
    // ============================================================
    $(document).on('click', '.submit-a-review-btn', function (e) {
        e.preventDefault();
        var reservationId = $(this).data('reservation_id');
        // Reset form
        $('#submit-review-form')[0].reset();
        $('#hc-review-form-message').html('');
        $('#hc-rating-text').text('').removeClass('hc-rating-set');
        $('#review_reservation_id').val(reservationId);
        $('#hc-review-char-count').text('0 / 1000');
        $('#submitReviewModal').modal('show');
    });

    // Star rating — interactive highlight
    $(document).on('change', 'input[name="rating"]', function () {
        var val = parseInt($(this).val());
        var labels = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        $('#hc-rating-text').text(labels[val - 1] || '').addClass('hc-rating-set');
    });

    // Character counter for review textarea
    $(document).on('input', '#hc_review_content', function () {
        var len = $(this).val().length;
        $('#hc-review-char-count').text(len + ' / 1000');
        if (len > 1000) $(this).val($(this).val().substring(0, 1000));
    });

    // ============================================================
    // SUBMIT A REVIEW — Submit Form via AJAX
    // ============================================================
    $(document).on('click', '.submit-review-ajax-btn', function (e) {
        e.preventDefault();
        var $btn          = $(this);
        var form          = $('#submit-review-form');
        var rating        = form.find('input[name="rating"]:checked').val();
        var reviewContent = form.find('textarea[name="review_content"]').val().trim();
        var reservationId = form.find('input[name="reservation_id"]').val();
        var security      = form.find('input[name="security"]').val();

        // Client-side validation
        if (!rating) {
            hcShowModalMessage('#hc-review-form-message', 'danger', 'Please select a star rating before submitting.');
            return;
        }
        if (reviewContent.length < 10) {
            hcShowModalMessage('#hc-review-form-message', 'danger', 'Please write at least 10 characters for your review.');
            return;
        }
        if (reviewContent.length > 1000) {
            hcShowModalMessage('#hc-review-form-message', 'danger', 'Review must not exceed 1000 characters.');
            return;
        }

        hcSetBtnLoading($btn, true);

        $.ajax({
            type: 'POST',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                action:         'homey_child_submit_review',
                reservation_id: reservationId,
                rating:         rating,
                review_content: reviewContent,
                security:       security,
            },
            success: function (data) {
                if (data.success) {
                    hcShowModalMessage('#hc-review-form-message', 'success', data.data.msg || 'Review submitted successfully!');
                    form[0].reset();
                    $('#hc-rating-text').text('');
                    setTimeout(function () {
                        $('#submitReviewModal').modal('hide');
                    }, 2500);
                } else {
                    hcShowModalMessage('#hc-review-form-message', 'danger', (data.data && data.data.msg) ? data.data.msg : 'An error occurred. Please try again.');
                }
            },
            error: function () {
                hcShowModalMessage('#hc-review-form-message', 'danger', 'A network error occurred. Please check your connection and try again.');
            },
            complete: function () {
                hcSetBtnLoading($btn, false);
            }
        });
    });

    // ============================================================
    // REPORT A PROBLEM — Open Modal
    // ============================================================
    $(document).on('click', '.report-a-problem-btn', function (e) {
        e.preventDefault();
        var reservationId = $(this).data('reservation_id');
        // Reset form
        $('#report-problem-form')[0].reset();
        $('#hc-report-form-message').html('');
        $('#report_reservation_id').val(reservationId);
        $('#hc-desc-char-count').text('0 / 2000');
        $('#reportProblemModal').modal('show');
    });

    // Character counter for description textarea
    $(document).on('input', '#hc_report_description', function () {
        var len = $(this).val().length;
        $('#hc-desc-char-count').text(len + ' / 2000');
        if (len > 2000) $(this).val($(this).val().substring(0, 2000));
    });

    // ============================================================
    // REPORT A PROBLEM — Submit Form via AJAX
    // ============================================================
    $(document).on('click', '.submit-report-ajax-btn', function (e) {
        e.preventDefault();
        var $btn         = $(this);
        var form         = $('#report-problem-form');
        var userRole     = form.find('input[name="user_role"]:checked').val();
        var issueType    = form.find('input[name="issue_type"]:checked').val();
        var description  = form.find('textarea[name="description"]').val().trim();
        var damageList   = form.find('input[name="damage_list"]').val().trim();
        var damageAmount = form.find('input[name="damage_amount"]').val().trim();
        var acknowledged = form.find('input[name="acknowledgment"]').is(':checked');
        var reservationId = form.find('input[name="reservation_id"]').val();
        var security     = form.find('input[name="security"]').val();

        // Client-side validation
        if (!userRole) {
            hcShowModalMessage('#hc-report-form-message', 'danger', 'Please select whether you are a Host or Renter.');
            return;
        }
        if (!issueType) {
            hcShowModalMessage('#hc-report-form-message', 'danger', 'Please select the type of issue you are reporting.');
            return;
        }
        if (description.length < 10) {
            hcShowModalMessage('#hc-report-form-message', 'danger', 'Please provide a detailed description (at least 10 characters).');
            return;
        }
        if (!acknowledged) {
            hcShowModalMessage('#hc-report-form-message', 'danger', 'You must check the acknowledgment box to submit this report.');
            return;
        }

        hcSetBtnLoading($btn, true);

        $.ajax({
            type: 'POST',
            url: homey_child_ajax.ajax_url,
            dataType: 'json',
            data: {
                action:        'homey_child_report_problem',
                reservation_id: reservationId,
                user_role:     userRole,
                issue_type:    issueType,
                description:   description,
                damage_list:   damageList,
                damage_amount: damageAmount,
                acknowledgment: acknowledged ? '1' : '0',
                security:      security,
            },
            success: function (data) {
                if (data.success) {
                    hcShowModalMessage('#hc-report-form-message', 'success', data.data.msg || 'Report submitted successfully!');
                    form[0].reset();
                    setTimeout(function () {
                        $('#reportProblemModal').modal('hide');
                    }, 3000);
                } else {
                    hcShowModalMessage('#hc-report-form-message', 'danger', (data.data && data.data.msg) ? data.data.msg : 'An error occurred. Please try again.');
                }
            },
            error: function () {
                hcShowModalMessage('#hc-report-form-message', 'danger', 'A network error occurred. Please check your connection and try again.');
            },
            complete: function () {
                hcSetBtnLoading($btn, false);
            }
        });
    });

    // Clear modal messages when modals close
    $('#submitReviewModal, #reportProblemModal').on('hidden.bs.modal', function () {
        $(this).find('.hc-alert').remove();
    });

});