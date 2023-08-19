jQuery(document).ready(function ($) {

    var swalFunc = function open_connect_popup() {
        Swal.fire({
            title: '',
            showClass: {
                popup: 'in'
            },
            html: jQuery('.wps-ic-connect-form').html(),
            width: 700,
            position: 'center',
            customClass: {
                container: 'in',
                popup: 'wps-ic-connect-popup'
            },
            //customClass:'wps-ic-connect-popup',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            onOpen: function () {


                var swal_container = $('.swal2-container');
                var form = $('#wps-ic-connect-form', swal_container);



                    var form_container = $('.wps-ic-form-container', swal_container);
                    var success_message = $('.wps-ic-success-message-container', swal_container);
                    var error_message_container = $('.wps-ic-error-message-container', swal_container);
                    var error_message_text = $('.wps-ic-error-message-container-text', swal_container);
                    var already_connected = $('.wps-ic-error-already-connected', swal_container);
                    var success_message_text = $('.wps-ic-success-message-container-text', swal_container);
                    var success_message_choice_text = $('.wps-ic-success-message-choice-container-text', swal_container);
                    var success_message_buttons = $('.wps-ic-success-message-choice-container-text a', swal_container);
                    var finishing = $('.wps-ic-finishing-container', swal_container);

                    var loader = $('.wps-ic-loading-container', swal_container);
                    var tests = $('.wps-ic-tests-container', swal_container);
                    var init = $('.wps-ic-init-container', swal_container);

                    $(already_connected).hide();
                    $(error_message_text).hide();
                    $(success_message_text).hide();
                    $(error_message_container).hide();
                    $(init, swal_container).hide();
                    $(form_container).hide();
                    $(loader).show();
                    $(tests).hide();

                    var apikey = $('input[name="apikey"]', form_container).val();

                    // Connect
                    $('.wps-ic-connect-inner').addClass('padded');
                    $(success_message).show();
                    $(success_message_choice_text).show();

                    $(loader).hide();
                    $(tests).hide();
            }
        });
    }

    //swalFunc();

    $('.cname-disabled').hover(function (e) {
        if (!$('.cname-container').hasClass('active-hover')) {
            $('.cname-container').addClass('active-hover shake-effect');
            setTimeout(function(){
                $('.cname-container').removeClass('active-hover shake-effect');
            },1500);
        }
        return false;
    });

    $('body').on('click', '.disabled-checkbox', function (e) {
        e.preventDefault();
        return false;
    });


    $('.setting-label', '.setting-option').hover(function (e) {
        var parent = $(this).parent();
        $('.setting-value.ic-custom-tooltip', parent).tooltipster('show');
    });

    $('.setting-label', '.setting-option').mouseleave(function (e) {
        var parent = $(this).parent();
        $('.setting-value.ic-custom-tooltip', parent).tooltipster('hide');
    });

    $('.setting-label', '.setting-option').on('click', function (e) {
        e.preventDefault();

        var parent = $(this).parent();
        $('label', parent).trigger('click');

        return false;
    });
    
    $('input[type="checkbox"]#css-toggle', '.wpc-checkbox').on('change', function(){
        var setting_name = $(this).data('setting_name');
        var value = $(this).is(':checked');

        if (value == true) {
            // ON
            $('a[data-value="html+css"]', '.wp-ic-select-box').trigger('click');
        } else {
            // OFF
            $('a[data-value="html"]', '.wp-ic-select-box').trigger('click');
        }
    });

    $('input[type="checkbox"]', '.wpc-checkbox').on('change', function(){
        var input = $(this);
        var setting_name = $(this).data('setting_name');
        var value = $(this).is(':checked');

        if (setting_name == 'fonts') {
            if (value == true) {
                // Set to checked
                $('#fonts-enabled,#fonts').attr('checked', 'checked');
            } else {
                $('#fonts-enabled,#fonts').removeAttr('checked');
            }
        }
    });


    $('body').on('click', '.close-toggle', function (e) {
        //var closeWhat = $(this).data('close-target');
        $(this).parent().fadeOut();
    });



    function CustomCnameClose() {
        var popup = $('.custom-cname-popup');
        var save = $('[name="save"]', popup);
        var loading = $('.cdn-popup-loading', popup);
        var content = $('.cdn-popup-content', popup);
        var top = $('.cdn-popup-top', popup);
        var steps = $('.custom-cdn-steps', popup);
        var step_1 = $('.custom-cdn-step-1', steps);
        var step_2 = $('.custom-cdn-step-2', steps);
        var step_1_retry = $('.custom-cdn-step-1-retry', steps);
        var step_2_img = $('.custom-cdn-step-2-img', steps);

        $(step_1).show();
        $(step_2).hide();
        $(step_1_retry).hide();
    }

    function CustomCname() {
        var popup = $('.swal2-container .custom-cname-popup');
        var popupData = $('.swal2-container .custom-cname-popup');
        var save = $('[name="save"]', popup);
        var cant_see = $('.btn-i-cant-see', popup);
        var loading = $('.cdn-popup-loading', popup);
        var content = $('.cdn-popup-content', popup);
        var top = $('.cdn-popup-top', popup);
        var steps = $('.custom-cdn-steps', popup);
        var step_1 = $('.custom-cdn-step-1', steps);
        var step_2 = $('.custom-cdn-step-2', steps);
        var step_2_img = $('.custom-cdn-step-2-img', steps);
        var step_1_retry = $('.custom-cdn-step-1-retry', steps);
        var configure = $('.setting-configure');
        var configured = $('.setting-configured');
        var cname_enabled = $('.cname-enabled');
        var cname_disabled = $('.cname-disabled');
        var label_enabled = $('.label-enabled');
        var label_disabled = $('.label-disabled');
        var cname_configured = $('.cname-configured');

        $(save).on('click', function (e) {
            e.preventDefault();
            $(top).hide();
            $(content).hide();
            $(loading).show();

            var cname_field = $('[name="custom-cdn"]', popupData).val();

            if (cname_field == '') {
                //wps-ic-mu-popup-empty-cname
                Swal.fire({
                    title: '', position: 'center', html: jQuery('.wps-ic-mu-popup-empty-cname').html(), width: 600, showCloseButton: true, showCancelButton: false, showConfirmButton: false, allowOutsideClick: true, customClass: {
                        container: 'no-padding-popup-bottom-bg switch-legacy-popup',
                    }, onOpen: function () {

                    }, onClose: function () {
                        //openConfigurePopup(popup_modal);
                    }
                });
                return false;
            }

            $('h4', loading).show();

            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_cname_add', cname: cname_field}, function (response) {
                $(top).show();
                $(step_1_retry).hide();
                $('h4', loading).hide();

                if (response.success) {
                        $(loading).hide();
                        $(content).show();

                        $(cname_disabled).hide();
                        $(label_disabled).hide();
                        $(cname_enabled).show();
                        $(label_enabled).show();
                        $(configure).hide();
                        $(configured).show();
                        $(step_1).hide();
                        $(step_2_img).attr('src', response.data.image);

                        setTimeout(function(){
                            $(step_2).show();
                            countdown = 6;

                            setInterval(function() {
                                countdown--;
                                if (countdown==0) {
                                    $('.btn-i-cant-see', step_2).html('I can\'t see the above image (' + countdown + ')');
                                } else {
                                    $('.btn-i-cant-see', step_2).html('I can\'t see the above image');
                                }
                            }, 1100);

                            $(cname_configured).html(response.data.configured).show();
                            $('.btn-close').on('click', function (e) {
                                e.preventDefault();
                                Swal.close();
                                return false;
                            });
                        }, 1000);
                }
                else {
                    $(loading).hide();
                    $(content).show();

                    $(cname_enabled).hide();
                    $(label_enabled).hide();
                    $(cname_configured).html('').hide();
                    $(cname_disabled).show();
                    $(label_disabled).show();
                    $(configure).show();
                    $(configured).hide();
                    $(step_1).show();

                    if (response.data == 'invalid-dns-prop') {
                        $('.custom-cdn-error-message', popup).html('<span class="icon-container close-toggle"><i class="icon-cancel"></i></span> Seems like DNS is not set correctly...');
                    } else if (response.data == 'dns-api-not-working') {
                        $('.custom-cdn-error-message', popup).html('<span class="icon-container close-toggle"><i class="icon-cancel"></i></span> Seems like DNS API is not working, please contact support...');
                    } else {
                        $('.custom-cdn-error-message', popup).html('<span class="icon-container close-toggle"><i class="icon-cancel"></i></span> This domain is invalid, please link a new domain...');
                    }

                    $('.wpc-dns-error-text', popup).show();
                    $('.custom-cdn-error-message', popup).show();
                    $(step_2).hide();
                    $(step_1_retry).hide();
                }
            });
        });

        $(cant_see).on('click', function (e) {
            e.preventDefault();

            var configure = $('.setting-configure');
            var configured = $('.setting-configured');

            $(configure).show();
            $(configured).hide();

            $(loading).show();
            $(content).hide();


            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_cname_retry'}, function (response) {
                $(top).hide();
                $(content).hide();
                $(loading).show();
                $('h4', loading).show();

                if (response.success) {
                    $(loading).hide();
                    $(content).show();

                    $(cname_disabled).hide();
                    $(label_disabled).hide();
                    $(cname_enabled).show();
                    $(label_enabled).show();
                    $(configure).hide();
                    $(configured).show();
                    $(step_1).hide();
                    $(step_2_img).attr('src', response.data.image);

                    setTimeout(function(){
                        $(step_2).show();
                        $(cname_configured).html(response.data.configured).show();
                        $('.btn-close').on('click', function (e) {
                            e.preventDefault();
                            Swal.close();
                            return false;
                        });
                    }, 1000);

                } else {
                    $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_remove_cname'}, function (response) {
                        if (response.success) {
                            $(loading).hide();
                            $(content).show();
                            $(cname_enabled).hide();
                            $(cname_disabled).show();
                            $(step_1_retry).show();
                            $(step_1).hide();
                            $(step_2).hide();
                        }
                    });
                }
            });

            return false;
        });
    }

    function RemoveCustomCname() {
        var popup = $('.remove-cname-popup');
        var popupData = $('.swal2-container .remove-cname-popup');
        var save = $('[name="save"]', popup);
        var cant_see = $('.btn-i-cant-see', popup);
        var loading = $('.cdn-popup-loading', popup);
        var content = $('.cdn-popup-content', popup);
        var top = $('.cdn-popup-top', popup);
        var steps = $('.custom-cdn-steps', popup);
        var step_1 = $('.custom-cdn-step-1', steps);
        var step_2 = $('.custom-cdn-step-2', steps);
        var step_2_img = $('.custom-cdn-step-2-img', steps);
        var step_1_retry = $('.custom-cdn-step-1-retry', steps);
        var configure = $('.setting-configure');
        var configured = $('.setting-configured');
        var cname_enabled = $('.cname-enabled');
        var cname_disabled = $('.cname-disabled');
        var label_enabled = $('.label-enabled');
        var label_disabled = $('.label-disabled');

        $(loading).show();
        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_remove_cname'}, function (response) {
            if (response.success) {
                $(configure).show();
                $(configured).hide();
                $(cname_enabled).hide();
                $(label_enabled).hide();
                $(cname_disabled).show();
                $(label_disabled).show();
                Swal.close();
            }
        });
    }

    function ExcludeList() {
        var popup = $('.exclude-list-popup');
        var popupData = $('.swal2-container .exclude-list-popup');
        var save = $('.btn-save', popup);
        var loading = $('.cdn-popup-loading', popup);
        var content = $('.cdn-popup-content', popup);
        var top = $('.cdn-popup-top', popup);

        $(save).on('click', function (e) {
            e.preventDefault();
            $(top).hide();
            $(content).hide();
            $(loading).show();

            var excludeList = $('[name="exclude-list-textarea"]', popupData).val();
            var lazyExcludeList = $('[name="exclude-lazy-textarea"]', popupData).val();
            var delayExcludeList = $('[name="delay-js-exclude-list-textarea"]', popupData).val();

            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_exclude_list', excludeList: excludeList, lazyExcludeList:lazyExcludeList, delayExcludeList: delayExcludeList}, function (response) {
                if (response.success) {
                    $('.exclude-list-textarea-value').text(excludeList);
                    $('.exclude-lazy-textarea-value').text(lazyExcludeList);
                    $('.delay-js-exclude-list-textarea-value').text(delayExcludeList);
                    $(top).show();
                    $(content).show();
                    $(loading).hide();
                    Swal.close();
                }

            });

            return false;
        });
    }

    function GeoLocation() {
        var popup = $('.swal2-container .geo-location-popup');
        var save = $('.btn-save-location', popup);
        var find = $('.btn-i-dont-know', popup);
        var loading = $('.cdn-popup-loading', popup);
        var content = $('.cdn-popup-content', popup);
        var top = $('.cdn-popup-top', popup);

        $(save).on('click', function (e) {
            e.preventDefault();
            $(top).hide();
            $(content).hide();
            $(loading).show();

            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_geolocation_force', location: $('select[name="location-select"]', popup).val()}, function (response) {
                if (response.success) {

                    var continent = response.data.continent;
                    var country_name = response.data.country_name;

                    $('select[name="location-select"] option').removeAttr('selected');
                    $('select[name="location-select"] option[value="' + continent + '"]').attr('selected', 'selected');
                    $('select[name="location-select"]').val(continent);

                    $('.wpc-dynamic-text', popup).html('We have detected that your server is located in ' + country_name + ' (' + continent + '), if that\'s not correct, please select the nearest region below.');

                    // OK
                    /*$(top).show();
                    $(content).show();
                    $(loading).hide();*/
                    window.location.reload();
                }
                else {
                    // Error Popup
                }
            });

            return false;
        });

        $(find).on('click', function (e) {
            e.preventDefault();
            $(top).hide();
            $(content).hide();
            $(loading).show();

            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_geolocation'}, function (response) {
                console.log(response.data);
                if (response.success) {
                    var continent = response.data.continent;
                    var country_name = response.data.country_name;
                    $('select[name="location-select"] option').removeAttr('selected');
                    $('select[name="location-select"] option[value="' + continent + '"]').attr('selected', 'selected');
                    $('select[name="location-select"]').val(continent);

                    $('.wpc-dynamic-text', popup).html('We have detected that your server is located in ' + country_name + ' (' + continent + '), if that\'s not correct, please select the nearest region below.');

                    // OK
                    $(top).show();
                    $(content).show();
                    $(loading).hide();
                }
                else {
                    // Error Popup
                }
            });

            return false;
        });
    }


    $('.wps-ic-search-through').on('click', function (e) {
        e.preventDefault();

        $('.wp-ic-header-buttons-container').hide();
        $('.changes-saved-container').hide();
        $('.changes-detected-container').fadeIn();


        var search_through = $(this).data('value');

        if (search_through == 'html') {

            if ($('#css-toggle').is(':checked')) {
                $('#css-toggle').trigger('click');
            }

        }
        else if (search_through == 'html+css') {

            if (!$('#css-toggle').is(':checked')) {
                $('#css-toggle').trigger('click');
            }

        }
        else if (search_through == 'all') {
            if (!$('#css-toggle').is(':checked')) {
                $('#css-toggle').trigger('click');
            }

        }

        return false;
    });

    /**
     * Ajax Checkbox
     */
    $('.wpc-ajax-checkbox').on('change', function (e) {
        e.preventDefault();

        var parent = $(this);
        var checkbox = $('input[type="checkbox"]', parent);
        var setting_name = $(checkbox).data('setting_name');
        var value = $(checkbox).data('setting_value');
        var checked = $(checkbox).is(':checked');

        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_ajax_checkbox', setting_name: setting_name, value: value, checked: checked}, function (response) {
            if (response.success) {
                // OK
            }
            else {
                // Error Popup
            }
        });
        return false;
    });


    /**
     * Detect Option change
     */
    $('.ic-advanced-settings-v2 input[type="checkbox"]').on('change', function (e) {
        if ($(this).hasClass('disabled-checkbox')) {
            return false;
        }

        $('.changes-saved-container').hide();
        $('.wp-ic-header-buttons-container').hide();
        $('.changes-detected-container').fadeIn();
    });

    function change_service_status(active) {
        if (!active) {
            $('.wps-ic-service-status').removeClass('paused').addClass('active').html('Live Optimization Active');
            $('.local-requests-left').hide();
            $('.requests-left').show();
            $('.bulk-button').hide();
        }
        else {
            $('.wps-ic-service-status').removeClass('active').addClass('paused').html('Live Optimization Paused');
            $('.local-requests-left').show();
            $('.requests-left').hide();
            $('.bulk-button').show();
        }
    }

    /**
     * @since 5.00.59
     */
    $('.wps-ic-service-status').on('click', function (e) {
        e.preventDefault();
        $('.wps-ic-live-cdn-ajax').trigger('click');
        return false;
    });

    /**
     * @since 5.00.59
     */
    $('.wps-ic-ajax-checkbox-cdn').on('click', function (e) {
        e.preventDefault();


        if ($(this).hasClass('locked')) {
            return false;
        }

        var parent = $(this);
        var checkbox = $('input[type="checkbox"]', parent);
        var setting_name = $(checkbox).data('setting_name');
        var value = $(checkbox).data('setting_value');
        var checked = $(checkbox).is(':checked');
        var on_span = $(checkbox).data('on-text');
        var off_span = $(checkbox).data('off-text');
        var leftover_popup = $(parent).hasClass('no-leftover-popup');
        var allow_live_popup = $(parent).hasClass('dont-allow-local');

        var span = $('span', parent);
        var label_holder = $('.label-holder', parent);

        /**
         * Is this service status change?
         */
        if (setting_name == 'live-cdn') {

            if (allow_live_popup && 1==0) {
                Swal.fire({
                    title: '', html: jQuery('#no-live-popup').html(), width: 600, showCancelButton: false, showConfirmButton: false, confirmButtonText: 'Okay, I Understand', allowOutsideClick: true, customClass: {
                        container: 'no-padding-popup-bottom-bg switch-legacy-popup',
                    }, onOpen: function () {
                    }
                });
                return false;
            }

            if (leftover_popup) {
                Swal.fire({
                    title: '', html: jQuery('#no-credits-popup').html(), width: 600, showCancelButton: false, showConfirmButton: false, confirmButtonText: 'Okay, I Understand', allowOutsideClick: true, customClass: {
                        container: 'no-padding-popup-bottom-bg switch-legacy-popup',
                    }, onOpen: function () {
                    }
                });
                return false;
            }

            change_service_status(checked);
        }

        if (checked) {
            $(checkbox).prop('checked', false);
        }
        else {
            $(checkbox).prop('checked', true);
        }

        var checked = $(checkbox).is(':checked');


        /*
         * If label change on status change should occur
         */
        if (typeof on_span !== 'undefined' && typeof off_span !== 'undefined') {
            if (on_span != '' && off_span != '') {
                if (!checked) {
                    $(span).html(off_span);
                }
                else {
                    $(span).html(on_span);
                }
            }
        }

        /*
         * If label change on status change should occur
         */
        if (typeof label_holder !== 'undefined' && typeof label_holder !== 'undefined') {
            if (checked) {
                $(label_holder).html('Off');
            }
            else {
                $(label_holder).html('On');
            }
        }

        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_ajax_checkbox', setting_name: setting_name, value: value, checked: checked}, function (response) {
            if (response.success) {
                // OK
            }
            else {
                // Error Popup
            }
        });

        return false;
    });

    /** New Above **/


    var tooltips = 0;
    var ajax_run = true;


    /**
     * Additional configuration in advanced settings
     * @since 5.00.59
     */
    $('.button-save-settings').on('click', function (e) {
        e.preventDefault();


        var inputFields = $('input[name^="wp-ic-setting"]');
        var settings = '';
        var parsed = '';

        $(inputFields).each(function (index, value) {
            var checked = $(this).is(':checked');
            if (checked) {
                checked = '1';
            }
            else {
                checked = '0';
            }
            parsed = parsed + $(this).data('setting_name') + '=' + checked + '&';
        });

        parsed = parsed.replace(/\&$/, '');

        Swal.fire({
            title: '', html: jQuery('#saving-settings-popup').html(), width: 600, showCancelButton: false, showConfirmButton: false, allowOutsideClick: false, customClass: {
                container: 'no-padding-popup-bottom-bg',
            }, onOpen: function () {

                $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_save_all_settings', settings: parsed}, function (response) {
                    if (response.success) {
                        swal.close();

                        Swal.fire({
                            title: '', html: jQuery('#settings-saved-popup').html(), width: 600, showCancelButton: false, showConfirmButton: false, allowOutsideClick: true, showCloseButton: true, customClass: {
                                container: 'no-padding-popup-bottom-bg',
                            },
                        });

                    }
                    else {
                        alert('Oops! We weren\'t able to save your settings! :(');
                    }
                });

                //swal.close();

            }
        });

        // saving-settings-popup
        // settings-saved-popup

        return false;
    });


    /**
     * Additional configuration in advanced settings
     * @since 5.00.54
     */
    $('.wps-ic-additional-configuration').on('click', function (e) {
        e.preventDefault();
        return false;
    })


    /*
    * Circle
    */
    $('#circle-big').circleProgress({
        size: 120, startAngle: -Math.PI / 6 * 3, lineCap: 'round', thickness: '4', fill: {
            gradient: ["#1c87f1", "#3c4cdf"], gradientAngle: Math.PI / 7
        }
    });

    /**
     * @since 4.0.0
     * Status: Required 5.00.00
     */
    $('.projected-flag-ok').tooltipster({
        maxWidth: '300', delay: 50, speed: 100
    });


    /**
     * @since 4.0.0
     * Status: Required 5.00.00
     */
    $('.projected-flag-warning').tooltipster({
        maxWidth: '300', delay: 50, speed: 100, theme: 'warning-tooltip-theme',
    });

    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.setting-value.ic-custom-tooltip').tooltipster({
        maxWidth: '235',
        position: 'left'
    });


    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.tooltip').tooltipster({
        maxWidth: '300',
    });


    /**
     * @since 5.00.20
     */
    $('.btn-purge-cdn').on('click', function (e) {
        e.preventDefault();
        var btn = $(this);
        var span = $('span', btn);
        var loading = $('#purge-cdn-loading', btn);

        $(btn).addClass('loading');
        $(loading).show();
        $(span).html('Purging');

        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_purge_cdn'}, function (response) {

            if (response.success) {
                $(btn).removeClass('loading');
                $(loading).hide();
                $(span).html('Purge CDN');
            }
            else {
                alert('Oops! We weren\'t able to purge your CDN! :(');
            }
        });

        return false;
    });

    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.whole-checkbox-autopilot>div').on('click', function (e) {
        e.preventDefault();

        if ($(this).parent().hasClass('disabled-no-popup')) {
            return false;
        }
        if ($(this).parent().hasClass('disabled')) {
            // Disable Local Popup
            return false;

            Swal.fire({
                title: '', html: jQuery('#local-disabled-popup').html(), width: 900, showCancelButton: true, cancelButtonColor: '#fdfdfd', cancelButtonText: "Ok", showConfirmButton: false, customClass: {
                    container: 'no-padding-popup-bottom-bg switch-legacy-popup local-disabled-popup',
                }, onOpen: function () {


                }
            });
            return false;
        }

        saving_settings = true;
        var parent = $(this).parent();
        var input = $('input', parent);
        var setting_name = $(input).data('setting_name');
        var value = $(input).data('setting_value');
        var checked = $(input).is(':checked');
        var informative = $('span', parent);
        var div = $(parent);
        var ap_status = $(parent).data('autopilot-status');

        if (ap_status == '1') {
            // Turning OFF
            Swal.fire({
                title: '', html: jQuery('#legacy-enable-popup').html(), width: 900, showCancelButton: true, cancelButtonColor: '#fdfdfd', confirmButtonColor: '#fdfdfd', confirmButtonText: 'Switch to Local', cancelButtonText: "Stay on Live", customClass: {
                    container: 'no-padding-popup-bottom-bg switch-legacy-popup',
                }, onOpen: function () {


                }
            }).then(function (isConfirm) {
                if (isConfirm.value == true) {

                    $(parent).data('autopilot-status', '0');

                    if ($(input).is(':checked')) {
                        $(input).prop('checked', false);
                        checked = false;
                        value = 0;
                        if (!$(informative).hasClass('no-change')) {
                            $(informative).html('Local');
                        }

                        $('.wps-ic-local-compress').show();
                        $('.wps-ic-live-compress').hide();
                    }
                    else {
                        $(input).prop('checked', true);
                        checked = true;
                        value = 1;

                        if (!$(informative).hasClass('no-change')) {
                            $(informative).html('Live CDN');
                        }

                        $('.wps-ic-local-compress').hide();
                        $('.wps-ic-live-compress').show();
                    }

                    $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting_name, value: value, checked: checked, checkbox: true}, function (response) {
                        if (response.success) {
                            // Nothing
                            saving_settings = false;

                            $('.wps-ic-legacy-option').removeClass('wps-ic-legacy-hide').show();
                            window.location.reload();

                        }
                        else {
                            alert('Oops! We weren\'t able to save your settings! :(');
                        }
                    });
                }
                else {
                    return false;
                }
            });
        }
        else {
            $(parent).data('autopilot-status', '1');
            if ($(input).is(':checked')) {
                $(input).prop('checked', false);
                checked = false;
                value = 0;
                if (!$(informative).hasClass('no-change')) {
                    $(informative).html('Local');
                }

                $('.wps-ic-local-compress').show();
                $('.wps-ic-live-compress').hide();
            }
            else {
                $(input).prop('checked', true);
                checked = true;
                value = 1;

                if (!$(informative).hasClass('no-change')) {
                    $(informative).html('Live CDN');
                }

                $('.wps-ic-local-compress').hide();
                $('.wps-ic-live-compress').show();
            }

            $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting_name, value: value, checked: checked, checkbox: true}, function (response) {
                if (response.success) {
                    // Nothing
                    saving_settings = false;

                    //$('.wps-ic-legacy-option').addClass('wps-ic-legacy-hide').hide();
                    window.location.reload();
                }
                else {
                    alert('Oops! We weren\'t able to save your settings! :(');
                }
            });
        }


    });


    function disable_other_toggles(action) {

        var standard = $('.whole-checkbox');

        if (action == 'disable') {
            $(standard).addClass('disable');
        }
        else {
            $(standard).removeClass('disable');
        }
    }


    /**
     * Change Optimization
     */
    $('.wps-ic-change-optimization').on('click', function (e) {
        e.preventDefault();

        var link = $(this);
        var value = $(link).data('optimization_level');

        $('input#wp-ic-setting-optimization').attr('value', value);

        $.post(ajaxurl, {action: 'wps_ic_settings_change', what: 'optimization', value: value}, function (response) {
            if (response.success) {
                // Nothing
            }
            else {
                alert('Oops! We weren\'t able to save your settings! :(');
            }
        });
    });


    function lockedPopup() {
        Swal.fire({
            title: '', html: jQuery('#locked-popup').html(), width: 600, showCancelButton: false, showConfirmButton: false, allowOutsideClick: true, showCloseButton: true, customClass: {
                container: 'no-padding-popup-bottom-bg',
            }, onOpen: function () {


            }
        });
        disable_other_toggles('enable');
        return false;
    }

    $('.button-locked').on('click', function (e) {
        e.preventDefault();
        return false;
    })

    $('.checkbox-container-v2.locked').on('click', function (e) {
        e.preventDefault();
        //lockedPopup();
        return false;
    });

    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.whole-checkbox>div').on('click', function (e) {
        e.preventDefault();

        saving_settings = true;
        var parent = $(this).parent();
        var input = $('input', parent);
        var setting_name = $(input).data('setting_name');
        var value = $(input).data('setting_value');
        var checked = $(input).is(':checked');
        var informative = $('span', parent);
        var div = $(parent);

        if (!checked) {
            if ($(this).hasClass('locked') || $(this).parent().hasClass('locked')) {
                e.preventDefault();
                //lockedPopup();
                return false;
            }
        }

        disable_other_toggles('disable');

        // If setting is CSS/JS then change minify also
        if (setting_name == 'css' || setting_name == 'js') {
            if ($(input).is(':checked')) {

                setTimeout(function () {
                    $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting_name + '-minify', value: 0, checked: false, checkbox: true}, function (response) {
                        if (response.success) {
                            // Nothing
                            //saving_settings = false;
                            $('#' + setting_name + '-minify-toggle').prop('checked', false);
                        }
                        else {
                            alert('Oops! We weren\'t able to save your settings! :(');
                        }
                    });
                }, 1000);

                $('.minify-' + setting_name + '-checkbox').css('display', 'none');
            }
            else {
                $('.minify-' + setting_name + '-checkbox').css('display', 'inline-block');
            }
        }

        if ($(input).is(':checked')) {
            $(input).prop('checked', false);
            checked = false;
            value = 0;
            if (!$(informative).hasClass('no-change')) {
                if (setting_name == 'css-minify' || setting_name == 'js-minify') {
                    $(informative).html('Minify');
                }
                else {
                    $(informative).html('OFF');
                }
            }
        }
        else {
            $(input).prop('checked', true);
            checked = true;
            value = 1;

            if (!$(informative).hasClass('no-change')) {
                if (setting_name == 'css-minify' || setting_name == 'js-minify') {
                    $(informative).html('Minify');
                }
                else {
                    $(informative).html('ON');
                }
            }
        }


        // Turning On?
        if (value == 1) {
            show_popup_for(setting_name);
        }

        // Is AutoPilot?
        if (setting_name == 'autopilot') {
            if ($(input).is(':checked')) {
                $(div).css('padding-top', '13px');
                $('.wp-ic-select-box.disabled').hide();
                $('.wp-ic-select-box.enabled').show();
            }
            else {
                $(div).css('padding-top', '40px');
                $('.wp-ic-select-box.disabled').show();
                $('.wp-ic-select-box.enabled').hide();
            }
        }


        if (setting_name == 'live_api') {
            if (checked) {
                /**
                 * Enabled Live
                 */
                $('.live-option-row').attr('style', 'opacity:1;');
                $('.ic-live-overlay').attr('style', 'display:none;opacity:0;visibility:hidden;');

                $('#ic-legacy-row').attr('style', 'opacity:0.3;position:relative;');
                $('#ic-legacy-overlay').attr('style', 'display:block;opacity:1;visibility:visible;');
            }
            else {
                /**
                 * Disabled Live
                 */
                $('.live-option-row').attr('style', 'opacity:0.3;position:relative;');
                $('.ic-live-overlay').attr('style', 'display:block;opacity:1;visibility:visible;');

                $('#ic-legacy-row').attr('style', 'opacity:1;');
                $('#ic-legacy-overlay').attr('style', 'display:none;opacity:0;visibility:hidden;');
            }
        }


        // Show CDN scanning popup
        if (setting_name == 'cdn' && value == 1 && checked == true) {
            show_cdn_popup();
        }


        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting_name, value: value, checked: checked, checkbox: true}, function (response) {
            disable_other_toggles('enable');

            if (response.success) {
                // Nothing
                saving_settings = false;
            }
            else {
                alert('Oops! We weren\'t able to save your settings! :(');
            }
        });
    });


    function show_popup_for(setting_name) {

        if (setting_name == 'lazy' || setting_name == 'generate_adaptive' || setting_name == 'css' || setting_name == 'js' || setting_name == 'js-minify' || setting_name == 'css-minify' || setting_name == 'defer-js' || setting_name == 'css_combine') {
            show_compatibility_popup(setting_name);
        }

    }


    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    function show_compatibility_popup(popupID) {
        Swal.fire({
            title: '', html: jQuery('#' + popupID + '-compatibility-popup').html(), width: 600, showCancelButton: false, showConfirmButton: true, confirmButtonText: 'Okay, I Understand', allowOutsideClick: false, customClass: {
                container: 'no-padding-popup-bottom-bg switch-legacy-popup',
            }, onOpen: function () {


            }
        });
    }


    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.wp-ic-ajax-checkbox-v2').on('click', function (e) {
        e.preventDefault();

        saving_settings = true;
        var parent = $(this).parent().parent();
        var input = $('input', parent);
        var setting = $(input).data('setting_name');
        var value = $(input).data('setting_value');
        var checked = $(input).is(':checked');
        var informative = $('span', parent);

        if ($(input).is(':checked')) {
            $(input).prop('checked', false);
            checked = false;
            value = 0;
            if (!$(informative).hasClass('no-change')) {
                $(informative).html('OFF');
            }
        }
        else {
            $(input).prop('checked', true);
            checked = true;
            value = 1;

            if (!$(informative).hasClass('no-change')) {
                $(informative).html('ON');
            }
        }

        // Show CDN scanning popup
        if (setting == 'cdn' && value == 1 && checked == true) {
            show_cdn_popup();
        }

        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting, value: value, checked: checked, checkbox: true}, function (response) {
            if (response.success) {
                // Nothing
                saving_settings = false;
            }
            else {
                alert('Oops! We weren\'t able to save your settings! :(');
            }
        });
    });


    /**
     * @since 3.3.0
     * Status: Required 5.00.00
     */
    $('.wp-ic-ajax-input').focusout(function (e) {
        e.preventDefault();

        var parent = $(this).parent();
        var input = $('input', parent);
        var setting = $(input).data('setting_name');
        var value = $(input).attr('value');

        $.post(wps_ic_vars.ajaxurl, {action: 'wps_ic_settings_change', what: setting, value: value, checked: false, checkbox: false}, function (response) {
            if (response.success) {
                // Nothing
            }
            else {
                alert('Oops! We weren\'t able to save your settings! :(');
            }
        });
    });


    /**
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    if ($('.wps-ic-trigger_connect').length) {
        var link = $('.wps-ic-authorize-api').attr('href');
        $.ajaxSetup({async: false, cache: false});
        $.post(ajaxurl, {action: 'wps_ic_authorize_api'}, function (response) {
            if (response.success) {
                window.location.href = link;
            }
            else {
                alert(response.data);
            }
        });
    }


    /**
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    $('.button-tooltips').on('click', function (e) {
        e.preventDefault;

        var link = $(this);
        var tooltips_on = $(this).data('tooltips');

        if (tooltips_on == '0') {
            // Turn  it on
            $('span', link).html('On');
            tooltips = 1;
        }
        else {
            $('span', link).html('Off');
            tooltips = 0;
        }

        return false;
    });


    /**
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    $('form#wps_ic_activate_form').submit(function (e) {
        e.preventDefault();

        var error = false;
        var form = $(this);
        var loading = $('.wps-ic-form-loading-container');

        var apikey = $('input[name="apikey"]', form).val();
        var fail_test = $('input[name="fail_test"]', form).val();

        if (apikey == '') {
            $('input[name="apikey"]', form).addClass('ic_required');
            error = true;
        }

        if (error == false) {

            $(form).hide(function () {
                $(loading).show();

                Swal.fire({
                    title: '', html: $('#wps-ic-connection-tests').html(), showConfirmButton: false, showCloseButton: false, allowOutsideClick: false, onOpen: function () {
                        jQuery('body').on('click', '.wps-ic-swal-close', function (e) {
                            e.preventDefault();

                            swal.close();
                        });
                    }
                });

                $.post(ajaxurl, {action: 'wps_ic_api_test', test_id: 'verify_api_key', apikey: apikey, fail_test: fail_test}, function (response) {
                    if (response.success == true) {

                        test_finished(form, loading, 'verify_api_key', 'success');

                        if (run_Ajax()) {
                            $.post(ajaxurl, {action: 'wps_ic_api_connect', apikey: apikey, fail_test: fail_test}, function (response) {
                                if (response.success == true) {

                                    test_finished(form, loading, 'finalization', 'success');

                                    $(form).hide();
                                    $(loading).hide();
                                    swal.close();

                                    Swal.fire({
                                        title: '', html: $('#wps-ic-connection-tests-done').html(), showConfirmButton: false, showCloseButton: false, allowOutsideClick: false, onOpen: function () {
                                            jQuery('body').on('click', '.wps-ic-swal-close', function (e) {
                                                e.preventDefault();
                                                swal.close();
                                            });
                                        }
                                    });

                                }
                                else {
                                    test_finished(form, loading, 'finalization', 'failed');
                                }
                            });
                        }

                    }
                    else {
                        test_finished(form, loading, 'verify_api_key', 'failed', response.data.code);
                    }

                });

            });

        }

        return false;
    });


    /**
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    function run_Ajax() {
        if (ajax_run == false) {
            console.log('Ajax Canceled');
            return false;
        }
        else {
            return true;
        }
    }


    /**
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    function test_finished(form, loading, test, status, message) {
        var swalcontainer = $('#swal2-content');
        var msg = '';
        var title = '';

        if (status == 'failed') {
            $(loading).hide();
            $(form).show();
            $('.wps-ic-swal-close', swalcontainer).attr('value', 'Close');


            $('ul>li[data-test="' + test + '"] span', swalcontainer).removeClass('running').addClass('failed');
            $('ul>li[data-test="' + test + '"] span', swalcontainer).removeClass('fa-dot-circle').addClass('fa-check');

            if (test == 'verify_api_key') {
                msg = 'Your api key does not match our records.';
                title = 'API Key Validation';
            }
            else if (test == 'communication_with_portal') {
                msg = 'Your site cannot communicate with the portal.';
                title = 'Portal Communication';
            }
            else if (test == 'image_compress') {

                if (message == 'unable_upload') {
                    msg = 'We were not able to upload & optimize a test image.';
                }
                else if (message == 'unable_compress') {
                    msg = 'We were not able to compress the test image.';
                }
                else if (message == 'no_attachments') {
                    msg = 'We did not find any attachments to compress.';
                }

                title = 'Image Compress';
            }
            else if (test == 'image_restore') {

                if (message == 'unable_restore') {
                    msg = 'We were not able to restore the test image.';
                }

                title = 'Image Restore';
            }

            swal.close();

            $('ul>li', '#wps-ic-test-error').html('<span class="fas"></span> ' + title);
            $('ul>li span', '#wps-ic-test-error').addClass('failed');
            $('ul>li span', '#wps-ic-test-error').addClass('fa-times');
            $('.ic-error-msg', '#wps-ic-test-error').html(msg);

            Swal.fire({
                title: '', html: $('#wps-ic-test-error').html(), showConfirmButton: false, showCloseButton: true, allowOutsideClick: false, onOpen: function () {
                    jQuery('body').on('click', '.wps-ic-swal-close', function (e) {
                        e.preventDefault();
                        window.location.reload();
                        swal.close();
                    });

                }, onClose: function () {
                    window.location.reload();
                }
            });

        }
        else {
            $('ul>li[data-test="' + test + '"] span', swalcontainer).removeClass('running').addClass('success');
            $('ul>li[data-test="' + test + '"] span', swalcontainer).removeClass('fa-dot-circle').addClass('fa-check');
        }
    }


    /**
     * Authorize with remote API
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    $('.wps-ic-authorize-api').on('click', function (e) {

        $.ajaxSetup({async: false, cache: false});
        $.post(ajaxurl, {action: 'wps_ic_authorize_api'}, function (response) {
            if (response.success) {
                window.location.reload();
            }
            else {
                alert(response.data);
            }
        });

    });


    /**
     * Deauthorize with remote API
     * @since 3.3.0
     * Status: Maybe not required? TODO
     */
    $('.wps-ic-deauthorize-api').on('click', function (e) {

        $.ajaxSetup({async: false, cache: false});
        $.post(ajaxurl, {action: 'wps_ic_deauthorize_api'}, function (response) {
            if (response.success) {
                window.location.reload();
            }
            else {
                alert(response.data);
            }
        });

    });


});