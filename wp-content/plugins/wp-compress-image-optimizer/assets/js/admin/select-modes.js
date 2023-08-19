jQuery(document).ready(function ($) {

    if (wpc_ic_modes.showModes == true) {
        Swal.fire({
            title: '',
            position: 'center',
            html: jQuery('#select-mode').html(),
            width: 1050,
            showCloseButton: true,
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


    $('.wpc-dropdown-trigger-popup,.wpc-dropdown-trigger-popup button,.wpc-select-modes').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: '',
            position: 'center',
            html: jQuery('#select-mode').html(),
            width: 1050,
            showCloseButton: true,
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

        return false;
    });

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


});