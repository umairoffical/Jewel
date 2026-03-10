jQuery(document).ready(function ($) {
    "use strict";

    if ( typeof HOMEY_ajax_vars !== "undefined" ) {
        var ajaxurl = HOMEY_ajax_vars.admin_url+ 'admin-ajax.php';
        var login_redirect_type = HOMEY_ajax_vars.redirect_type;
        var login_redirect = HOMEY_ajax_vars.login_redirect;
        var is_singular_listing = HOMEY_ajax_vars.is_singular_listing;
        var is_singular_experience = HOMEY_ajax_vars.is_singular_experience;
        var paypal_connecting = HOMEY_ajax_vars.paypal_connecting;
        var login_sending = HOMEY_ajax_vars.login_loading;
        var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
        var currency_updating_msg = HOMEY_ajax_vars.currency_updating_msg;
        var homey_date_format = HOMEY_ajax_vars.homey_date_format;
        var userID = HOMEY_ajax_vars.user_id;
        var homey_reCaptcha = HOMEY_ajax_vars.homey_reCaptcha;
        var processing_text = HOMEY_ajax_vars.processing_text;
        var already_login_text = HOMEY_ajax_vars.already_login_text;
        var is_listing_detail = HOMEY_ajax_vars.is_listing_detail;
        var booked_hours_array = HOMEY_ajax_vars.booked_hours_array;
        var pending_hours_array = HOMEY_ajax_vars.pending_hours_array;
        var booking_start_hour = HOMEY_ajax_vars.booking_start_hour;
        var booking_end_hour = HOMEY_ajax_vars.booking_end_hour;
        var homey_min_book_days = HOMEY_ajax_vars.homey_min_book_days;

        var current_month = 1;
        var current_month2 = 2;

        if( booked_hours_array !=='' && booked_hours_array !== undefined && booked_hours_array.length !== 0 ) {
            booked_hours_array   = JSON.parse (booked_hours_array);
        }

        if( pending_hours_array !=='' && pending_hours_array !== undefined && pending_hours_array.length !== 0 ) {
            pending_hours_array   = JSON.parse (pending_hours_array);
        }

        var is_tansparent = HOMEY_ajax_vars.homey_tansparent;
        var retina_logo = HOMEY_ajax_vars.retina_logo;
        var retina_logo_splash = HOMEY_ajax_vars.retina_logo_splash;
        var retina_logo_mobile = HOMEY_ajax_vars.retina_logo_mobile;
        var retina_logo_mobile_splash = HOMEY_ajax_vars.retina_logo_mobile_splash;
        var no_more_listings = HOMEY_ajax_vars.no_more_listings;
        var no_more_experiences = HOMEY_ajax_vars.no_more_experiences;
        var allow_additional_guests = HOMEY_ajax_vars.allow_additional_guests;
        var allowed_guests_num = HOMEY_ajax_vars.allowed_guests_num;
        var num_additional_guests = HOMEY_ajax_vars.num_additional_guests;
        var agree_term_text = HOMEY_ajax_vars.agree_term_text;
        var already_booked_text = HOMEY_ajax_vars.already_booked_text;
        var choose_gateway_text = HOMEY_ajax_vars.choose_gateway_text;
        var success_icon = HOMEY_ajax_vars.success_icon;
        var calendar_link = HOMEY_ajax_vars.calendar_link;
        var exp_calendar_link = HOMEY_ajax_vars.exp_calendar_link;
        var focusedInput_2 = null;

        var homey_calendar_months = HOMEY_ajax_vars.homey_calendar_months;

        var allowed_guests_plus_additional = parseInt(allowed_guests_num) + parseInt(num_additional_guests);

        var compare_url = HOMEY_ajax_vars.compare_url;
        var add_compare = HOMEY_ajax_vars.add_compare;
        var remove_compare = HOMEY_ajax_vars.remove_compare;
        var compare_limit = HOMEY_ajax_vars.compare_limit;
        // var homey_booking_type = HOMEY_ajax_vars.homey_booking_type;
        var homey_booking_type = 'per_hour';

        var homey_is_rtl = HOMEY_ajax_vars.homey_is_rtl;

        if( homey_is_rtl == 'yes' ) {
            homey_is_rtl = true;
        } else {
            homey_is_rtl = false;
        }

        var homey_is_mobile = false;
        if (/Android|webOS|iPhone|iPad|iPod|tablet|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            homey_is_mobile = true;
        }

        var homey_window_width = $( window ).width();

        /*var homey_timeStamp_2 = function(str) {
         return new Date(str.replace(/^(\d{2}\-)(\d{2}\-)(\d{4})$/,
           '$2$1$3')).getTime();
       };*/

        var homey_processing_modal = function ( msg ) {
            var process_modal ='<div class="modal fade" id="homey_modal" tabindex="-1" role="dialog" aria-labelledby="homeyModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">'+msg+'</div></div></div></div></div>';
            $('body').append(process_modal);
            $('#homey_modal').modal();
        }

        var homey_processing_modal_close = function ( ) {
            $('#homey_modal').modal('hide');
        }

        var homey_timeStamp_2 = function(str) {
            var myDate=str.split("-");
            var newDate=myDate[1]+"/"+myDate[0]+"/"+myDate[2];
            return new Date(newDate).getTime();
        };
        /*--------------------------------------------------------------------------
         *   Retina Logo
         * -------------------------------------------------------------------------*/
        if (window.devicePixelRatio == 2) {

            if(is_tansparent) {
                if(retina_logo_splash != '') {
                    $(".transparent-header .homey_logo img").attr("src", retina_logo_splash);
                }

                if(retina_logo_mobile_splash != '') {
                    $(".mobile-logo img").attr("src", retina_logo_mobile_splash);
                }

            } else {
                if(retina_logo != '') {
                    $(".homey_logo img").attr("src", retina_logo);
                }

                if(retina_logo_mobile != '') {
                    $(".mobile-logo img").attr("src", retina_logo_mobile);
                }
            }
        }

        /*--------------------------------------------------------------------------
         *  Currency Switcher
         * -------------------------------------------------------------------------*/
        var currencySwitcherList = $('#homey-currency-switcher-list');
        if( currencySwitcherList.length > 0 ) {

            $('#homey-currency-switcher-list > li').on('click', function(e) {
                e.stopPropagation();
                currencySwitcherList.slideUp( 200 );

                var selectedCurrencyCode = $(this).data( 'currency-code' );

                if ( selectedCurrencyCode ) {

                    $('.homey-selected-currency span').html( selectedCurrencyCode );
                    homey_processing_modal('<i class="'+process_loader_spinner+'"></i> '+currency_updating_msg);

                    $.ajax({
                        url: ajaxurl,
                        dataType: 'JSON',
                        method: 'POST',
                        data: {
                            'action' : 'homey_currency_converter',
                            'currency_to_converter' : selectedCurrencyCode,
                        },
                        success: function (res) {
                            if( res.success ) {
                                var wp_http_referer_value = jQuery(document).find("input[name='_wp_http_referer']").val();
                                if(typeof wp_http_referer_value !== "undefined") {
                                    if(wp_http_referer_value.indexOf("?") !== -1){
                                        window.location.href = wp_http_referer_value + '&n=' + new Date().getTime();
                                    }else{
                                        window.location.href = wp_http_referer_value + '?n=' + new Date().getTime();
                                    }
                                }else{
                                    window.location.reload();
                                }
                            } else {
                                alert( "Check your server settings: " +res.msg );
                                window.location.reload();
                            }
                        },
                        error: function (xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        }
                    });

                }

            });
        }

        $('.homey-currency-switcher').on('change', function(e) {

            var selectedCurrencyCode = $(this).val();

            if ( selectedCurrencyCode ) {

                homey_processing_modal('<i class="'+process_loader_spinner+'"></i> '+currency_updating_msg);

                $.ajax({
                    url: ajaxurl,
                    dataType: 'JSON',
                    method: 'POST',
                    data: {
                        'action' : 'homey_currency_converter',
                        'currency_to_converter' : selectedCurrencyCode,
                    },
                    success: function (res) {
                        if( res.success ) {
                            window.location.reload();
                        } else {
                            console.log( res );
                        }
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }
                });

            }

        });

        /*--------------------------------------------------------------------------
         *  Module Ajax Pagination
         * -------------------------------------------------------------------------*/
        var listings_module_section = $('#listings_module_section');
        if( listings_module_section.length > 0 ) {

            $("body").on('click', '.homey-loadmore a', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $wrap = $this.closest('#listings_module_section').find('#module_listings');

                var limit = $this.data('limit');
                var paged = $this.data('paged');
                var style = $this.data('style');
                var type = $this.data('type');
                var roomtype = $this.data('roomtype');
                var country = $this.data('country');
                var state = $this.data('state');
                var city = $this.data('city');
                var area = $this.data('area');
                var featured = $this.data('featured');
                var offset = $this.data('offset');
                var sortby = $this.data('sortby');
                var booking_type = $this.data('booking_type');
                var author = $this.data('author');
                var authorid = $this.data('authorid');

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_loadmore_listings',
                        'limit': limit,
                        'paged': paged,
                        'style': style,
                        'type': type,
                        'roomtype': roomtype,
                        'country': country,
                        'state': state,
                        'city': city,
                        'area': area,
                        'featured': featured,
                        'sort_by': sortby,
                        'offset': offset,
                        'booking_type': booking_type,
                        'author': author,
                        'authorid': authorid,
                    },
                    beforeSend: function( ) {
                        $this.find('i').css('display', 'inline-block');
                    },
                    success: function (data) {
                        if(data == 'no_result') {
                            $this.closest('#listings_module_section').find('.homey-loadmore').text(no_more_listings);
                            return;
                        }
                        $wrap.append(data);
                        $this.data("paged", paged+1);
                        var next_paged = $this.data("paged");
                        var current_offset = $this.data("limit");
                        $this.data("offset", current_offset * (next_paged - 1));

                        homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
                        homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);
                        compare_for_ajax();

                    },
                    complete: function(){
                        $this.find('i').css('display', 'none');
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });

            });
        }

        var listings_module_section_taxonomy = $('#listings_module_section_taxonomy');
        if( listings_module_section_taxonomy.length > 0 ) {

            $("body").on('click', '.homey-loadmore a', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $wrap = $this.closest('#listings_module_section_taxonomy').find('#module_listings_div');

                var limit = $this.data('limit');
                var paged = $this.data('paged');
                var style = $this.data('style');
                var type = $this.data('type');
                var roomtype = $this.data('roomtype');
                var country = $this.data('country');
                var state = $this.data('state');
                var city = $this.data('city');
                var area = $this.data('area');
                var featured = $this.data('featured');
                var offset = $this.data('offset');
                var sortby = $this.data('sortby');
                var booking_type = $this.data('booking_type');
                var author = $this.data('author');
                var authorid = $this.data('authorid');

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_loadmore_listings',
                        'limit': limit,
                        'paged': paged,
                        'style': style,
                        'type': type,
                        'roomtype': roomtype,
                        'country': country,
                        'state': state,
                        'city': city,
                        'area': area,
                        'featured': featured,
                        'sort_by': sortby,
                        'offset': offset,
                        'booking_type': booking_type,
                        'author': author,
                        'authorid': authorid,
                    },
                    beforeSend: function( ) {
                        $this.find('i').css('display', 'inline-block');
                    },
                    success: function (data) {
                        if(data == 'no_result') {
                            $this.closest('#listings_module_section_taxonomy').find('.homey-loadmore').text(no_more_listings);
                            return;
                        }
                        $wrap.append(data);
                        $this.data("paged", paged+1);
                        var next_paged = $this.data("paged");
                        var current_offset = $this.data("limit");
                        $this.data("offset", current_offset * (next_paged - 1));

                        homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
                        homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);
                        compare_for_ajax();

                    },
                    complete: function(){
                        $this.find('i').css('display', 'none');
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });

            });
        }

        //experiences
        var experiences_module_section = $('#experiences_module_section');
        if( experiences_module_section.length > 0 ) {

            $("body").on('click', '.homey-loadmore a', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $wrap = $this.closest('#experiences_module_section').find('#module_experiences');

                var limit = $this.data('limit');
                var paged = $this.data('paged');
                var style = $this.data('style');
                var type = $this.data('type');
                var roomtype = $this.data('roomtype');
                var country = $this.data('country');
                var state = $this.data('state');
                var city = $this.data('city');
                var area = $this.data('area');
                var featured = $this.data('featured');
                var offset = $this.data('offset');
                var sortby = $this.data('sortby');
                var booking_type = $this.data('booking_type');
                var author = $this.data('author');
                var authorid = $this.data('authorid');

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_loadmore_experiences',
                        'limit': limit,
                        'paged': paged,
                        'style': style,
                        'type': type,
                        'roomtype': roomtype,
                        'country': country,
                        'state': state,
                        'city': city,
                        'area': area,
                        'featured': featured,
                        'sort_by': sortby,
                        'offset': offset,
                        'booking_type': booking_type,
                        'author': author,
                        'authorid': authorid,
                    },
                    beforeSend: function( ) {
                        $this.find('i').css('display', 'inline-block');
                    },
                    success: function (data) {
                        if(data == 'no_result') {
                            $this.closest('#experiences_module_section').find('.homey-loadmore').text(no_more_experiences);
                            return;
                        }
                        $wrap.append(data);
                        $this.data("paged", paged+1);
                        var next_paged = $this.data("paged");
                        var current_offset = $this.data("limit");
                        $this.data("offset", current_offset * (next_paged - 1) );

                        homey_init_add_exp_favorite(ajaxurl, userID, is_singular_experience);
                        homey_init_remove_exp_favorite(ajaxurl, userID, is_singular_experience);
                        compare_exp_for_ajax();

                    },
                    complete: function(){
                        $this.find('i').css('display', 'none');
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });

            });
        }

        //end of experiences
        /*--------------------------------------------------------------------------
         *   Add or remove favorites
         * -------------------------------------------------------------------------*/

        homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
        homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);

        //experiences
        homey_init_add_exp_favorite(ajaxurl, userID, is_singular_experience);
        homey_init_remove_exp_favorite(ajaxurl, userID, is_singular_experience);
        //end of experiences

        /*--------------------------------------------------------------------------
         *   Compare for ajax
         * -------------------------------------------------------------------------*/
        var compare_for_ajax = function() {
            var listings_compare = homeyGetCookie('homey_compare_listings');
            var limit_item_compare = 4;
            add_to_compare(compare_url, add_compare, remove_compare, compare_limit, listings_compare, limit_item_compare );
            remove_from_compare(listings_compare, add_compare, remove_compare);
        }

        //experiences
        var compare_exp_for_ajax = function() {
            var experiences_compare = homeyGetCookie('homey_compare_experiences');
            var limit_item_compare = 4;
            add_to_compare_exp(compare_url, add_compare, remove_compare, compare_limit, experiences_compare, limit_item_compare );
            remove_from_compare_exp(experiences_compare, add_compare, remove_compare);
        }
        //experiences

        /* ------------------------------------------------------------------------ */
        /*  Paypal single listing payment
         /* ------------------------------------------------------------------------ */
        $('#homey_complete_order').on('click', function(e) {
            e.preventDefault();
            var hform, payment_gateway, listing_id, is_upgrade;

            payment_gateway = $("input[name='homey_payment_type']:checked").val();
            is_upgrade = $("input[name='is_upgrade']").val();

            listing_id = $('#listing_id').val();

            if( payment_gateway == 'paypal' ) {
                homey_processing_modal( paypal_connecting );
                homey_paypal_payment( listing_id, is_upgrade);

            } else if ( payment_gateway == 'stripe' ) {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
            }
            return;

        });

        //experiences
        $('#homey_complete_order_exp').on('click', function(e) {
            e.preventDefault();
            var hform, payment_gateway, experience_id, is_upgrade;

            payment_gateway = $("input[name='homey_payment_type']:checked").val();
            is_upgrade = $("input[name='is_upgrade']").val();

            experience_id = $('#experience_id').val();

            if( payment_gateway == 'paypal' ) {
                homey_processing_modal( paypal_connecting );
                homey_paypal_exp_payment( experience_id, is_upgrade);

            } else if ( payment_gateway == 'stripe' ) {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
            }
            return;

        });
        //experiences

        /* ------------------------------------------------------------------------ */
        /*  Paypal payment function
         /* ------------------------------------------------------------------------ */
        var homey_paypal_payment = function( listing_id, is_upgrade ) {

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_listing_paypal_payment',
                    'listing_id': listing_id,
                    'is_upgrade': is_upgrade,
                },
                success: function( response ) {
                    window.location.href = response;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        //experiences
        var homey_paypal_exp_payment = function( experience_id, is_upgrade ) {

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_experience_paypal_payment',
                    'experience_id': experience_id,
                    'is_upgrade': is_upgrade,
                },
                success: function( response ) {
                    window.location.href = response;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }
        //experiences

        if($('#add_review').length > 0) {
            $('#add_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                var rating = $('#rating').val();
                var review_action = $('#review_action').val();
                var review_content = $('#review_content').val();
                var review_reservation_id = $('#review_reservation_id').val();
                var security = $('#review-security').val();
                var parentDIV = $this.parents('.user-dashboard-right');

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_reservation_id': review_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });

            });
        }

        if($('#add_guest_review').length > 0) {
            $('#add_guest_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                var rating = $('#rating').val();
                var review_action = $('#review_guest_action').val();
                var review_content = $('#review_content').val();
                var review_guest_reservation_id = $('#review_guest_reservation_id').val();
                var security = $('#review-security').val();
                var parentDIV = $this.parents('.user-dashboard-right');

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_guest_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_guest_reservation_id': review_guest_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });

            });
        }

        var listing_review_ajax = function(sortby, listing_id, paged) {
            var review_container = $('#homey_reviews');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_ajax_review',
                    'sortby': sortby,
                    'listing_id': listing_id,
                    'paged': paged
                },
                beforeSend: function( ) {

                },
                success: function(data) {
                    review_container.empty();
                    review_container.html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){

                }

            });
        }

        if($('#sort_review').length > 0) {
            $('#sort_review').on('change', function() {
                var sortby = $(this).val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                listing_review_ajax(sortby, listing_id, paged);
                return;
            });
        }

        if($('#review_next').length > 0) {
            $('#review_next').on('click', function(e) {
                e.preventDefault();
                $('#review_prev').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var total_pages = $('#total_pages').val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)+1;
                $('#review_paged').val(paged);

                if(paged == total_pages) {
                    $(this).attr('disabled', true);
                }
                listing_review_ajax(sortby, listing_id, paged);
                return;
            });
        }

        if($('#review_prev').length > 0) {
            $('#review_prev').on('click', function(e) {
                e.preventDefault();
                $('#review_next').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)-1;
                $('#review_paged').val(paged);
                if(paged <= 1) {
                    $(this).attr('disabled', true);
                }
                listing_review_ajax(sortby, listing_id, paged);
                return;
            });
        }

        // Reviews js function for experiences
        if($('#add_exp_review').length > 0) {
            $('#add_exp_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                var rating = $('#rating').val();
                var review_action = $('#review_action').val();
                var review_content = $('#review_content').val();
                var review_reservation_id = $('#review_reservation_id').val();
                var security = $('#review-security').val();
                var parentDIV = $this.parents('.user-dashboard-right');

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_exp_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_reservation_id': review_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });

            });
        }

        if($('#add_guest_exp_review').length > 0) {
            $('#add_guest_exp_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                var rating = $('#rating').val();
                var review_action = $('#review_guest_action').val();
                var review_content = $('#review_content').val();
                var review_guest_reservation_id = $('#review_guest_reservation_id').val();
                var security = $('#review-security').val();
                var parentDIV = $this.parents('.user-dashboard-right');

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_guest_exp_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_guest_reservation_id': review_guest_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }
                });
            });
        }

        var experience_review_ajax = function(sortby, experience_id, paged) {
            var review_container = $('#homey_reviews');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_ajax_exp_review',
                    'sortby': sortby,
                    'experience_id': experience_id,
                    'paged': paged
                },
                beforeSend: function( ) {

                },
                success: function(data) {
                    review_container.empty();
                    review_container.html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                }
            });
        }

        if($('#sort_exp_review').length > 0) {
            $('#sort_exp_review').on('change', function() {
                var sortby = $(this).val();
                var experience_id = $('#review_experience_id').val();
                var paged = $('#review_paged').val();
                experience_review_ajax(sortby, experience_id, paged);
                return;
            });
        }

        if($('#review_exp_next').length > 0) {
            $('#review_exp_next').on('click', function(e) {
                e.preventDefault();
                $('#review_prev').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var total_pages = $('#total_pages').val();
                var experience_id = $('#review_experience_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)+1;
                $('#review_paged').val(paged);

                if(paged == total_pages) {
                    $(this).attr('disabled', true);
                }
                experience_review_ajax(sortby, listing_id, paged);
                return;
            });
        }

        if($('#review_exp_prev').length > 0) {
            $('#review_exp_prev').on('click', function(e) {
                e.preventDefault();
                $('#review_next').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var experience_id = $('#review_experience_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)-1;
                $('#review_paged').val(paged);
                if(paged <= 1) {
                    $(this).attr('disabled', true);
                }
                experience_review_ajax(sortby, experience_id, paged);
                return;
            });
        }
        // End of Reviews js function for experiences

        /* ------------------------------------------------------------------------ */
        /* Set date format
        /* ------------------------------------------------------------------------ */
        var homey_convert_date = function(date) {

            if(date == '') {
                return '';
            }

            var d_format='';
            var return_date='';

            // Check if homey_date_format is defined before using it
            if (typeof homey_date_format !== 'undefined') {
                d_format = homey_date_format.toUpperCase();
            } else {
                d_format = 'YYYY-MM-DD'; // Default format if homey_date_format is undefined
            }

            var changed_date_format = d_format.replace("YY", "YYYY");
            var return_date = moment(date, changed_date_format).format('YYYY-MM-DD');

            return return_date;

        }

        var homey_calculate_booking_cost = function(booking_dates, total_hours, guests, listing_id, security, extra_options) {
            var $this = $(this);
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_calculate_booking_cost',
                    'booking_dates' : booking_dates,
                    'total_hours' : total_hours,
                    'guests': guests,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    console.log(data);
                    var response = typeof data === 'string' ? JSON.parse(data) : data;
                    if( response.success ) {

                        if(notify.find('.notify').length > 1) {
                            notify.find('.notify').first().remove();
                        }
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+response.message+'</div>');

                        $('#homey_booking_cost, .payment-list').empty().html(response.output);

                    } else {
                        if(notify.find('.notify').length > 1) {
                            notify.find('.notify').first().remove();
                        }
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+response.message+'</div>');
                    }

                    // $('#homey_booking_cost, .payment-list').empty().html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var homey_calculate_exp_booking_cost = function(check_in_date, guests, experience_id, security, extra_options) {
            var $this = $(this);
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            if(check_in_date === '') {
                $('#homey_booking_cost, .payment-list').empty();
                return;
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_calculate_exp_booking_cost',
                    'check_in_date': check_in_date,
                    'guests': guests,
                    'extra_options': extra_options,
                    'experience_id': experience_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    $('#homey_booking_cost, .payment-list').empty().html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var homey_calculate_hourly_booking_cost = function(check_in_date, start_hour, end_hour, guests, listing_id, security, extra_options) {
            var $this = $(this);
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            if(check_in_date === '' || start_hour === '' || end_hour === '') {
                $('#homey_booking_cost, .payment-list').empty();
                return;
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_calculate_hourly_booking_cost',
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    $('#homey_booking_cost, .payment-list').empty().html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var check_booking_availability_on_date_change = function(check_in_date, check_out_date, total_hours, listing_id, security) {
            var $this = $(this);

            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $('.homey_extra_price input').each(function(){
                $(this).prop("checked", false);
            });

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'check_booking_availability_on_date_change',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'total_hours' : total_hours,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    if( data.success ) {
                        $('#request_for_reservation, #request_for_reservation_mobile').removeAttr("disabled");
                        $('#instance_reservation, #instance_reservation_mobile').removeAttr("disabled");
                        if(notify.find('.notify').length > 1) {
                            notify.find('.notify').first().remove();
                        }
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        $('.single-total-hours').show();
                        $('.total-hours-value').text(data.total_hours);
                    } else {
                        if(notify.find('.notify').length > 1) {
                            notify.find('.notify').first().remove();
                        }
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                        $('#request_for_reservation, #request_for_reservation_mobile').attr("disabled", true);
                        $('#instance_reservation, #instance_reservation_mobile').attr("disabled", true);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var check_exp_availability_on_date_change = function(check_in_date, experience_id, security) {
            var $this = $(this);

            var exp_guests = $("input[name='exp_guests']").val();

            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $('.homey_exp_extra_price input').each(function(){
                $(this).prop("checked", false);
            });

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'check_exp_availability_on_date_change',
                    'check_in_date': check_in_date,
                    'experience_id': experience_id,
                    'exp_guests': exp_guests,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    if( data.success ) {
                        $('#request_for_reservation, #request_for_reservation_mobile').removeAttr("disabled");
                        $('#instance_reservation, #instance_reservation_mobile').removeAttr("disabled");
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                        $('#request_for_reservation, #request_for_reservation_mobile').attr("disabled", true);
                        $('#instance_reservation, #instance_reservation_mobile').attr("disabled", true);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var check_booking_availability_on_hour_change = function(check_in_date, start_hour, end_hour, listing_id, security) {
            var $this = $(this);

            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $('.homey_extra_price input').each(function(){
                $(this).prop("checked", false);
            });
            var visitortimezone_gmt = $("#visitortimezone").val();
            var prettyDateTime = $("#prettyDateTime").val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'check_booking_availability_on_hour_change',
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'listing_id': listing_id,
                    'security': security,
                    'visitortimezone_gmt': visitortimezone_gmt,
                    'prettyDateTime': prettyDateTime
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    if( data.success ) {
                        $('#request_hourly_reservation, #request_hourly_reservation_mobile').removeAttr("disabled");
                        $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').removeAttr("disabled");
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                        $('#request_hourly_reservation, #request_hourly_reservation_mobile').attr("disabled", true);
                        $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').attr("disabled", true);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        // Single listing booking form
        $("#single-listing-date-range input").on('focus', function() {
            $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').css("display", "block");
            $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').addClass("arrive_active");
            $(this).closest('.single-date-hour').find('.single-form-guests-js').css("display", "none");
            focusedInput_2 = $(this).attr('name');
            $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').removeClass('arrive_active depart_active').addClass(focusedInput_2+'_active');
        });

        $(".single-guests-js input").on('focus', function() {
            $(this).prev("label").css("display", "block");
            $(this).addClass("on-focus");
            $('.single-form-guests-js').css("display", "block");
        });

        $(".exp-single-guests-js input").on('focus', function() {
            $(this).prev("label").css("display", "block");
            $(this).addClass("on-focus");
            $('.exp-single-form-guests-js').css("display", "block");
        });

        var numClicks = 0;
        var fromTimestamp_2, toTimestamp_2 = 0; // init start and end timestamps

        var homey_booking_dates = function() {

            $('.single-listing-booking-calendar-js ul li').on('click', function() {
                console.log(' ln 1139');
                var $this = $(this);
                $this.show();
                if($this.hasClass('past-day') || $this.hasClass('homey-not-available-for-booking')) {
                    if(!$this.hasClass('reservation_start')) {
                        return false;
                    }
                }

                numClicks += 1;
                var vl = $this.data('formatted-date');
                var timestamp = $this.data('timestamp');

                // if modify days after selecting once
                if (focusedInput_2 == 'depart' && timestamp > fromTimestamp_2) {

                    $('.single-listing-calendar-wrap ul').find('li.to-day').removeClass('selected')
                        .siblings().removeClass('to-day in-between');

                    numClicks = 2;
                }

                if( numClicks == 1 ) {
                    fromTimestamp_2 = timestamp;

                    //day nodes
                    $('.single-listing-calendar-wrap ul li').removeClass('to-day from-day selected in-between');
                    $this.addClass('from-day selected');
                    // move caret
                    $('.single-listing-booking-calendar-js').removeClass('arrive_active').addClass('depart_active');

                    $('input[name="arrive"]').val(vl);
                    $('input[name="depart"]').val('');

                    if(homey_booking_type != 'per_hour') {
                        homey_calculate_price_checkin();
                    }

                } else if(numClicks == 2) {

                    toTimestamp_2 = timestamp;
                    //day end node
                    $this.addClass('to-day selected');
                    $('.single-listing-booking-calendar-js').removeClass('depart_active').addClass('arrive_active');

                    var check_in_date = $('input[name="arrive"]').val();
                    check_in_date = homey_timeStamp_2(check_in_date);
                    var check_out_date = homey_timeStamp_2(vl);

                    if(check_in_date >= check_out_date && homey_booking_type != 'per_day_date') {
                        fromTimestamp_2 = timestamp;
                        toTimestamp_2 = 0;
                        //day nodes
                        $('.single-listing-calendar-wrap ul li').removeClass('to-day from-day selected in-between');
                        $this.addClass('from-day selected');

                        // move caret
                        $('.single-listing-booking-calendar-js').removeClass('arrive_active').addClass('depart_active');

                        $('input[name="arrive"]').val(vl);
                        numClicks = 1;
                    } else {
                        setInBetween_2(fromTimestamp_2, toTimestamp_2);
                        $('input[name="depart"]').val(vl);
                        $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

                        if(homey_booking_type != 'per_hour') {
                            homey_calculate_price_checkout();
                        }
                    }
                }
                if(numClicks == 2) {
                    numClicks = 0;
                }

            });
        }

        //Run only for daily/nighty booking

        if(homey_booking_type != 'per_hour') {

            homey_booking_dates();

            $('.single-listing-calendar-wrap ul li').on('hover', function () {

                var ts = $(this).data('timestamp');
                if (numClicks == 1) {
                    setInBetween_2(fromTimestamp_2, ts);
                }
            });
            /*
            * method to send in-between days
            * */
            var setInBetween_2 = function(fromTime, toTime) {
                $('.single-listing-calendar-wrap ul li').removeClass('in-between')
                    .filter(function () {
                        var currentTs = $(this).data('timestamp');
                        return currentTs > fromTime && currentTs < toTime;
                    }).addClass('in-between');
            }

            var homey_calculate_price_checkin = function() {
                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var extra_options = []; 
                var temp_opt;
                $('.homey_extra_price input').each(function() {
                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }
                });

                var total_hours = $('.total-hours-count-hidden').val();
                var guests = $("#select-listings-guests option:selected").val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(check_in_date, check_out_date, total_hours, guests, listing_id, security, extra_options);
            }

            if( is_singular_listing == 'yes' ) {
                homey_calculate_price_checkin();
            }

            var homey_calculate_price_checkout = function() {
                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);
                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var extra_options = []; 
                var temp_opt;
                $('.homey_extra_price input').each(function() {
                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }
                });

                var total_hours = $('.total-hours-count-hidden').val();
                var guests = $("#select-listings-guests option:selected").val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(check_in_date, check_out_date, total_hours, guests, listing_id, security, extra_options);
                check_booking_availability_on_date_change(check_in_date, check_out_date, total_hours, listing_id, security);
            }
        }


        /***************************************************************************/
        // PER HOUR BOOKINGS
        /***************************************************************************/

        if(homey_booking_type == 'per_hour') {

            $('.hourly-js-desktop ul li').on('click', function () {
                var $this = $(this);
                var vl = $this.data('formatted-date');
                console.log(vl);
                var formattedDate = moment(vl, "YYYY-MM-DD").format("MM-DD-YYYY");
                vl = formattedDate;
                console.log(vl);
                $this.closest('.single-date-hour').find('.check_in_arrive_date').val(vl);

                $('.single-listing-hourly-calendar-wrap ul li').removeClass('selected');
                $this.addClass('selected');

                $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

                var check_in_date = $('.check_in_arrive_date').val();
                check_in_date = moment(check_in_date, "MM-DD-YYYY").format("YYYY-MM-DD");
                check_in_date = homey_convert_date(check_in_date);
                var listing_id = $('#listing_id').val();
                var $this = $(this);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'homey_show_hours_based_on_date',
                        check_in_date: check_in_date,
                        listing_id: listing_id
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.success) {
                            $this.closest('.single-date-hour').find('#start_hour').html(response.options);
                            var $endHour = $this.closest('.single-date-hour').find('#end_hour');
                            $endHour.html(response.options);
                            $endHour.find('option:last').prop('selected', true);

                            $('.remove-booking-hour-row').on('click', function() {
                                $(this).closest('.single-date-hour').remove();
                            });

                            count_total_hours();
                        }
                    }
                });
            });

            $('.remove-booking-hour-row').on('click', function() {
                $(this).closest('.single-date-hour').remove();
            });
            
            $('.add-more-date-hours-btn').on('click', function() {
                var listing_id = $('#listing_id').val();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        'action': 'booking_add_multiple_dates',
                        'listing_id': listing_id
                    },
                    beforeSend: function() {
                        $('.add-more-date-hours-btn').text('Adding...');
                    },
                    success: function(response) {
                        var response = JSON.parse(response);
                        if(response.success) {
                            $('.multiple-date-hours-container').append(response.html);

                            $('.remove-booking-hour-row').on('click', function() {
                                count_total_hours();
                                $(this).closest('.single-date-hour').remove();
                            });

                            $('.add-more-date-hours-btn').html('<i class="fa fa-plus" aria-hidden="true"></i> Add Day');

                            $("#single-listing-date-range input").on('focus', function() {
                                $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').css("display", "block");
                                $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').addClass("arrive_active");
                                $(this).closest('.single-date-hour').find('.single-form-guests-js').css("display", "none");
                                focusedInput_2 = $(this).attr('name');
                                $(this).closest('.single-date-hour').find('.single-listing-booking-calendar-js').removeClass('arrive_active depart_active').addClass(focusedInput_2+'_active');
                            });

                            $(".btn-cross-calendar, #calendar-cross-btn, #calendar-cross-btn-i").on('click', function(){
                                $(".single-listing-booking-calendar-js").css("display", "none");
                                $(".single-experience-booking-calendar-js").css("display", "none");
                                $(".search-calendar-main").css("display", "none");
                            });

                            calendar_next_prev_child('single-listing-hourly-calendar-wrap', 'listing-cal-next', true, true);
                            calendar_next_prev_child('single-listing-hourly-calendar-wrap', 'listing-cal-prev', false, true);

                            $('.start_hour').on('change', function(){
                                count_total_hours();
                            });
                
                            $('.end_hour').on('change', function(){
                                count_total_hours();
                            });

                            $('.hourly-js-desktop ul li').on('click', function () {
                                var $this = $(this);
                                var vl = $this.data('formatted-date');
                                var formattedDate = moment(vl, "YYYY-MM-DD").format("MM-DD-YYYY");
                                vl = formattedDate;
                                $this.closest('.single-date-hour').find('.check_in_arrive_date').val(vl);
                
                                $('.single-listing-hourly-calendar-wrap ul li').removeClass('selected');
                                $this.addClass('selected');
                
                                $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();
                
                                var check_in_date = $('.check_in_arrive_date').val();
                                check_in_date = moment(check_in_date, "MM-DD-YYYY").format("YYYY-MM-DD");
                                check_in_date = homey_convert_date(check_in_date);
                                var listing_id = $('#listing_id').val();
                                var $this = $(this);
                
                                $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'homey_show_hours_based_on_date',
                                        check_in_date: check_in_date,
                                        listing_id: listing_id
                                    },
                                    success: function(response) {
                                        response = JSON.parse(response);
                                        if(response.success) {
                                            $this.closest('.single-date-hour').find('#start_hour').html(response.options);
                                            var $endHour = $this.closest('.single-date-hour').find('#end_hour');
                                            $endHour.html(response.options);
                                            $endHour.find('option:last').prop('selected', true);
                
                                            $('.remove-booking-hour-row').on('click', function() {
                                                $(this).closest('.single-date-hour').remove();
                                            });
                
                                            count_total_hours();
                                        }
                                    }
                                });
                            });

                        }
                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    }
                });
            });

            $('.start_hour').on('change', function(){
                count_total_hours();
            });

            $('.end_hour').on('change', function(){
                count_total_hours();
            });

            function count_total_hours(){
                var total_hours = 0;

                $('.single-date-hour').each(function(){
                    var start_hour = $(this).find('#start_hour').val();
                    var end_hour = $(this).find('#end_hour').val();
                    var hours = end_hour - start_hour;
                    total_hours += hours;
                });

                $('.total-hours-count-hidden').val(total_hours);
                $('.total-hours-count-number').text(total_hours);

                homey_all_booking_fields();
            }

            $('#select-listings-guests').on('change', function () {
                var current_val = $(this).val();
                if(current_val != ''){
                    $('.each-tier-value').text(current_val);
                }
                homey_all_booking_fields();
            });

            $('.homey_extra_price input').on('click', function(){
                homey_all_booking_fields();
            });

            function homey_all_booking_fields(){
                var extra_options = []; 
                var temp_opt;

                $('.homey_extra_price input').each(function() {
                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }
                });

                var booking_dates = [];
                $('.single-date-hour').each(function(index){
                    var start_hour = $(this).find('#start_hour').val();
                    var end_hour = $(this).find('#end_hour').val();
                    var arrive_date = $(this).find('#hourly_check_inn').val();

                    booking_dates[index + 1] = {
                        'arrive_date': arrive_date,
                        'start_hour': start_hour,
                        'end_hour': end_hour
                    };
                });

                var total_hours = $('.total-hours-count-hidden').val();
                var guests = $("#select-listings-guests option:selected").val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(booking_dates, total_hours, guests, listing_id, security, extra_options);
            }

            // $('.hourly-js-mobile ul li').on('click', function () {
            //     var $this = $(this);
            //     var vl = $this.data('formatted-date');
            //     $('input[name="arrive"]').val(vl);

            //     $('.single-listing-hourly-calendar-wrap ul li').removeClass('selected');
            //     $this.addClass('selected');

            //     $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

            //     var check_in_date = $('input[name="arrive"]').val();
            //     check_in_date = homey_convert_date(check_in_date);

            //     var start_hour = $('#start_hour_overlay').val();
            //     var end_hour = $('#end_hour_overlay').val();
            //     var guests = $('input[name="guests"]').val();
            //     var listing_id = $('#listing_id').val();
            //     var security = $('#reservation-security').val();
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security);

            //     if(check_in_date === '' || start_hour === '' || end_hour === '')
            //         return;
            //     check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security);
            // });

            // $('#start_hour_overlay').on('change', function () {
            //     var check_in_date = $('input[name="arrive"]').val();
            //     check_in_date = homey_convert_date(check_in_date);

            //     var start_hour = $('#start_hour_overlay').val();
            //     var end_hour = $('#end_hour_overlay').val();
            //     var guests = $('input[name="guests"]').val();
            //     var listing_id = $('#listing_id').val();
            //     var security = $('#reservation-security').val();
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security);

            //     if(check_in_date === '' || start_hour === '' || end_hour === '')
            //         return;
            //     check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security);
            // });

            // $('#end_hour_overlay').on('change', function () {
            //     var check_in_date = $('input[name="arrive"]').val();
            //     check_in_date = homey_convert_date(check_in_date);

            //     var start_hour = $('#start_hour_overlay').val();
            //     var end_hour = $('#end_hour_overlay').val();
            //     var guests = $('input[name="guests"]').val();
            //     var listing_id = $('#listing_id').val();
            //     var security = $('#reservation-security').val();
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security);
            //     check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security);
            // });

            // $('.apply_guests').on('click', function () {
            //     var check_in_date = $('input[name="arrive"]').val();
            //     check_in_date = homey_convert_date(check_in_date);

            //     var start_hour = $('select[name="start_hour"]').val();
            //     var end_hour = $('select[name="end_hour"]').val();
            //     var guests = $('input[name="guests"]').val();
            //     var listing_id = $('#listing_id').val();
            //     var security = $('#reservation-security').val();
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security);
            //     check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security);
            // });

            // $('#apply_guests_hourly').on('click', function () {
            //     var check_in_date = $('input[name="arrive"]').val();
            //     check_in_date = homey_convert_date(check_in_date);

            //     var start_hour = $('#start_hour_overlay').val();
            //     var end_hour = $('#end_hour_overlay').val();
            //     var guests = $('input[name="guests"]').val();
            //     var listing_id = $('#listing_id').val();
            //     var security = $('#reservation-security').val();
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security);
            //     check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security);
            // });


            // $('.homey_extra_price input').on('click', function(){
            //     var extra_options = []; var temp_opt;
            //     var items_for_checkboxes = $('.homey_extra_price input');

            //     if($("#overlay-booking-module").hasClass("open")){
            //         items_for_checkboxes = $("#overlay-booking-module").find('.homey_extra_price input');
            //     }

            //     $(items_for_checkboxes).each(function() {

            //         if( ($(this).is(":checked")) ) {
            //             var extra_name = $(this).data('name');
            //             var extra_price = $(this).data('price');
            //             var extra_type = $(this).data('type');
            //             temp_opt    =   '';
            //             temp_opt    =   extra_name;
            //             temp_opt    =   temp_opt + '|' + extra_price;
            //             temp_opt    =   temp_opt + '|' + extra_type;
            //             extra_options.push(temp_opt);
            //         }

            //     });

            //     if($("#overlay-booking-module").hasClass("open")){
            //         var check_in_date = $("#overlay-booking-module").find('input[name="arrive"]').val();
            //         var start_hour = $("#overlay-booking-module").find('select[name="start_hour"]').val();
            //         var end_hour = $("#overlay-booking-module").find('select[name="end_hour"]').val();
            //         var guests = $("#overlay-booking-module").find('input[name="guests"]').val();
            //         var listing_id = $("#overlay-booking-module").find('#listing_id').val();
            //         var security = $("#overlay-booking-module").find('#reservation-security').val();
            //     }else{
            //         var check_in_date = $('input[name="arrive"]').val();
            //         var start_hour = $('select[name="start_hour"]').val();
            //         var end_hour = $('select[name="end_hour"]').val();
            //         var guests = $('input[name="guests"]').val();
            //         var listing_id = $('#listing_id').val();
            //         var security = $('#reservation-security').val();
            //     }

            //     check_in_date = homey_convert_date(check_in_date);
            //     homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security, extra_options);

            // });

        }

        // $('.start_hour').on('change', function () {
        //     var extra_options = []; 
        //     var temp_opt;
        //     $('.homey_extra_price input').each(function() {
        //         if( ($(this).is(":checked")) ) {
        //             var extra_name = $(this).data('name');
        //             var extra_price = $(this).data('price');
        //             var extra_type = $(this).data('type');
        //             temp_opt    =   '';
        //             temp_opt    =   extra_name;
        //             temp_opt    =   temp_opt + '|' + extra_price;
        //             temp_opt    =   temp_opt + '|' + extra_type;
        //             extra_options.push(temp_opt);
        //         }
        //     });

        //     var check_in_date = '';
        //     var check_out_date = '';

        //     var total_hours = $('.total-hours-count-hidden').val();
        //     var guests = $("#select-listings-guests option:selected").val();
        //     var listing_id = $('#listing_id').val();
        //     var security = $('#reservation-security').val();

        //     homey_calculate_booking_cost(check_in_date, check_out_date, total_hours, guests, listing_id, security, extra_options);
        //     check_booking_availability_on_date_change(check_in_date, check_out_date, total_hours, listing_id, security);
        // });

        // $('.end_hour').on('change', function () {
            
        //     var extra_options = []; 
        //     var temp_opt;
        //     $('.homey_extra_price input').each(function() {

        //         if( ($(this).is(":checked")) ) {
        //             var extra_name = $(this).data('name');
        //             var extra_price = $(this).data('price');
        //             var extra_type = $(this).data('type');
        //             temp_opt    =   '';
        //             temp_opt    =   extra_name;
        //             temp_opt    =   temp_opt + '|' + extra_price;
        //             temp_opt    =   temp_opt + '|' + extra_type;
        //             extra_options.push(temp_opt);
        //         }

        //     });

        //     var check_in_date = '';
        //     var check_out_date = '';

        //     var total_hours = $('.total-hours-count-hidden').val();
        //     var guests = $("#select-listings-guests option:selected").val();
        //     var listing_id = $('#listing_id').val();
        //     var security = $('#reservation-security').val();

        //     homey_calculate_booking_cost(check_in_date, check_out_date, total_hours, guests, listing_id, security, extra_options);
        //     check_booking_availability_on_date_change(check_in_date, check_out_date, total_hours, listing_id, security);
        // });

        /* ------------------------------------------------------------------------ */
        /*  Guests count
        /* ------------------------------------------------------------------------ */

        var single_listing_guests = function() {

            $('.adult_plus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val()) || 0;
                var adult_guest = parseInt($('input[name="adult_guest"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());

                adult_guest++;
                $('.homey_adult').text(adult_guest);
                $('input[name="adult_guest"]').val(adult_guest);

                var total_guests = adult_guest + child_guest;

                if( (allow_additional_guests != 'yes') && (total_guests >= allowed_guests_num)) {
                    $('.adult_plus').attr("disabled", true);
                    $('.child_plus').attr("disabled", true);
                    $('input[name="guests"]').val(allowed_guests_num);


                } else if( (allow_additional_guests == 'yes') && (total_guests >= allowed_guests_plus_additional) ) {
                    if(num_additional_guests !== '') {
                        $('.adult_plus').attr("disabled", true);
                        $('.child_plus').attr("disabled", true);
                        $('input[name="guests"]').val(allowed_guests_num);

                    }
                }

                $('input[name="guests"]').val(total_guests);
            });

            $('.adult_minus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val()) || 0;
                var adult_guest = parseInt($('input[name="adult_guest"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());

                if (adult_guest == 0) return;
                adult_guest--;
                $('.homey_adult').text(adult_guest);
                $('input[name="adult_guest"]').val(adult_guest);

                var total_guests = adult_guest + child_guest;
                $('input[name="guests"]').val(total_guests);

                $('.adult_plus').removeAttr("disabled");
                $('.child_plus').removeAttr("disabled");
            });

            $('.child_plus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());
                var adult_guest = parseInt($('input[name="adult_guest"]').val());

                child_guest++;
                $('.homey_child').text(child_guest);
                $('input[name="child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                if( (allow_additional_guests != 'yes') && (total_guests == allowed_guests_num)) {
                    $('.adult_plus').attr("disabled", true);
                    $('.child_plus').attr("disabled", true);

                } else if( (allow_additional_guests == 'yes') && (total_guests == allowed_guests_plus_additional) ) {
                    if(num_additional_guests !== '') {
                        $('.adult_plus').attr("disabled", true);
                        $('.child_plus').attr("disabled", true);
                    }
                }

                $('input[name="guests"]').val(total_guests);

            });

            $('.child_minus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());
                var adult_guest = parseInt($('input[name="adult_guest"]').val());

                if (child_guest == 0) return;
                child_guest--;
                $('.homey_child').text(child_guest);
                $('input[name="child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                $('input[name="guests"]').val(total_guests);

                $('.adult_plus').removeAttr("disabled");
                $('.child_plus').removeAttr("disabled");

            });
        }

        single_listing_guests();

        // Single experience booking form
        var experience_id = $('#experience_id').val();
        if( is_singular_experience ) {

            $("#single-experience-date-range input").on('focus', function() {
                var bookingCalendarJsEl = $('#single-experience-date-range').find('.single-experience-booking-calendar-js');
                var mainCalendarJsEl = $('#single-experience-date-range').find('.single-experience-main-calendar-js');

                if (typeof bookingCalendarJsEl != 'undefined') {
                    console.log('withinnnn...');

                    $(bookingCalendarJsEl).css("display", "block");
                    $(bookingCalendarJsEl).addClass("arrive_active");
                    $('.single-form-guests-js').css("display", "none");
                    focusedInput_2 = $(this).attr('name');
                    $(bookingCalendarJsEl).removeClass('arrive_active depart_active').addClass(focusedInput_2 + '_active');
                }

                if (typeof mainCalendarJsEl != 'undefined') {
                    console.log('not withinnnn...');

                    $(mainCalendarJsEl).css("display", "block");
                    $(mainCalendarJsEl).addClass("arrive_active");
                    $('.single-form-guests-js').css("display", "none");
                    focusedInput_2 = $(this).attr('name');
                    $(mainCalendarJsEl).removeClass('arrive_active depart_active').addClass(focusedInput_2 + '_active');
                }
            });

            $("#mob-single-experience-date-range input").on('focus', function() {
                var bookingCalendarJsEl = $('#mob-single-experience-date-range').find('.single-experience-booking-calendar-js');
                var mainCalendarJsEl = $('#mob-single-experience-date-range').find('.single-experience-main-calendar-js');

                if (typeof bookingCalendarJsEl != 'undefined') {
                    //console.log('withinnnn... mob');

                    $(bookingCalendarJsEl).css("display", "block");
                    $(bookingCalendarJsEl).addClass("arrive_active");
                    $('.single-form-guests-js').css("display", "none");
                    focusedInput_2 = $(this).attr('name');
                    $(bookingCalendarJsEl).removeClass('arrive_active depart_active').addClass(focusedInput_2 + '_active');
                }

                if (typeof mainCalendarJsEl != 'undefined') {
                    //console.log('not withinnnn... mob');

                    $(mainCalendarJsEl).css("display", "block");
                    $(mainCalendarJsEl).addClass("arrive_active");
                    $('.single-form-guests-js').css("display", "none");
                    focusedInput_2 = $(this).attr('name');
                    $(mainCalendarJsEl).removeClass('arrive_active depart_active').addClass(focusedInput_2 + '_active');
                }
            });


            $(".single-guests-js input").on('focus', function() {
                $(this).prev("label").css("display", "block");
                $(this).addClass("on-focus");
                $('.single-form-guests-js').css("display", "block");
            });

            var numClicks = 0;
            var fromTimestamp_2, toTimestamp_2 = 0; // init start and end timestamps

            var homey_calculate_exp_price_checkout = function() {
                var extra_options = []; var temp_opt;

                $('.homey_exp_extra_price input').each(function() {

                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }

                });

                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var guests = $('input[name="exp_guests"]').val();
                var experience_id = $('#experience_id').val();
                var security = $('#reservation-security').val();

                homey_calculate_exp_booking_cost(check_in_date, guests, experience_id, security, extra_options);
                check_exp_availability_on_date_change(check_in_date, experience_id, security);
            }

            var homey_experiences_booking_dates = function() {

                $('.single-experience-booking-calendar-js ul li').on('click', function() {

                    var $this = $(this);
                    $this.show();
                    if($this.hasClass('past-day') || $this.hasClass('homey-not-available-for-booking')) {
                        if(!$this.hasClass('reservation_start')) {
                            return false;
                        }
                    }

                    numClicks = 2;
                    var vl = $this.data('formatted-date');
                    var formattedDate = moment(vl, "DD-MM-YYYY").format("MM-DD-YYYY");
                    vl = formattedDate;

                    var timestamp = $this.data('timestamp');

                    var available_slots_by_date_text = $this.data('availableSlotsInfo');
                    console.log(' oooo '+ available_slots_by_date_text);
                    $("#available_slots_by_date_text").text('');
                    $("#available_slots_by_date_text").text(available_slots_by_date_text);

                    toTimestamp_2 = timestamp;
                    //day end node
                    $this.addClass('to-day selected');
                    $('.single-experience-booking-calendar-js').removeClass('depart_active').addClass('arrive_active');

                    var check_in_date = $('input[name="arrive"]').val();
                    check_in_date = homey_timeStamp_2(check_in_date);

                    $('input[name="arrive"]').val(vl);
                    var coming_guests = $('input[name="exp_guests"]').val();
                    $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

                    if( coming_guests > 0 ) {
                        homey_calculate_exp_price_checkout();
                    }
                });
            }

            homey_experiences_booking_dates();


            $('.apply_exp_guests').on('click', function () {
                var extra_options = []; var temp_opt;
                $('.homey_exp_extra_price input').each(function() {

                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }

                });

                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var guests = $('input[name="exp_guests"]').val();
                var experience_id = $('#experience_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_exp_booking_cost(check_in_date, guests, experience_id, security, extra_options);
                check_exp_availability_on_date_change(check_in_date, experience_id, security, extra_options);
            });

            $('.homey_exp_extra_price input').on('click', function(){
                var extra_options = []; var temp_opt;

                $('.homey_extra_price input').each(function() {

                    if( ($(this).is(":checked")) ) {
                        var extra_name = $(this).data('name');
                        var extra_price = $(this).data('price');
                        var extra_type = $(this).data('type');
                        temp_opt    =   '';
                        temp_opt    =   extra_name;
                        temp_opt    =   temp_opt + '|' + extra_price;
                        temp_opt    =   temp_opt + '|' + extra_type;
                        extra_options.push(temp_opt);
                    }

                });

                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var start_hour = $('select[name="start_hour"]').val();
                var end_hour = $('select[name="end_hour"]').val();
                var guests = $("#select-listings-guests option:selected").val();
                var experience_id = $('#experience_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(check_in_date, check_out_date, start_hour, end_hour, guests, listing_id, security, extra_options);

            });


        } // end is_singular_experience


        /* ------------------------------------------------------------------------ */
        /*  Guests count
        /* ------------------------------------------------------------------------ */

        var single_experience_guests = function() {

            $('.exp_adult_plus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="exp_guests"]').val()) || 0;
                var adult_guest = parseInt($('input[name="exp_adult_guest"]').val());
                var child_guest = parseInt($('input[name="exp_child_guest"]').val());

                adult_guest++;
                $('.exp_homey_adult').text(adult_guest);
                $('input[name="exp_adult_guest"]').val(adult_guest);

                var total_guests = adult_guest + child_guest;

                if( (allow_additional_guests != 'yes') && (total_guests == allowed_guests_num)) {
                    $('.exp_adult_plus').attr("disabled", true);
                    $('.exp_child_plus').attr("disabled", true);

                } else if( (allow_additional_guests == 'yes') && (total_guests == allowed_guests_plus_additional) ) {
                    if(num_additional_guests !== '') {
                        $('.exp_adult_plus').attr("disabled", true);
                        $('.exp_child_plus').attr("disabled", true);
                    }
                }

                $('input[name="exp_guests"]').val(total_guests);
            });

            $('.exp_adult_minus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="exp_guests"]').val()) || 0;
                var adult_guest = parseInt($('input[name="exp_adult_guest"]').val());
                var child_guest = parseInt($('input[name="exp_child_guest"]').val());

                if (adult_guest == 0) return;
                adult_guest--;
                $('.exp_homey_adult').text(adult_guest);
                $('input[name="exp_adult_guest"]').val(adult_guest);

                var total_guests = adult_guest + child_guest;
                $('input[name="exp_guests"]').val(total_guests);

                $('.exp_adult_plus').removeAttr("disabled");
                $('.exp_child_plus').removeAttr("disabled");
            });

            $('.exp_child_plus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="exp_guests"]').val());
                var child_guest = parseInt($('input[name="exp_child_guest"]').val());
                var adult_guest = parseInt($('input[name="exp_adult_guest"]').val());

                child_guest++;
                $('.exp_homey_child').text(child_guest);
                $('input[name="exp_child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                if( (allow_additional_guests != 'yes') && (total_guests == allowed_guests_num)) {
                    $('.exp_adult_plus').attr("disabled", true);
                    $('.exp_child_plus').attr("disabled", true);

                } else if( (allow_additional_guests == 'yes') && (total_guests == allowed_guests_plus_additional) ) {
                    if(num_additional_guests !== '') {
                        $('.exp_adult_plus').attr("disabled", true);
                        $('.exp_child_plus').attr("disabled", true);
                    }
                }

                $('input[name="exp_guests"]').val(total_guests);

            });

            $('.exp_child_minus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="exp_guests"]').val());
                var child_guest = parseInt($('input[name="exp_child_guest"]').val());
                var adult_guest = parseInt($('input[name="exp_adult_guest"]').val());

                if (child_guest == 0) return;
                child_guest--;
                $('.exp_homey_child').text(child_guest);
                $('input[name="exp_child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                $('input[name="exp_guests"]').val(total_guests);

                $('.exp_adult_plus').removeAttr("disabled");
                $('.exp_child_plus').removeAttr("disabled");

            });
        }

        single_experience_guests();

        /* ------------------------------------------------------------------------ */
        /*  Reservation Request
        /* ------------------------------------------------------------------------ */
        $('#request_for_reservation, #request_for_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var extra_options = []; 
            var temp_opt;
            $('.homey_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            var booking_dates = [];
            $('.single-date-hour').each(function(index){
                var start_hour = $(this).find('#start_hour').val();
                var end_hour = $(this).find('#end_hour').val();
                var arrive_date = $(this).find('#hourly_check_inn').val();

                booking_dates[index + 1] = {
                    'arrive_date': arrive_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour
                };
            });

            var guest_message = $('.guest-message').val();
            var total_hours = $('.total-hours-count-hidden').val();
            var guests = $("#select-listings-guests option:selected").val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();
            var new_reser_request_user_email = $('#new_reser_request_user_email').val();
            if(new_reser_request_user_email == ''){
                new_reser_request_user_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
            }

            var action_name_for_wp_action = 'homey_add_reservation';

            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': action_name_for_wp_action,
                    'booking_dates': booking_dates,
                    'total_hours': total_hours,
                    'guests': guests,
                    'listing_id': listing_id,
                    'extra_options': extra_options,
                    'guest_message': guest_message,
                    'new_reser_request_user_email': new_reser_request_user_email,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        console.log(data.reservation_detail);
                        $('.check_in_date, .check_out_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');

                        if( data.reservation_detail ) {
                            window.location.href = data.reservation_detail;
                        }

                    } else {
                        if($('.account-loggedin').length < 1){
                            jQuery('#modal-login').modal('show');
                        }
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });
        });

        /* ------------------------------------------------------------------------ */
        /*  Experiences Reservation Request
         /* ------------------------------------------------------------------------ */
        $('#request_for_exp_reservation, #request_for_exp_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var extra_options = [];
            var temp_opt;
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            var guest_message = $('textarea[name="exp_guest_message"]').val();

            var guests = $('input[name="exp_guests"]').val();
            var adult_guest = $('input[name="exp_adult_guest"]').val();
            var child_guest = $('input[name="exp_child_guest"]').val();

            var experience_id = $('#experience_id').val();

            var new_reser_exp_request_user_email = $('#new_reser_exp_request_user_email').val();
            console.log(new_reser_exp_request_user_email+ ' first try data --> ');

            if(typeof new_reser_exp_request_user_email === 'undefined' || new_reser_exp_request_user_email == ''){
                new_reser_exp_request_user_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
                console.log(new_reser_exp_request_user_email+ ' no data --> ');
            }

            var security = $('#reservation-security').val();
            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();

            save_exp_booking_details($('input[name="arrive"]').val(), guests, guest_message, adult_guest, child_guest, new_reser_exp_request_user_email);

            $('.homey_exp_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            //if( parseInt( userID, 10 ) === 0 ) {
            // $('#modal-login').modal('show');
            // } else {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_add_exp_reservation',
                    'check_in_date': check_in_date,
                    'guests': guests,
                    'experience_id': experience_id,
                    'extra_options': extra_options,
                    'guest_message': guest_message,
                    'new_reser_exp_request_user_email': new_reser_exp_request_user_email,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $('.check_in_date, .check_out_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');

                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            }); //
            //}

        });

        /* ------------------------------------------------------------------------ */
        /*  Hourly Reservation Request
         /* ------------------------------------------------------------------------ */
        $('#request_hourly_reservation').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            var guest_message = $('textarea[name="guest_message"]').val();

            if(guest_message == ''){
                guest_message = $('#overlay-booking-module').find('textarea[name="guest_message"]').val();
            }

            var start_hour = $('select[name="start_hour"]').val();
            var end_hour = $('select[name="end_hour"]').val();
            var guests = $('input[name="guests"]').val();
            var listing_id = $('#listing_id').val();

            var new_reser_request_user_email = $('#new_reser_request_user_email').val();

            if(new_reser_request_user_email == ''){
                new_reser_request_user_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
            }

            var security = $('#reservation-security').val();

            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();
            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            var action_name_for_wp_action = 'homey_add_hourly_reservation';
            if (new_reser_request_user_email && new_reser_request_user_email.trim() !== "") {
                action_name_for_wp_action = 'hm_no_login_add_hourly_reservation';
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': action_name_for_wp_action,
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'guest_message': guest_message,
                    'listing_id': listing_id,
                    'new_reser_request_user_email': new_reser_request_user_email,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $('.check_in_date, .check_out_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');

                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            }); // end ajax
            //}

        });

        /* ------------------------------------------------------------------------ */
        /*  Hourly Reservation Request
         /* ------------------------------------------------------------------------ */
        $('#request_hourly_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            var guest_message = $('textarea[name="guest_message"]').val();
            if(guest_message == ''){
                guest_message = $('#overlay-booking-module').find('textarea[name="guest_message"]').val();
            }

            var start_hour = $('#start_hour_overlay').val();
            var end_hour = $('#end_hour_overlay').val();
            var guests = $('input[name="guests"]').val();
            var listing_id = $('#listing_id').val();
            var new_reser_request_user_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();

            var security = $('#reservation-security').val();
            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            //if( parseInt( userID, 10 ) === 0 ) {
            // $('#modal-login').modal('show');
            //} else {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_add_hourly_reservation',
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'guest_message': guest_message,
                    'listing_id': listing_id,
                    'new_reser_request_user_email': new_reser_request_user_email,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $('.check_in_date, .check_out_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');

                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            }); // end ajax
            //}

        });

        /* ------------------------------------------------------------------------ */
        /*  Reserve a period host
         /* ------------------------------------------------------------------------ */
        $('#reserve_period_host').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('#period_start_date').val();
            //check_in_date = homey_convert_date(check_in_date);

            var check_out_date = $('#period_end_date').val();
            //check_out_date = homey_convert_date(check_out_date);

            var listing_id = $('#period_listing_id').val();
            var period_note = $('#period_note').val();
            var security = $('#period-security').val();
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_reserve_period_host',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'period_note': period_note,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = calendar_link;
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        //experience reserve host
        $('#reserve_exp_period_host').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('#period_start_date').val();
            //check_in_date = homey_convert_date(check_in_date);

            var check_out_date = $('#period_end_date').val();
            //check_out_date = homey_convert_date(check_out_date);

            var experience_id = $('#period_experience_id').val();
            var period_note = $('#period_note').val();
            var security = $('#period-security').val();
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_reserve_exp_period_host',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'period_note': period_note,
                    'experience_id': experience_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = exp_calendar_link;
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });
        //experience reserve host

        //alert(HOMEY_ajax_vars.homey_timezone);
        /* ------------------------------------------------------------------------ */
        /* Per Hour availability calendar
        /* ------------------------------------------------------------------------ */
        var homey_hourly_availability_calendar = function(){
            var  today = new Date();
            var listing_booked_dates=[];
            var listing_pending_dates=[];
            var html_booked_info = "<div id='html_booked_info' style='display:none;'>";
            for (var key in booked_hours_array) {

                if (booked_hours_array.hasOwnProperty(key) && key!=='') {
                    var temp_book=[];
                    temp_book['title']     =   HOMEY_ajax_vars.hc_reserved_label,
                        temp_book ['start']    =   moment.unix(key).utc().format(),
                        temp_book ['end']      =   moment.unix( booked_hours_array[key]).utc().format(),
                        temp_book ['editable'] =   false;
                    temp_book ['color'] =   '#fdd2d2';
                    temp_book ['textColor'] =   '#444444';
                    html_booked_info += "<span data-start-hours='"+moment.unix(key).utc().format('h:mm')+"' data-end-hours='"+moment.unix( booked_hours_array[key]).utc().format('h:mm')+"' data-date='"+moment.unix(key).utc().format("D-M-Y")+"'></span>";
                    listing_booked_dates.push(temp_book);
                }
            }
            html_booked_info += '</div>';
            $("body").append(html_booked_info);
            for (var key_pending in pending_hours_array) {
                if (pending_hours_array.hasOwnProperty(key_pending) && key_pending!=='') {
                    var temp_pending=[];
                    temp_pending['title']     =   HOMEY_ajax_vars.hc_pending_label,
                        temp_pending ['start']    =   moment.unix(key_pending).utc().format(),
                        temp_pending ['end']      =   moment.unix( pending_hours_array[key_pending]).utc().format(),
                        temp_pending ['editable'] =   false;
                    temp_pending ['color']    =   '#ffeedb';
                    temp_pending ['textColor'] =   '#333333';
                    listing_pending_dates.push(temp_pending);
                }
            }

            var hours_slot = $.merge(listing_booked_dates, listing_pending_dates);
            var calendarEl = document.getElementById('homey_hourly_calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: HOMEY_ajax_vars.homey_current_lang,
                timeZone: HOMEY_ajax_vars.homey_timezone,
                plugins: [ 'timeGrid' ],
                defaultView: 'timeGridWeek',
                slotDuration:'00:30:00',
                minTime: booking_start_hour,
                maxTime: booking_end_hour,
                events: hours_slot,
                defaultDate: today,
                selectHelper: true,
                selectOverlap : false,
                footer: false,
                nowIndicator: true,
                allDayText: HOMEY_ajax_vars.hc_hours_label,
                weekNumbers: false,
                weekNumbersWithinDays: true,
                weekNumberCalculation: 'ISO',
                editable: false,
                eventLimit: true,
                unselectAuto: false,
                isRTL: homey_is_rtl,
                buttonText: {
                    today:    HOMEY_ajax_vars.hc_today_label
                }
            });

            calendar.render();
        }

        if(homey_booking_type == 'per_hour' && is_listing_detail == 'yes') {
            if( $('#homey_hourly_calendar').length > 0 ) {
                homey_hourly_availability_calendar();
            }
        }

        var prev_date = $("#hourly_check_inn").val();

        function disable_hourlystart_and_end(){
            if($("#hourly_check_inn").val() != '') {
                $("span").removeClass('already-booked');

                var all_booked = $("#post_already_bookd_dates").find("span");
                var start_hours = $(".start_hour").find(".text");
                var end_hours = $(".end_hour").find(".text");
                var homey_date_format_moment = homey_date_format.replace("mm", "M");
                homey_date_format_moment = homey_date_format_moment.replace("dd", "D");
                homey_date_format_moment = homey_date_format_moment.replace("yy", "Y");
                var date_selected = moment($("#hourly_check_inn").val(), homey_date_format_moment).format('D-M-Y');

                $(start_hours).each(function (i, start_itm) {
                    $(start_itm).closest('li').removeClass('disabled');
                });

                $(end_hours).each(function (i, end_itm) {
                    $(end_itm).closest('li').removeClass('disabled');
                });

                $(start_hours).each(function (i, start_itm) {
                    var current_time = '';
                    if ($(start_itm).text().indexOf(":") > -1) {
                        current_time =  $(start_itm).text();
                        current_time = current_time.replace(" am", "");
                        current_time = current_time.replace(" pm", "");

                        current_time = date_selected + ' ' + current_time+':00';
                        $(all_booked).each(function (i, itm) {
                            var already_booked_date = $(itm).data("datetime");
                            // console.log(already_booked_date+' kki'+ current_time);

                            var ms = moment(already_booked_date,"DD/MM/YYYY HH:mm:ss").diff(moment(current_time,"DD/MM/YYYY HH:mm:ss"));
                            var d = moment.duration(ms);

                            if (d == 0) {
                                $(start_itm).addClass("already-booked");
                                $(start_itm).closest('li').addClass('disabled');
                            }
                            // else {
                            //     $(start_itm).closest('li').removeClass('disabled');
                            //     $(start_itm).removeClass("already-booked");
                            // }
                        });
                    }
                });

                $(end_hours).each(function (i, end_itm) {
                    var current_time = '';
                    if ($(end_itm).text().indexOf(":") > -1) {
                        current_time =  $(end_itm).text();
                        current_time = current_time.replace(" am", "");
                        current_time = current_time.replace(" pm", "");

                        current_time = date_selected + ' ' + current_time+':00';
                        $(all_booked).each(function (i, itm) {
                            var already_booked_date = $(itm).data("datetime");
                            // console.log(already_booked_date+' kki'+ current_time);

                            var ms = moment(already_booked_date,"DD/MM/YYYY HH:mm:ss").diff(moment(current_time,"DD/MM/YYYY HH:mm:ss"));
                            var d = moment.duration(ms);

                            if (d == 0) {
                                if(!$(end_itm).closest('li').prev().hasClass('disabled') && !$(end_itm).closest('li').prev().hasClass('checkout-available')){
                                    $(end_itm).addClass("already-booked");
                                    $(end_itm).closest('li').addClass('checkout-available');
                                }else{
                                    $(end_itm).addClass("already-booked");
                                    $(end_itm).closest('li').addClass('disabled');
                                }

                            }
                            // else {
                            //     $(end_itm).closest('li').removeClass('disabled');
                            //     $(end_itm).removeClass("already-booked");
                            // }
                        });
                    }
                });
            }

            prev_date = $("#hourly_check_inn").val();
        }

        setInterval(function(){
            if(prev_date != $("#hourly_check_inn").val()){
                disable_hourlystart_and_end();
            }
        }, 500);

        /* ------------------------------------------------------------------------ */
        /*  Instace Booking
         /* ------------------------------------------------------------------------ */
        $('#instance_reservation, #instance_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var extra_options = [];
            var temp_opt;
            var $this = $(this);
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            var check_out_date = $('input[name="depart"]').val();
            check_out_date = homey_convert_date(check_out_date);

            var guests = $('input[name="guests"]').val();
            var adult_guest = $('input[name="adult_guest"]').val();
            var child_guest = $('input[name="child_guest"]').val();

            var guest_message = $('textarea[name="guest_message"]').val();
            if(guest_message == ''){
                guest_message = $('#overlay-booking-module').find('textarea[name="guest_message"]').val();
            }

            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();
            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();

            var new_reser_request_user_email = $('#new_reser_request_user_email').val();
            if(typeof new_reser_request_user_email == 'undefined') {
                new_reser_request_user_email = '';
            }

            var overlay_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
            if(typeof overlay_email != 'undefined'){
                if(new_reser_request_user_email == '' && overlay_email.length > 0) {
                    new_reser_request_user_email = overlay_email;

                }
            }

            save_booking_details($('input[name="arrive"]').val(), $('input[name="depart"]').val(), guests, guest_message, adult_guest, child_guest);

            $('.homey_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            var action_name_for_wp_action = 'homey_instance_booking';
            if (typeof new_reser_request_user_email != 'undefined' && new_reser_request_user_email.trim() !== "") {
                action_name_for_wp_action = 'hm_no_login_instance_booking';
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': action_name_for_wp_action,
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'guests': guests,
                    'adult_guest': adult_guest,
                    'child_guest': child_guest,
                    'extra_options': extra_options,
                    'guest_message': guest_message,
                    'new_reser_request_user_email': new_reser_request_user_email,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function (data) {

                    if( data.success ) {
                        $('.check_in_date, .check_out_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = data.instance_url;
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });
            //}

        });

        /* ------------------------------------------------------------------------ */
        /*  Hourly instace Booking
        /* ------------------------------------------------------------------------ */
        $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var start_hour;
            var end_hour;
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            start_hour = $('select[name="start_hour"]').val();
            end_hour = $('select[name="end_hour"]').val();

            if(start_hour == ''){
                start_hour = $('#overlay-booking-module').find('select[name="start_hour"]').val();
                end_hour = $('#overlay-booking-module').find('select[name="end_hour"]').val();
            }

            var guests = $('input[name="guests"]').val();
            var adult_guest = $('input[name="adult_guest"]').val();
            var child_guest = $('input[name="child_guest"]').val();

            var guest_message = $('textarea[name="guest_message"]').val();
            if(guest_message == ''){
                guest_message = $('#overlay-booking-module').find('textarea[name="guest_message"]').val();
            }
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();
            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();

            if(homey_is_mobile || homey_window_width < 991) {
                start_hour = $('#start_hour_overlay').val();
                end_hour = $('#end_hour_overlay').val();
            }
            //saving booking details and adding to referer URL
            save_hourl_booking_details($('input[name="arrive"]').val(), start_hour, end_hour, guests, guest_message, adult_guest, child_guest);

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            var new_reser_request_user_email = $('#new_reser_request_user_email').val();
            if(typeof new_reser_request_user_email == 'undefined') {
                new_reser_request_user_email = '';
            }

            var overlay_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
            if(typeof overlay_email != 'undefined'){
                if(new_reser_request_user_email == '' && overlay_email.length > 0) {
                    new_reser_request_user_email = overlay_email;

                }
            }

            var action_name_for_wp_action = 'homey_instance_hourly_booking';
            if (typeof new_reser_request_user_email != 'undefined' && new_reser_request_user_email.trim() !== "") {
                action_name_for_wp_action = 'hm_no_login_instance_hourly_booking';
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': action_name_for_wp_action,
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'guest_message': guest_message,
                    'security': security,
                    'new_reser_request_user_email' : new_reser_request_user_email
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function (data) {

                    if( data.success ) {
                        $('.check_in_date, .start_hour, .end_hour').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = data.instance_url;
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Experiences instance Booking
        /* ------------------------------------------------------------------------ */
        $('#instance_exp_reservation, #instance_exp_reservation_mobile').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('input[name="arrive"]').val();
            check_in_date = homey_convert_date(check_in_date);

            var guests = $('input[name="exp_guests"]').val();
            var adult_guest = $('input[name="exp_adult_guest"]').val();
            var child_guest = $('input[name="exp_child_guest"]').val();

            var guest_message = $('textarea[name="exp_guest_message"]').val();
            if(guest_message == ''){
                guest_message = $('#overlay-booking-module').find('textarea[name="guest_message"]').val();
            }

            var experience_id = $('#experience_id').val();
            var security = $('#reservation-security').val();
            var notify = $this.parents('.homey_notification');
            notify.find('.notify').remove();

            var new_reser_request_user_email = $('#new_reser_exp_request_user_email').val();

            if(new_reser_request_user_email == ''){
                new_reser_request_user_email = $('#overlay-booking-module').find('#new_reser_request_user_email').val();
            }

            //saving booking details and adding to referer URL
            save_exp_booking_details($('input[name="arrive"]').val(), guests, guest_message, adult_guest, child_guest);

            if( homey_is_mobile || homey_window_width < 991 ) {
                start_hour  = $('#start_hour_overlay').val();
                end_hour    = $('#end_hour_overlay').val();
            }

            var extra_options = [];
            var temp_opt;
            $('.homey_exp_extra_price input').each(function() {
                if( ($(this).is(":checked")) ) {
                    var extra_name = $(this).data('name');
                    var extra_price = $(this).data('price');
                    var extra_type = $(this).data('type');
                    temp_opt    =   '';
                    temp_opt    =   extra_name;
                    temp_opt    =   temp_opt + '|' + extra_price;
                    temp_opt    =   temp_opt + '|' + extra_type;
                    extra_options.push(temp_opt);
                }
            });

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_instant_exp_booking',
                    'check_in_date': check_in_date,
                    'guests': guests,
                    'extra_options': extra_options,
                    'new_reser_exp_request_user_email': new_reser_request_user_email,
                    'experience_id': experience_id,
                    'guest_message': guest_message,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function (data) {

                    if( data.success ) {
                        $('.check_in_date').val('');
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = data.instance_url;
                    } else {
                        if($('.account-loggedin').length < 1){
                            jQuery('#modal-login').modal('show');
                        }
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');

                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });

        }); // experiences instance Booking

        /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
        /* ------------------------------------------------------------------------ */
        $('.confirm-reservation').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $this.data('reservation_id');
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_confirm_reservation',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
        $('.confirm-offsite-reservation').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $this.data('reservation_id');
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_confirm_offsite_reservation',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
        $('#guest_paid_button').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var parentDIV = $this.parents('.user-dashboard-right');
            var reservation_id = $('#reservation_id').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_guest_made_payment',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
        $('#decline').on('click', function(e){
            e.preventDefault();
            $('#dialog-confirm').modal('show');
        });

        $('#confirm-reservation-cancelation').on('click', function(e){
            e.preventDefault();
            $('#dialog-confirm').modal('hide');

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason22').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_decline_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });
        });

        /* ------------------------------------------------------------------------ */
        /*  Decline Reservation Hourly
         /* ------------------------------------------------------------------------ */
        $('#decline_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_decline_hourly_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        $("#decline-reservation-btn").click(function(){
            console.log('..this that..');
            $('html, body').animate({
                scrollTop: $('#decline-reservation').offset().top + 150
            }, 'slow');
        });

        $("#cancel-reservation-btn").click(function(){
            console.log('..this that..');

            $('html, body').animate({
                scrollTop: $('#cancel-reservation').offset().top + 150
            }, 'slow');
        });

        /* ------------------------------------------------------------------------ */
        /*  Cancel Reservation
         /* ------------------------------------------------------------------------ */
        $('#cancelled').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_cancelled_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function() {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(
                            '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                '<button type="button" class="close" data-hide="alert" aria-label="Close">' +
                                    '<i class="homey-icon homey-icon-close"></i>' +
                                '</button>' +
                                data.message +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    // Improved, will NOT eval xhr.responseText.
                    parentDIV.find('.alert').remove();
                    var errMsg = "An unexpected error occurred.";
                    // Try to parse valid JSON, fallback to display message.
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data && data.message) {
                            errMsg = data.message;
                        }
                    } catch (parseError) {
                        // If the response is not JSON (e.g., HTML from a 500 error), show default message plus status.
                        if(xhr.status === 500) {
                            errMsg = "Internal server error (500).";
                        } else if(xhr.responseText && xhr.responseText.trim().charAt(0) === '<') {
                            errMsg = "Server returned an unexpected HTML response.";
                        } else if(xhr.status && xhr.status !== 200){
                            errMsg = "Server error: " + xhr.status;
                        }
                    }
                    
                    parentDIV.find('.dashboard-area').prepend(
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-hide="alert" aria-label="Close">' +
                        '<i class="homey-icon homey-icon-close"></i></button>' +
                        errMsg +
                        '</div>'
                    );
                    // For dev diagnostic, still log the raw error to console
                    console.error('AJAX Error:', status, error, xhr.responseText);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }
            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Cancel Hourly Reservation
         /* ------------------------------------------------------------------------ */
        $('#cancelled_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_cancelled_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        // =============== ********** ================ //
        // experience related new javascript code

        /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
        /* ------------------------------------------------------------------------ */
        $('.confirm-exp-reservation').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $this.data('reservation_id');
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_confirm_exp_reservation',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
        $('#guest_paid_exp_button').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var parentDIV = $this.parents('.user-dashboard-right');
            var reservation_id = $('#reservation_id').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_guest_made_payment',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
        $('#exp-decline').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason22').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_exp_decline_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Decline Reservation Hourly
         /* ------------------------------------------------------------------------ */
        $('#exp_decline_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_exp_decline_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
        $("#decline-exp-reservation-btn").click(function(){
            $('html, body').animate({
                scrollTop: $('#decline-exp-reservation').offset().top + 150
            }, 'slow');
        });

        $("#cancel-exp-reservation-btn").click(function(){
            $('html, body').animate({
                scrollTop: $('#cancel-exp-reservation').offset().top + 150
            }, 'slow');
        });

        /* ------------------------------------------------------------------------ */
        /*  Cancel Reservation
         /* ------------------------------------------------------------------------ */
        $('#exp-cancelled').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_exp_cancelled_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        /* ------------------------------------------------------------------------ */
        /*  Cancel Hourly Reservation
         /* ------------------------------------------------------------------------ */
        $('#exp_cancelled_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_exp_cancelled_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="homey-icon homey-icon-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        // end of experience related new javascript code
        // =============== ********** ================ //

        var homey_booking_paypal_payment = function($this, reservation_id, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_booking_paypal_payment',
                    'reservation_id': reservation_id,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#homey_notify').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_exp_booking_paypal_payment = function($this, reservation_id, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_exp_booking_paypal_payment',
                    'reservation_id': reservation_id,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#homey_notify').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_hourly_booking_paypal_payment = function($this, reservation_id, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_hourly_booking_paypal_payment',
                    'reservation_id': reservation_id,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#homey_notify').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('#make_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservation_id').val();
            var security = $('#checkout-security').val();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_booking_paypal_payment($this, reservation_id, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
                $('#homey_notify').html('');
            }
            return;
        });

        $('#make_exp_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservation_id').val();
            var security = $('#checkout-security').val();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_exp_booking_paypal_payment($this, reservation_id, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
                $('#homey_notify').html('');
            }
            return;
        });

        $('#make_hourly_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservation_id').val();
            var security = $('#checkout-security').val();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_hourly_booking_paypal_payment($this, reservation_id, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
                $('#homey_notify').html('');
            }
            return;

        });

        var homey_instance_booking_paypal_payment = function($this, check_in, check_out, guests, extra_options, listing_id, renter_message, security, reservor_name, reservor_phone, adult_guest, child_guest, reservation_no_userHash) {
            //alert(extra_options); return;
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_instance_booking_paypal_payment',
                    'check_in': check_in,
                    'check_out': check_out,
                    'guests': guests,
                    'adult_guest': adult_guest,
                    'child_guest': child_guest,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'security': security,
                    'reservor_name': reservor_name,
                    'reservor_phone': reservor_phone,
                    'reservation_no_userHash': reservation_no_userHash,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#instance_noti').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_instance_exp_booking_paypal_payment = function($this, check_in, guests, extra_options, experience_id, renter_message, security, reservor_name, reservor_phone) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_instance_exp_booking_paypal_payment',
                    'check_in': check_in,
                    'guests': guests,
                    'extra_options': extra_options,
                    'experience_id': experience_id,
                    'renter_message': renter_message,
                    'security': security,
                    'reservor_name': reservor_name,
                    'reservor_phone': reservor_phone,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#instance_noti').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_hourly_instance_booking_paypal_payment = function($this, check_in, check_in_hour, check_out_hour, start_hour, end_hour, guests, extra_options, listing_id, renter_message, security, reservation_no_userHash) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_hourly_instance_booking_paypal_payment',
                    'check_in': check_in,
                    'check_in_hour': check_in_hour,
                    'check_out_hour': check_out_hour,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'security': security,
                    'reservation_no_userHash': reservation_no_userHash,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    $('#instance_noti').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('#make_instance_booking_payment').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var check_in   = $('#check_in_date').val();
            //check_in = homey_convert_date(check_in);

            var check_out  = $('#check_out_date').val();
            //check_out = homey_convert_date(check_out);

            var guests     = $('#guests').val();
            var adult_guest     = $("input[name='adult_guest']").val();
            var child_guest     = $("input[name='child_guest']").val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();
            var reservor_name   = $('#first-name').val() +' '+ $('#last-name').val();
            var reservor_phone   = $('#phone').val();
            var reservation_no_userHash   = $('#reservation_no_userHash').val();

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            $('#instance_noti').empty();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_instance_booking_paypal_payment($this, check_in, check_out, guests, extra_options, listing_id, renter_message, security, reservor_name, reservor_phone, adult_guest, child_guest, reservation_no_userHash);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('form');
                hform.find('.homey_stripe_simple button').trigger("click");

            }
            return;
        });

        $('#make_instance_exp_booking_payment').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var check_in   = $('#check_in_date').val();
            //check_in = homey_convert_date(check_in);

            var guests     = $('#guests').val();
            var experience_id = $('#experience_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();
            var reservor_name   = $('#first-name').val() +' '+ $('#last-name').val();
            var reservor_phone   = $('#phone').val();

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            $('#instance_noti').empty();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_instance_exp_booking_paypal_payment($this, check_in, guests, extra_options, experience_id, renter_message, security, reservor_name, reservor_phone);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('form');
                hform.find('.homey_stripe_simple button').trigger("click");

            }
            return;
        });

        $('#make_hourly_instance_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in   = $('#check_in_date').val();

            var check_in_hour  = $('#check_in_hour').val();
            var check_out_hour  = $('#check_out_hour').val();
            var start_hour  = $('#start_hour').val();
            var end_hour  = $('#end_hour').val();
            var guests     = $('#guests').val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();
            var reservation_no_userHash   = $('#reservation_no_userHash').val();

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            $('#instance_noti').empty();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }

            if(payment_gateway === 'paypal') {
                homey_hourly_instance_booking_paypal_payment($this, check_in, check_in_hour, check_out_hour, start_hour, end_hour, guests, extra_options, listing_id, renter_message, security, reservation_no_userHash);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('form');
                hform.find('.homey_stripe_simple button').trigger("click");

            }
            return;

        });

        // this is testing for login
        var is_no_login_user_reg = $(".homey-booking-block-body-1").find('#email').val();

        if( typeof is_no_login_user_reg != 'undefined')
        {
            if( is_no_login_user_reg != '')
            {
                $('.homey-booking-block-title-2').removeClass('inactive mb-0');
                $('.homey-booking-block-body-2').slideDown('slow');

                $('.homey-booking-block-title-1').addClass('mb-0');
                $('.homey-booking-block-body-1').slideUp('slow');
                $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').removeClass('hidden');
                $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').show();

                var renter_message = $('#renter_message').val();
                $('#guest_message').val(renter_message);
            }
        }

        var first_name = $('#first-name').val();
        var last_name = $('#last-name').val();
        var phone = $('#phone').val();
        var renter_message = $('#renter_message').val();

        if(first_name != '' && last_name != '' && phone != '' && 1==2)
        {
            $('.homey-booking-block-title-2').removeClass('inactive mb-0');
            $('.homey-booking-block-body-2').slideDown('slow');

            $('.homey-booking-block-title-1').addClass('mb-0');
            $('.homey-booking-block-body-1').slideUp('slow');
            $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').removeClass('hidden');
            $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').show();
            $('#guest_message').val(renter_message);
        }

        $('button.homey-booking-step-1').on('click', function(e){
            e.preventDefault();
            var $this = $(this);

            var first_name = $('#first-name').val();
            var last_name = $('#last-name').val();

            var click_to_agreement = $(document).find("input[name='click_to_agreement']:checked").val();
            $('.homey-booking-block-body-1 .continue-block-button p.error').remove();

            if(click_to_agreement != undefined) {

                var phone = $('#phone').val();
                var reservation_no_userHash = '';

                if (typeof $("#reservation_no_userHash") !== 'undefined') {
                    reservation_no_userHash = $('#reservation_no_userHash').val();
                }

                var renter_message = $('#renter_message').val();
                // check if experience action needed or listing action?
                var action_name = 'homey_instance_step_1';
                if( $('#is-experience-step-1').length > 0){
                    action_name = 'homey_exp_instance_step_1';
                }

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': action_name,
                        'first_name': first_name,
                        'last_name': last_name,
                        'phone': phone,
                        'reservation_no_userHash': reservation_no_userHash,
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $('.homey-booking-block-body-1 .continue-block-button p.error').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {
                        if( data.success ) {
                            $('.homey-booking-block-title-2').removeClass('inactive mb-0');
                            $('.homey-booking-block-body-2').slideDown('slow');

                            $('.homey-booking-block-title-1').addClass('mb-0');
                            $('.homey-booking-block-body-1').slideUp('slow');
                            $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').removeClass('hidden');
                            $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').show();
                            $('#guest_message').val(renter_message);
                        } else {
                            $('.homey-booking-block-body-1 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ data.message +'</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });
            } else {
                $('.homey-booking-block-body-1 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ agree_term_text +'</p>');
            }
        });

        $('button.homey-booking-step-2').on('click', function(e){
            e.preventDefault();
            var $this = $(this);

            if(document.getElementById('stripe_cardholder_name') != null){
                document.getElementById('stripe_cardholder_name').value = "";
                document.getElementById('stripe_cardholder_email').value = "";

                document.getElementById('stripe_cardholder_name').setAttribute('autocomplete', 'off');
                document.getElementById('stripe_cardholder_email').setAttribute('autocomplete', 'off');

            }

            var agreement = $("input[name='agreement']:checked").val();

            $('.homey-booking-block-body-2 .continue-block-button p.error').remove();

            if(agreement != undefined) {
                //per night booking data
                var dataToCall = {};
                var security = jQuery("#checkout-security").val();

                var dataToCall = {};
                //per night booking data
                if(jQuery("#check_in_hour").length > 0){
                    var check_in_date = jQuery("#check_in_date").val();
                    var start_hour = jQuery("#start_hour").val();
                    var end_hour = jQuery("#end_hour").val();
                    var listing_id = jQuery("#listing_id").val();

                    jQuery.ajax({
                        type: 'post',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'check_booking_availability_on_hour_change',
                            'check_in_date': check_in_date,
                            'start_hour': start_hour,
                            'end_hour': end_hour,
                            'listing_id': listing_id,
                            'security': security,
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $('.homey-booking-block-body-2 .continue-block-button p.error').remove();
                            $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                        },
                        success: function(data) {
                            if( data.success ) {
                                console.log('dates are available checking on last step');
                                $('.homey-booking-block-title-3').removeClass('inactive mb-0');
                                $('.homey-booking-block-body-3').slideDown('slow');

                                $('.homey-booking-block-title-2').addClass('mb-0');
                                $('.homey-booking-block-body-2').slideUp('slow');
                                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').removeClass('hidden');
                                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').show();

                            }else{
                                $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ already_booked_text +'</p>');
                                window.history.back();
                            }
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }
                    });
                }else {
                    var check_in_date = jQuery("#check_in_date").val();
                    var check_out_date = jQuery("#check_out_date").val();
                    var listing_id = jQuery("#listing_id").val();

                    jQuery.ajax({
                        type: 'post',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'check_booking_availability_on_date_change',
                            'check_in_date': check_in_date,
                            'check_out_date': check_out_date,
                            'listing_id': listing_id,
                            'security': security
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $('.homey-booking-block-body-2 .continue-block-button p.error').remove();
                            $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                        },
                        success: function(data) {
                            if( data.success ) {
                                console.log('dates are available checking on last step');
                                $('.homey-booking-block-title-3').removeClass('inactive mb-0');
                                $('.homey-booking-block-body-3').slideDown('slow');

                                $('.homey-booking-block-title-2').addClass('mb-0');
                                $('.homey-booking-block-body-2').slideUp('slow');
                                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').removeClass('hidden');
                                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').show();

                            }else{
                                $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ already_booked_text +'</p>');
                                window.history.back();
                            }
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }
                    });

                }

            } else {
                $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ agree_term_text +'</p>');
            }

        });

        $('button.homey-booking-exp-step-2').on('click', function(e){
            e.preventDefault();
            var $this = $(this);

            if(document.getElementById('stripe_cardholder_name') != null){
                document.getElementById('stripe_cardholder_name').value = "";
                document.getElementById('stripe_cardholder_email').value = "";

                document.getElementById('stripe_cardholder_name').setAttribute('autocomplete', 'off');
                document.getElementById('stripe_cardholder_email').setAttribute('autocomplete', 'off');

            }

            var agreement = $("input[name='agreement']:checked").val();

            $('.homey-booking-block-body-2 .continue-block-button p.error').remove();

            if(agreement != undefined) {
                //per night booking data
                var dataToCall = {};
                var security = jQuery("#checkout-security").val();

                var check_in_date = jQuery("#check_in_date").val();
                var experience_id = jQuery("#experience_id").val();

                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'check_exp_availability_on_date_change',
                        'check_in_date': check_in_date,
                        'experience_id': experience_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $('.homey-booking-block-body-2 .continue-block-button p.error').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {
                        if( data.success ) {
                            console.log('exp dates are available checking on last step');
                            $('.homey-booking-block-title-3').removeClass('inactive mb-0');
                            $('.homey-booking-block-body-3').slideDown('slow');

                            $('.homey-booking-block-title-2').addClass('mb-0');
                            $('.homey-booking-block-body-2').slideUp('slow');
                            $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').removeClass('hidden');
                            $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').show();

                        }else{
                            $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ already_booked_text +'</p>');
                            window.history.back();
                        }
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }
                });
            } else {
                $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ agree_term_text +'</p>');
            }

        });

        $('.homey-booking-block-title-1 .edit-booking-form').on('click', function(e){
            e.preventDefault();

            $('.homey-booking-block-title-2, .homey-booking-block-title-3').addClass('mb-0');
            $('.homey-booking-block-body-2, .homey-booking-block-body-3').slideUp('slow');

            $('.homey-booking-block-title-1').removeClass('mb-0');
            $('.homey-booking-block-body-1').slideDown('slow');

        });

        $('.homey-booking-block-title-2 .edit-booking-form').on('click', function(e){
            e.preventDefault();

            $('.homey-booking-block-title-1, .homey-booking-block-title-3').addClass('mb-0');
            $('.homey-booking-block-body-1, .homey-booking-block-body-3').slideUp('slow');

            $('.homey-booking-block-title-2').removeClass('mb-0');
            $('.homey-booking-block-body-2').slideDown('slow');

        });

        /*--------------------------------------------------------------------------
         *  Contact listing host
         * -------------------------------------------------------------------------*/
        $( '.contact_listing_host').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $host_contact_wrap = $this.parents( '.host-contact-wrap' );
            var $form = $this.parents( 'form' );
            var $messages = $host_contact_wrap.find('.homey_contact_messages');

            $.ajax({
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(response) {
                    if( response.success ) {
                        $messages.empty().append(response.msg);
                        $form.find('input').val('');
                        $form.find('textarea').val('');
                    } else {
                        $messages.empty().append(response.msg);
                        $this.children('i').removeClass(process_loader_spinner);
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });
        });

        /*--------------------------------------------------------------------------
        *  Contact experience  host
        * -------------------------------------------------------------------------*/
        $( '.contact_experience_host').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $host_contact_wrap = $this.parents( '.host-contact-wrap' );
            var $form = $this.parents( 'form' );
            var $messages = $host_contact_wrap.find('.homey_contact_messages');

            $.ajax({
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function(response) {
                    if( response.success ) {
                        $messages.empty().append(response.msg);
                        $form.find('input').val('');
                        $form.find('textarea').val('');
                    } else {
                        $messages.empty().append(response.msg);
                        $this.children('i').removeClass(process_loader_spinner);
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });
        });

        /*--------------------------------------------------------------------------
         *   Contact host form on host detail page
         * -------------------------------------------------------------------------*/
        $('#host_detail_contact').on('click', function(e) {
            e.preventDefault();
            var current_element = $(this);
            var $this = $(this);
            var $form = $this.parents( 'form' );

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    current_element.children('i').remove();
                    current_element.prepend('<i class=" '+process_loader_spinner+'"></i>');
                },
                success: function( res ) {
                    current_element.children('i').removeClass(process_loader_spinner);
                    if( res.success ) {
                        $('#form_messages').empty().append(res.msg);
                        current_element.children('i').addClass(success_icon);
                    } else {
                        $('#form_messages').empty().append(res.msg);
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }

            });
        });

        /*--------------------------------------------------------------------------
        *   Print Property
        * -------------------------------------------------------------------------*/
        if( $('.homey-print').length > 0 ) {
            $('.homey-print').on('click', function (e) {
                e.preventDefault();
                var listingID, printWindow;

                listingID = $(this).attr('data-listing-id');

                printWindow = window.open('', 'Print Me', 'width=850 ,height=842');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_create_print',
                        'listing_id': listingID,
                    },
                    success: function (data) {
                        printWindow.document.write(data);
                        printWindow.document.close();
                        // printWindow.print();
                        printWindow.focus();
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });
            });
        }

        /*--------------------------------------------------------------------------
         *   Print Experiences
         * -------------------------------------------------------------------------*/
        if( $('.homey-print-experience').length > 0 ) {
            $('.homey-print-experience').on('click', function (e) {
                e.preventDefault();
                var experienceID, printWindow;

                experienceID = $(this).attr('data-experience-id');

                printWindow = window.open('', 'Print Me', 'width=850 ,height=842');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_create_experience_print',
                        'experience_id': experienceID,
                    },
                    success: function (data) {
                        printWindow.document.write(data);
                        printWindow.document.close();
                        // printWindow.print();
                        printWindow.focus();
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });
            });
        }

        /* ------------------------------------------------------------------------ */
        /*  Homey login and regsiter
         /* ------------------------------------------------------------------------ */
        $('.homey_login_button').on('click', function(e){
            e.preventDefault();
            var current = $(this);
            homey_login( current );
        });

        $('.homey-register-button').on('click', function(e){
            e.preventDefault();
            var current = $(this);
            var iti_selected_flag = $('.iti__selected-flag').first().attr('title');
            // Use a regular expression to extract the country code and number
            var matches = '';
            var countryCode = '';

            if(typeof iti_selected_flag != 'undefined'){
                matches = iti_selected_flag.match(/:\s\+(\d+)/);
            }

            // Check if the matches array has the desired value
            if (matches && matches.length > 1) {
                countryCode = '+' + matches[1];
            }
            // alert(iti_selected_flag);

            $('#iti__selected_flag').val(countryCode);
            homey_register( current );
        });

        var homey_login = function( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');
            var $reservation_login_required = $('#reservation_login_required').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');

                    jQuery(current).children('i').remove();
                    jQuery(current).prepend('<i class=" homey-icon homey-icon-loading-half fa-spinner"></i>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $messages.empty().append('<p class="success text-success"><i class="homey-icon homey-icon-check-circle-1"></i> '+ response.msg +'</p>');

                        var wp_http_referer_value = jQuery(document).find("input[name='_wp_http_referer']").val();

                        if($reservation_login_required == 1 ){
                            window.location.href = wp_http_referer_value;
                            return false;
                        }

                        if(wp_http_referer_value != '/' && login_redirect_type == 'same_page'){
                            if(wp_http_referer_value.indexOf("?") !== -1){
                                window.location.href = wp_http_referer_value + '&n=' + new Date().getTime();
                            }else{
                                window.location.href = wp_http_referer_value + '?n=' + new Date().getTime();
                            }
                            return false;
                        }

                        if( login_redirect_type == 'same_page' ) {
                            window.location.reload(true);
                            return false;
                        } else {
                            if(wp_http_referer_value.indexOf("?") !== -1) {
                                if(login_redirect.indexOf("?") !== -1) {
                                    window.location.href = login_redirect + '&n=' + new Date().getTime();
                                }else{
                                    window.location.href = login_redirect + '?n=' + new Date().getTime();
                                }
                            }else{
                                window.location.href = login_redirect + '?n=' + new Date().getTime();
                            }
                            return false;
                        }

                    } else {
                        jQuery(current).children('i').remove();

                        $messages.empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ response.msg +'</p>');
                    }

                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }

                },
                error: function(xhr, status, error) {
                    jQuery(current).children('i').remove();

                    $messages.empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i>'+HOMEY_ajax_vars.homey_login_register_msg_text+'</p>');
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            })

        } // end homey_login

        var homey_register = function ( currnt ) {

            var $form = currnt.parents('form');
            var $messages = $('.homey_register_messages');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');

                    jQuery(currnt).children('i').remove();
                    jQuery(currnt).prepend('<i class=" homey-icon homey-icon-loading-half fa-spinner"></i>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $messages.empty().append('<p class="success text-success"><i class="homey-icon homey-icon-check-circle-1"></i> '+ response.msg +'</p>');
                        $('.homey_login_messages').empty().append('<p class="success text-success"><i class="homey-icon homey-icon-check-circle-1"></i> '+ response.msg +'</p>');
                        $('#modal-register').modal('hide');
                        $('#modal-login').modal('show');
                    } else {
                        jQuery(currnt).children('i').remove();

                        $messages.empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ response.msg +'</p>');
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    jQuery(currnt).children('i').remove();

                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /* ------------------------------------------------------------------------ */
        /*  Reset Password
         /* ------------------------------------------------------------------------ */
        $( '#homey_forgetpass').on('click', function(e){
            e.preventDefault();
            var user_login = $('#user_login_forgot').val(),
                security    = $('#homey_resetpassword_security').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_reset_password',
                    'user_login': user_login,
                    'security': security
                },
                beforeSend: function () {
                    $('#homey_msg_reset').empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $('#homey_msg_reset').empty().append('<p class="success text-success"><i class="homey-icon homey-icon-check-circle-1"></i> '+ response.msg +'</p>');
                    } else {
                        $('#homey_msg_reset').empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ response.msg +'</p>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });

        });


        if( $('#homey_reset_password').length > 0 ) {
            $('#homey_reset_password').on('click', function(e) {
                e.preventDefault();

                var $this = $(this);
                var rg_login = $('input[name="rp_login"]').val();
                var rp_key = $('input[name="rp_key"]').val();
                var new_pass = $('input[name="new_password"]').val();
                var security = $('input[name="homey_resetpassword_security"]').val();

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_reset_password_2',
                        'rq_login': rg_login,
                        'password': new_pass,
                        'rp_key': rp_key,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class=" '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {
                        if( data.success ) {
                            $('#password_reset_msgs, .homey_login_messages').empty().append('<p class="success text-success"><i class="homey-icon homey-icon-check-circle-1"></i> '+ data.msg +'</p>');
                            $('#new_password').val('');
                            $('#modal-login').modal('show');
                        } else {
                            $('#password_reset_msgs').empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i> '+ data.msg +'</p>');
                        }
                    },
                    error: function(errorThrown) {

                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });

            } );
        }

        /*--------------------------------------------------------------------------
         *   Facebook login
         * -------------------------------------------------------------------------*/
        $('.homey-facebook-login').on('click', function() {
            var current = $(this);
            homey_login_via_facebook( current );
        });

        var homey_login_via_facebook = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_facebook_login_oauth'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) {
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /*--------------------------------------------------------------------------
         *  Social Logins
         * -------------------------------------------------------------------------*/
        $('.homey-yahoo-login').on('click', function () {
            var current = $(this);
            homey_login_via_yahoo( current );
        });

        var homey_login_via_yahoo = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_yahoo_login'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) {
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('.homey-google-login').on('click', function () {
            var current = $(this);
            homey_login_via_google( current );
        });

        var homey_login_via_google = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_google_login_oauth'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) {
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /*
         * Property Message Notifications
         * -----------------------------*/
        var homey_message_notifications = function () {

            $.ajax({
                url: ajaxurl,
                data: {
                    action : 'homey_chcek_messages_notifications'
                },
                method: "POST",
                dataType: "JSON",

                beforeSend: function( ) {
                    // code here...
                },
                success: function(response) {
                    if( response.success ) {
                        if ( response.notification ) {
                            $( '.user-alert' ).show();
                            $( '.msg-alert' ).show();
                        } else {
                            $( '.user-alert' ).hide();
                            $( '.msg-alert' ).hide();
                        }
                    }
                }
            });

        };

        /*
         * Property Booking Notifications
         * -----------------------------*/
        var homey_booking_notification = function () {

            $.ajax({
                url: ajaxurl,
                data: {
                    action : 'homey_booking_notification'
                },
                method: "POST",
                dataType: "JSON",

                beforeSend: function( ) {
                    // code here...
                },
                success: function(response) {
                    if( response.success ) {
                        if ( response.notification != 0 ) {
                            $( '.new-booking-alert' ).show();
                        } else {
                            $( '.new-booking-alert' ).hide();
                        }
                    }
                }
            });

        };

        $( document ).ready(function() {
            homey_message_notifications();
            if( parseInt( userID, 10 ) != 0 ) {
                setInterval(function() { homey_message_notifications(); }, 60000);
            }

            homey_booking_notification();
            if( parseInt( userID, 10 ) != 0 ) {
                setInterval(function() { homey_booking_notification(); }, 60000);
            }
        });


        $('.btn_extra_expense').on('click', function(e) {
            e.preventDefault();
            var reservation_id = $('#resrv_id').val();

        });


        /* ------------------------------------------------------------------------ */
        /* WooCommerce Pay
        /* ------------------------------------------------------------------------ */
        $('.homey-woocommerce-featured-pay').on('click', function(e) {
            e.preventDefault();

            let listing_id = $(this).data('listid');
            let is_featured = $(this).data('featured');

            homey_processing_modal( processing_text );

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'homey_featured_woo_pay',
                    'listing_id': listing_id,
                    'is_featured': is_featured,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?listing_id='+listing_id+'&is_featured='+is_featured;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        /* ------------------------------------------------------------------------ */
        /* WooCommerce Experience Featured Pay
        /* ------------------------------------------------------------------------ */
        $('.homey-woocommerce-featured-pay-exp').on('click', function(e) {
            e.preventDefault();

            let experience_id = $(this).data('experienceId');
            let is_featured = $(this).data('featured');

            homey_processing_modal( processing_text );

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'homey_featured_woo_pay_exp',
                    'experience_id': experience_id,
                    'is_featured': is_featured,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?experience_id='+experience_id+'&is_featured='+is_featured;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        /* ------------------------------------------------------------------------ */
        /* WooCommerce Reservation Pay
        /* ------------------------------------------------------------------------ */
        $('.homey-woo-reservation-pay').on('click', function(e) {
            e.preventDefault();

            let reservation_id = $(this).data('reservation_id');

            homey_processing_modal( processing_text );

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'homey_reservation_woo_pay',
                    'reservation_id': reservation_id,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?reservation_id='+reservation_id;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        $('.homey-woo-exp-reservation-pay').on('click', function(e) {
            e.preventDefault();

            let reservation_id = $(this).data('reservation_id');

            homey_processing_modal( processing_text );

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'homey_reservation_woo_exp_pay',
                    'reservation_id': reservation_id,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?reservation_id='+reservation_id;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        $('#make_woocommerce_instant_experience_payment').on('click', function(e) {
            e.preventDefault();

            homey_processing_modal( processing_text );

            var $this = $(this);
            var check_in   = $('#check_in_date').val();

            var guests     = $('#guests').val();
            var experience_id = $('#experience_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();

            var reservation_no_userHash = '';
            if (typeof $("#reservation_no_userHash") !== 'undefined') {
                reservation_no_userHash = $('#reservation_no_userHash').val();
            }

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            var action_name_for_wp_action = 'homey_instant_reservation_woo_exp_pay';
            if (typeof reservation_no_userHash != 'undefined' && reservation_no_userHash > 0) {
                action_name_for_wp_action = 'hm_no_login_homey_instant_reservation_exp_woo_pay';
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': action_name_for_wp_action,
                    'check_in': check_in,
                    'guests': guests,
                    'extra_options': extra_options,
                    'experience_id': experience_id,
                    'renter_message': renter_message,
                    'reservation_no_userHash': reservation_no_userHash,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?check_in='+check_in+'&guests='+guests+'&extra_options[]='+extra_options+'&experience_id='+experience_id+'&renter_message='+renter_message;
                        console.log(urlWithGetVars);
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        $('#make_woocommerce_instant_booking_payment').on('click', function(e) {
            e.preventDefault();

            homey_processing_modal( processing_text );

            var $this = $(this);
            var check_in   = $('#check_in_date').val();

            var check_out  = $('#check_out_date').val();

            var guests     = $('#guests').val();
            var adult_guest     = $('#adult_guest').val();
            var child_guest     = $('#child_guest').val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();

            var reservation_no_userHash = '';
            if (typeof $("#reservation_no_userHash") !== 'undefined') {
                reservation_no_userHash = $('#reservation_no_userHash').val();
            }

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            var action_name_for_wp_action = 'homey_instant_reservation_woo_pay';
            if (typeof reservation_no_userHash != 'undefined' && reservation_no_userHash > 0) {
                action_name_for_wp_action = 'hm_no_login_homey_instant_reservation_woo_pay';
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': action_name_for_wp_action,
                    'check_in': check_in,
                    'check_out': check_out,
                    'guests': guests,
                    'adult_guest': adult_guest,
                    'child_guest': child_guest,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'reservation_no_userHash': reservation_no_userHash,
                },
                success: function(data) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?check_in='+check_in+'&check_out='+check_out+'&guests='+guests+'&extra_options[]='+extra_options+'&listing_id='+listing_id+'&renter_message='+renter_message;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(errorThrown) {

                }
            }); // $.ajax

        });

        $('#make_hourly_woocommerce_instant_booking_payment').on('click', function(e){
            e.preventDefault();

            homey_processing_modal( processing_text );

            var $this = $(this);
            var check_in   = $('#check_in_date').val();

            var check_in_hour  = $('#check_in_hour').val();
            var check_out_hour  = $('#check_out_hour').val();
            var start_hour  = $('#start_hour').val();
            var end_hour  = $('#end_hour').val();
            var guests     = $('#guests').val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();

            var extra_options = [];
            var temp_opt;
            $('.homey_extra_price').each(function() {
                var extra_name = $(this).data('name');
                var extra_price = $(this).data('price');
                var extra_type = $(this).data('type');
                temp_opt    =   '';
                temp_opt    =   extra_name;
                temp_opt    =   temp_opt + '|' + extra_price;
                temp_opt    =   temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            });

            $('#instance_noti').empty();

            var reservation_no_userHash = $('#reservation_no_userHash').val();

            var action_name_for_wp_action = 'homey_instant_hourly_reservation_woo_pay';
            if (typeof reservation_no_userHash != 'undefined' && reservation_no_userHash > 0) {
                action_name_for_wp_action = 'hm_no_login_instant_hourly_reservation_woo_pay';
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': action_name_for_wp_action,
                    'check_in': check_in,
                    'check_in_hour': check_in_hour,
                    'check_out_hour': check_out_hour,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'extra_options': extra_options,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'reservation_no_userHash': reservation_no_userHash,
                    'security': security,
                },

                success: function( data ) {
                    if ( data.success != false ) {
                        var urlWithGetVars = HOMEY_ajax_vars.woo_checkout_url+'?start_hour='+start_hour+'&end_hour='+end_hour+'&check_in='+check_in+'&check_in_hour='+check_in_hour+'&check_out_hour='+check_out_hour+'&guests='+guests+'&extra_options[]='+extra_options+'&listing_id='+listing_id+'&renter_message='+renter_message;
                        window.location.href = urlWithGetVars;
                    } else {
                        $('#homey_modal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });



        });

        if($("#has_social_account").data('hasSocialAccount') > 0){
            $('#modal-login').modal('show');
            $(".homey_login_messages").empty().append('<p class="error text-danger"><i class="homey-icon homey-icon-close"></i>'+already_login_text+'</p>');
        }



    }// typeof HOMEY_ajax_vars

    function save_booking_details(arrive, depart, guest, guest_message, adult_guest, child_guest, new_reser_request_user_email){
        var currentRefererUrl = $("input[name='_wp_http_referer']").val();
        if(typeof currentRefererUrl != "undefined"){
            var newRefereUrl = currentRefererUrl.split('?')[0]+'?arrive='+arrive+'&depart='+depart+'&guest='+guest+'&adult_guest='+adult_guest+'&child_guest='+child_guest+'&guest_message='+guest_message+'&new_reser_request_user_email='+new_reser_request_user_email;
            $("input[name='_wp_http_referer']").val(newRefereUrl);
        }
    }

    function save_hourl_booking_details(arrive, start, end, guest, guest_message, adult_guest, child_guest ){
        var currentRefererUrl = $("input[name='_wp_http_referer']").val();
        if(typeof currentRefererUrl != "undefined"){
            var newRefereUrl = currentRefererUrl.split('?')[0]+'?arrive='+arrive+'&start='+start+'&end='+end+'&guest='+guest+'&adult_guest='+adult_guest+'&child_guest='+child_guest+'&guest_message='+guest_message;
            $("input[name='_wp_http_referer']").val(newRefereUrl);
        }
    }

    function save_exp_booking_details(arrive, guest, guest_message, adult_guest, child_guest ){
        var currentRefererUrl = $("input[name='_wp_http_referer']").val();
        if(typeof currentRefererUrl != "undefined"){
            var newRefereUrl = currentRefererUrl.split('?')[0]+'?arrive='+arrive+'&guest='+guest+'&adult_guest='+adult_guest+'&child_guest='+child_guest+'&guest_message='+guest_message;
            $("input[name='_wp_http_referer']").val(newRefereUrl);
        }
    }


// to complete auto fill the price zahid.k
    if(jQuery('body').hasClass("single-listing")){

        var extra_options = [];
        var temp_opt;

        jQuery('.homey_extra_price input').each(function() {

            if( (jQuery(this).is(":checked")) ) {
                var extra_name = jQuery(this).data('name');
                var extra_price = jQuery(this).data('price');
                var extra_type = jQuery(this).data('type');
                temp_opt = '';
                temp_opt = extra_name;
                temp_opt = temp_opt + '|' + extra_price;
                temp_opt = temp_opt + '|' + extra_type;
                extra_options.push(temp_opt);
            }

        });

        var check_in_date = jQuery('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = jQuery('select[name="start_hour"]').val();
        var end_hour = jQuery('select[name="end_hour"]').val();

        var guests = jQuery('input[name="guests"]').val();
        var listing_id = jQuery('#listing_id').val();
        var security = jQuery('#reservation-security').val();

        if(typeof start_hour != 'undefined' && typeof end_hour != 'undefined'){
            homey_calculate_hourly_booking_cost(check_in_date, start_hour, end_hour, guests, listing_id, security, extra_options);
        }
    }

    jQuery("#place_order").click(function(){
        jQuery('html, body').animate({
            scrollTop: jQuery('.woocommerce-info').offset().top
        }, 'slow');
    });
    // to complete auto fill the price zahid.k

    //apprval of pending listings
    jQuery(".approve_listing").click(function(){
        var listing_id = jQuery(this).data('approval-listing-id');
        jQuery(this).text('...');
        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'homey_approve_listing',
                'listing_id': listing_id
            },

            success: function( data ) {
                if ( data.success != false ) {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            }
        });
    });
    //end of apprval of pending listings

    //apprval of pending listings
    jQuery(".approve_experience").click(function(){
        var experience_id = jQuery(this).data('approval-experience-id');
        jQuery(this).text('...');
        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'homey_approve_experience',
                'experience_id': experience_id
            },

            success: function( data ) {
                if ( data.success != false ) {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
                console.log(err.Message);
            }
        });
    });
    //end of apprval of pending listings

    // payment gateway issue
    var payment_gateway = $(".homey_check_gateway:checked").val();
    $(".homey_check_gateway").on('click', function() {
        select_payment_gateway($(this).val());

    });

    function select_payment_gateway(payment_gateway) {
        if ( payment_gateway == 'stripe' ) {
            $('#without_stripe').hide();
            $('#stripe_main_wrap').show();
            $(".payment-buttons").css("display", "none");
        } else {
            $('#without_stripe').show();
            $('#stripe_main_wrap').hide();
            $(".payment-buttons").css("display", "block");
        }
    }

    if(typeof payment_gateway != 'undefined'){
        select_payment_gateway(payment_gateway);
    }
    // end of payment gateway issue


    function calendar_next_prev_child(main_div, acdiv, is_next, singleMonth) {

        $('.'+acdiv).on('click', function (e) {
            e.preventDefault();

            var next_prev_m = homey_calendar_months;
            var next_prev_m1 = homey_calendar_months-1;
            var next_prev_m2 = homey_calendar_months-2;

            if(singleMonth) {

                if(is_next) {
                    if (current_month < next_prev_m1) {
                        current_month = current_month + 1;
                    } else {
                        current_month = next_prev_m;
                    }
                } else {
                    if (current_month > 1) {
                        current_month = current_month - 1;
                    } else {
                        current_month = 1;
                    }
                }

                $('.'+main_div).hide();
                $('.'+main_div).each(function () {
                    var month   =   parseInt($(this).attr('data-month'), 10);;
                    if (month === current_month) {
                        $(this).fadeIn();
                    }
                });

            } else {

                console.log("No Its Not Single Month");

                if(is_next) {
                    if (current_month2 < next_prev_m2) {
                        current_month2 = current_month2 + 1;
                    } else {
                        current_month2 = next_prev_m1;
                    }
                } else {
                    if (current_month2 > 3) {
                        current_month2 = current_month2 - 1;
                    } else {
                        current_month2 = 2;
                    }
                }

                if(is_next) {
                    $('.'+main_div).hide();
                    $('.'+main_div).each(function () {
                        var month   =   parseInt($(this).attr('data-month'), 10);
                        if (month === current_month2 || month === current_month2+1) {
                            $(this).fadeIn();
                        }
                    });
                } else {
                    $('.'+main_div).hide();
                    $('.'+main_div).each(function () {
                        var month   =   parseInt($(this).attr('data-month'), 10);
                        if (month === current_month2 || month === current_month2-1) {
                            $(this).fadeIn();
                        }
                    });
                }
            }

            if(singleMonth) {
                
                if(current_month == next_prev_m) {
                    $(this).addClass('disabled');
                } else {
                    $('.homey-next-month, .experience-cal-next, .listing-cal-next, .search-cal-next').removeClass('disabled');
                }

                if(current_month == 1) {
                    $(this).addClass('disabled');
                } else {
                    $('.homey-prev-month, .prev, .listing-cal-prev, .search-cal-prev').removeClass('disabled');
                }
            } else {

                if(current_month2 == next_prev_m1) {
                    $(this).addClass('disabled');
                } else {
                    $('.homey-next-month, .experience-cal-next, .listing-cal-next, .search-cal-next').removeClass('disabled');
                }

                if(current_month2 == 2) {
                    $(this).addClass('disabled');
                } else {
                    $('.homey-prev-month, .experience-cal-prev, .listing-cal-prev, .search-cal-prev').removeClass('disabled');
                }
            }

        });
    }

}); // end document ready