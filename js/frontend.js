if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        $('ul.fgCategories li,ul.fgLightbox li').mouseenter(function() {
            $(this).find('span.caption').slideDown();
        }).mouseleave(function() {
            $(this).find('span.caption').slideUp();
        });
    });
}