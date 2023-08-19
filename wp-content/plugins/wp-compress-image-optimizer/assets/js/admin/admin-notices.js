jQuery(document).ready(function ($) {
    $('.wps-ic-dismiss-notice').on('click', function(){
        var id = $(this).data('tag');

        $('.wps-ic-tag-'+id).slideUp();

        $.post(ajaxurl, {action: 'wps_ic_dismiss_notice', id: id}, function () {

        });
    });
});