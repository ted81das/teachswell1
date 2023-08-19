jQuery(document).ready(function ($) {


    function popupModes() {
        Swal.fire({
            title: '',
            position: 'center',
            html: jQuery('#select-mode').html(),
            width: 1050,
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: true,
            customClass: {
                container: 'no-padding-popup-bottom-bg switch-legacy-popup',
            },
            onOpen: function () {
                var modes_popup = $('.swal2-container .ajax-settings-popup');
                selectModesTrigger();
                hookCheckbox();
                saveMode(modes_popup);
            },
            onClose: function () {
                //openConfigurePopup(popup_modal);
            }
        });
    }

    function saveMode(modes_popup) {
        var save = $('.cdn-popup-save-btn', modes_popup);
        var loading = $('.cdn-popup-loading', modes_popup);
        var content = $('.cdn-popup-content', modes_popup);

        $(save).on('click', function (e) {
            e.preventDefault();
            $(content).hide();
            $(loading).show();

            var selected_mode = $('div.wpc-active', modes_popup).data('mode');
            var cdn = $('.form-check-input', modes_popup).prop('checked');

            $.post(wps_ic_vars.ajaxurl, {
                action: 'wps_ic_save_mode', mode: selected_mode, cdn: cdn}, function (response) {
                if (response.success){
                    location.reload();
                } else {
                    //error?
                }
            });

            return false;
        });
    }


    /**
     * Single Checkbox
     */
    function hookCheckbox() {
        $('label', '.swal2-content').on('click', function(){
            var parent = $(this).parent();
            var checkbox = $('input[type="checkbox"]', parent);
            $(checkbox).prop('checked', !$(checkbox).prop('checked'));
            console.log($(checkbox).prop('checked'));
        });

        $('input[type="checkbox"]', '.swal2-content').on('change', function () {
            var checkbox = $(this);
            var beforeValue = $(checkbox).attr('checked');

            console.log(checkbox);
            console.log(beforeValue);


            if (beforeValue == 'checked') {
                // It was already active, remove checked
                $(this).removeAttr('checked').prop('checked', false);
                $(parent).removeClass('active');
            } else {
                // It's not active, activate
                $(this).attr('checked', 'checked').prop('checked', true);
                $(parent).addClass('active');
            }
        });
    }


    function selectModesTrigger() {
        $('.wpc-popup-column', '.swal2-container').on('click', function (e) {
            e.preventDefault();

            var parent = $('.wpc-popup-columns', '.swal2-container');
            var selectBar = $('.wpc-select-bar .wpc-select-bar-inner','.swal2-container');
            var selectBarValue = $(this).data('slider-bar');
            var modeSelect = $(this).data('mode');

            $(selectBar).removeClass('wpc-select-bar-width-1 wpc-select-bar-width-2 wpc-select-bar-width-3');
            $(selectBar).addClass('wpc-select-bar-width-' + selectBarValue);

            $('.wpc-popup-column', parent).removeClass('wpc-active');
            $(this).addClass('wpc-active');

            var checked = $('.form-check-input','.wpc-popup-option-checkbox').is(':checked');
            console.log(checked);

            if (modeSelect == 'safe') {
                // Safe mode - turn off CDN
                $('.form-check-input','.wpc-popup-option-checkbox').removeAttr('checked').prop('checked', false);
            } else {
                if (!checked) {
                    $('.form-check-input','.wpc-popup-option-checkbox').attr('checked','checked').prop('checked', true);
                }
            }

            return false;
        });
    }

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
            }, //customClass:'wps-ic-connect-popup',
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            onOpen: function () {

                $('.wps-ic-connect-retry').on('click', function (e) {
                    e.preventDefault();
                    swalFunc();
                    return false;
                });

                var swal_container = $('.swal2-container');
                var form = $('#wps-ic-connect-form', swal_container);
                $('#wps-ic-connect-form', swal_container).on('submit', function (e) {
                    e.preventDefault();

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

                    $.post(ajaxurl, {
                        action: 'wps_ic_live_connect',
                        apikey: apikey
                    }, function (response) {
                        if (response.success) {
                            // Connect
                            $('.wps-ic-connect-inner').addClass('padded');
                            //$(success_message).show();
                            //$(success_message_choice_text).show();

                            Swal.close();

                            popupModes();

                            /**
                             * Figure out what optimization mode is enabled
                             */
                            if (response.data.liveMode == '0') {
                                var liveBtn = $('.wpc-live-btn', '.wpc-select-mode-containers');
                                var liveBtnText = $('.wpc-live-btn-text', liveBtn);
                                $(liveBtn).addClass('wpc-disabled-option');
                                $(liveBtnText).addClass('wpc-disabled-button');
                                $(liveBtnText).html('Disabled');
                            }

                            if (response.data.localMode == '0') {
                                var localBtn = $('.wpc-local-btn', '.wpc-select-mode-containers');
                                var localBtnText = $('.wpc-local-btn-text', localBtn);
                                $(localBtn).addClass('wpc-disabled-option');
                                $(localBtnText).addClass('wpc-disabled-button');
                                $(localBtnText).html('Disabled');
                            }

                            $('.wpc-live-btn', success_message_choice_text).on('click', function (e) {
                                e.preventDefault();

                                var btn = $(this);
                                if ($(btn).hasClass('wpc-disabled-option')) {
                                    return false;
                                }

                                $.post(ajaxurl, {
                                    action: 'wpc_ic_set_mode',
                                    value: 'recommended'
                                }, function (response) {
                                    $(loader).hide();
                                    $(tests).hide();
                                    setTimeout(function (){
                                        window.location.reload();
                                    },1000);
                                });
                            });

                            $('.wpc-local-btn', success_message_choice_text).on('click', function (e) {
                                e.preventDefault();

                                var btn = $(this);
                                if ($(btn).hasClass('wpc-disabled-option')) {
                                    return false;
                                }

                                $.post(ajaxurl, {
                                    action: 'wpc_ic_set_mode',
                                    value: 'safe'
                                }, function (response) {
                                    $(loader).hide();
                                    $(tests).hide();
                                    setTimeout(function (){
                                        window.location.reload();
                                    },1000);
                                });
                            });


                            $(success_message_buttons).on('click', function (e) {
                                e.preventDefault();

                                var btn = $(this);
                                if ($(btn).hasClass('wpc-disabled-option')) {
                                    return false;
                                }

                                $(finishing).show();
                                $(success_message).hide();
                                $(success_message_choice_text).hide();

                                setTimeout(function () {
                                    $(loader).hide();
                                    $(tests).hide();
                                }, 2000);
                            });

                            $(loader).hide();
                            $(tests).hide();
                        } else {
                            // Not OK
                            // msg = 'Your api key does not match our records.';
                            //                 title = 'API Key Validation';

                            if (response.data.msg == 'site-already-connected') {
                                $(already_connected).show();
                                $(error_message_container).show();
                                $(error_message_text).hide();
                                $(success_message_choice_text).hide();
                                $(success_message_text).hide();
                                $(success_message).hide();
                                $(loader).hide();
                                $(tests).hide();
                            } else {
                                $(error_message_text).show();
                                $(error_message_container).show();
                                $(success_message_text).hide();
                                $(success_message_choice_text).hide();
                                $(success_message).hide();
                                $(loader).hide();
                                $(tests).hide();
                            }

                            // $('.wps-ic-connect-retry', swal_container).bind('click');

                        }
                    });

                    return false;
                })

            }
        });
    }

    swalFunc();


});