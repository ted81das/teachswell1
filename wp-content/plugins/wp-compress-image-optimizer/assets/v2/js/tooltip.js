jQuery(document).ready(function ($) {

    // $('.wpc-custom-tooltip').tooltipster({
    //     functionInit: function(origin, content) {
    //         var contentID = $(content.origin).data('tooltip-id');
    //         var tooltipContent = $('#'+contentID);
    //         console.log(contentID);
    // }});

    // Question tooltips
    $('.wpc-tooltip').tooltipster({
        maxWidth: '300', delay: 50,
    });


    $('.wpc-custom-tooltip').hover(function (e) {
        var icon = $(this);
        var tooltipID = $(this).data('tooltip-id');
        var tooltipPosition = $(this).data('tooltip-position');

        //$('#'+tooltipID).show();
        var tooltipBox = $('#' + tooltipID);

        var parent = $(this).closest('.option-box');
        var formbox = $('.form-check', parent);

        var position = $(formbox).position();

        var boxWidth = $(tooltipBox).outerWidth() + 15;
        var boxHeight = $(tooltipBox).outerHeight() - 15;

        var leftPos = 0;
        var topPos = 0;

        if (tooltipPosition == 'right') {

            leftPos = position.left + 40;
            topPos = position.top - (boxHeight / 2);

            $(tooltipBox).css({'top': Math.round(topPos), 'left': Math.round(leftPos)});
        }
        else if (tooltipPosition == 'left') {

            leftPos = position.left - boxWidth;
            topPos = position.top - (boxHeight / 2);

            $(tooltipBox).css({'top': Math.round(topPos), 'left': Math.round(leftPos)});
        }
        else if (tooltipPosition == 'top') {
            leftPos = position.left +25- (boxWidth/2);
            topPos = position.top - (boxHeight+30);

            $(tooltipBox).css({'top': Math.round(topPos), 'left': Math.round(leftPos)});
        }


        $(tooltipBox).fadeIn(500);
    }, function (e) {
        var icon = $(this);
        var tooltipID = $(this).data('tooltip-id');
        var tooltipBox = $('#' + tooltipID);
        $(tooltipBox).fadeOut(500);
    });


});