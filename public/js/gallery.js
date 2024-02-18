$(function () {
    let fullsizeImageContainer = $('.fullsize-image-container');
    let htmlBody = $('html, body');

    $(document).on('click', '.uploaded-images-container .image, .fullsize-image-container .next, .fullsize-image-container .prev', function () {
        htmlBody.css('overflow', 'hidden');
        let fullsize_pic = $(this).attr('data-fullsize');
        let imgn = parseInt($(this).attr('data-imgn'));
        $('.prev', fullsizeImageContainer).show();
        if ((imgn - 1) < 0) {
            $('.prev', fullsizeImageContainer).hide();
        }
        $('.next', fullsizeImageContainer).show();
        if ((imgn + 1) == $('.maximages').attr('data-maximages')) {
            $('.next', fullsizeImageContainer).hide();
        }
        fullsizeImageContainer.css('background-size', 'contain').css('background-image', 'url(' + fullsize_pic + ')').attr('data-currentimgn', imgn);
        $('.next', fullsizeImageContainer).attr('data-imgn', (imgn + 1)).attr('data-fullsize', $('.imgn_' + (imgn + 1)).attr('data-fullsize'));
        $('.prev', fullsizeImageContainer).attr('data-imgn', (imgn - 1)).attr('data-fullsize', $('.imgn_' + (imgn - 1)).attr('data-fullsize'));
        fullsizeImageContainer.show().css('top', $(window).scrollTop()).css('height', window.innerHeight);
    });

    $(document).on('click', '.fullsize-image-container .close-gallery', function () {
        fullsizeImageContainer.hide();
        htmlBody.css('overflow', 'auto');
    });

    $(window).on('resize', function () {
        fullsizeImageContainer.css('height', window.innerHeight);
    });
});