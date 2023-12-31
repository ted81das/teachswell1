var selectedSites = '';
var contactPlan = '';
var additionalCredits = '';

jQuery(document).ready(function ($) {

    /**
     * Run through site checklist
     */
    var list_wrapper = $('.selected_sites_wrapper');
    if ($(list_wrapper).length > 0 && $('.main_wp_checksite', list_wrapper).length > 0) {
        $.ajaxSetup({async: true});
        $('.main_wp_checksite', list_wrapper).each(function (i, item) {
            var checkUrl = $(item).data('url');
            var siteID = $(item).data('site-id');

            $.post(ajaxurl, {action: 'wpc_checkSiteConnection', 'url': checkUrl, 'siteID': siteID}, function (response) {
                $('#selected_sites>div#ic-site-id-' + siteID).html(response.data);
            });


        });
    }


    /**
     * Activate plugin on selected sites
     */
    $('.wp-ic-activate-all').on('click', function () {

        var selectedSites = [];
        jQuery("input[name='selected_sites[]']:checked",selected_sites).each(function (i, item) {
            selectedSites.push($(item).val());
        });

        if (selectedSites.length == 0) {
            alert('Nothing selected');
            return;
        }

        var queue_list = $('ul', '#wpcompress-queue');
        $('.bulk-status').show();
        $('#wpcompress-queue').show();
        $(queue_list).append('<li>Started...</li>');

        contactPlan = $('input[name="contact[plan]"]').val();

        // TODO: Needed for plugin install!
        mainwp_install_bulk('plugin', 'wp-compress-image-optimizer');

        // console.log(selectedSites);
        // console.log(bulkInstallDone);
        // console.log(bulkInstallTotal);

        var interval = '';
        setTimeout(function () {
            /**
             * Check for completion
             */
            var totalSites = bulkInstallTotal;
            var doneSites = 0;

            if (bulkInstallDone <= bulkInstallTotal && selectedSites.length != 0) {
                $.each(selectedSites, function (i, item) {
                    // Install API Keys to sites
                    var siteID = $(item).attr('siteid');
                    var siteURL = $('#selected_sites>div#ic-site-id-' + siteID).attr('title');

                    $(queue_list).append('<li class="siteID-' + siteID + '">Working on ' + siteURL + '...</li>');

                    $.post(ajaxurl, {action: 'create_apikey_wpcompress', 'siteID': siteID, 'contactPlan': contactPlan}, function (response) {
                        console.log('Create API Key');
                        console.log('Key: ' + response.data.apikey);
                        console.log('Response: ' + response);
                        console.log('siteID: ' + siteID);

                        $(queue_list).append('<li class="siteID-' + siteID + '">Created API Key for ' + siteURL + '...</li>');

                        if (response.success == true) {
                            $.post(ajaxurl, {action: 'connect_apikey_wpcompress', 'siteID': siteID, 'apikey': response.data.apikey}, function (response) {

                                console.log('Connect API Key');
                                console.log(response);

                                if (response.success == true) {
                                    $(queue_list).append('<li class="siteID-' + siteID + '">Activated ' + siteURL + '...</li>');
                                    console.log('SiteID: ' + siteID + ' activated');
                                    doneSites++;
                                }
                                else {
                                    $(queue_list).append('<li class="siteID-' + siteID + '">Failed to Activate ' + siteURL + '...</li>');
                                    console.log('SiteID: ' + siteID + ' failed to activate');
                                    doneSites++;
                                }
                            });
                        }
                    });
                });

            }


            interval = setInterval(function () {
                if (totalSites == doneSites) {
                    clearInterval(interval);
                    $(queue_list).append('<li class="siteID-9999">Finished!</li>');
                }
            }, 200);
        }, 6000);


        // Fetch all selected sites
        selectedSites = $('input[type="checkbox"]:checked', '#selected_sites');
        contactPlan = $('input[name="contact[plan]"]').val();
        additionalCredits = $('input[name="contact[additional_credits]').val();

        return false;
    });


    /**
     * On connect form submit
     */
    $('.ic-connect-form').on('submit', function (e) {
        console.log('da');
        e.preventDefault();

        var form = $(this);
        $(form).hide();
        $('.ic-form-loading').show();

        var token = $('input[id="wpcompress_token"]', form).val();

        $.post(ajaxurl, {action: 'connect_wpcompress', 'api_login': 'true', 'token': token}, function (response) {
            if (response.success == true) {
                window.location.reload();
            }
            else {
                //window.location.reload();
                $(form).show();
                $('.ic-form-loading').hide();
                var outer = $(form).parent();
                $('.ic-form-error', outer).show();
            }
        });

        return false;
    });


    /**
     * Credits Sharing enabled?
     */
    $('#contact-credits-sharing').on('click', function () {

        if ($(this).is(':checked')) {
            $('.inline-select>.option').addClass('disabled-no-click');
        }
        else {
            $('.inline-select>.option').removeClass('disabled-no-click');
        }

    });


    /**
     * Inline Select
     */
    $('.inline-select div.option:not(.disabled-no-click)').on('click', function (e) {

        if ($(this).hasClass('disabled-no-click')) {
            return false;
        }

        var parent = $(this).parent();
        if ($(this).hasClass('disabled')) {

            swal.close();

            swal({
                title: '', html: jQuery('#wp-ic-not-enough-credits').html(), width: 900, showCloseButton: true, showCancelButton: false, showConfirmButton: false, onOpen: function () {
                }
            });

            return false;
        }

        var prev_selected = $('div.option.selected', parent).data('credits');
        var next_selected = $(this).data('credits');
        var plan = $(this).data('credits');
        var contactID = $(this).data('contactid');
        var planvalue = $(this).data('planvalue');

        $('div.option', parent).removeClass('selected');
        $(this).addClass('selected');
        $('input', parent).val(plan);
    });


    /**
     * Select All Sites - Button
     */
    var selected = false;
    var button = $('.sites_list_all');
    $('.sites_list_all').on('click', function (e) {
        e.preventDefault();

        var parent = '';
        var list = $('input', '.mainwp_selected_sites_item');

        $.each(list, function (index, item) {

            parent = $(item).parent();

            if (selected === false) {

                if (!$(parent).hasClass('selected_sites_item_checked')) {
                    $(item).trigger('click');
                }
            }
            else {

                if ($(parent).hasClass('selected_sites_item_checked')) {
                    $(item).trigger('click');
                }
            }

        });

        if (selected == false) {
            selected = true;
            button.html('Deselect all');
        }
        else {
            selected = false;
            button.html('Select all');
        }

        return false;
    });


});