if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        // calculate the correct height of the hidden span
        $('ul.bgCategories li,ul.bgLightbox li').find('span.caption').each(function() {
                $(this).css("height","0px");
                $(this).css("display","table-row");
                $(this).css("height",($(this).prop('scrollHeight')+5)+"px");
                $(this).css("max-height",$(this).parent().css("line-height"));
                $(this).css("display","none");
        });
        $('ul.bgCategories li,ul.bgLightbox li').mouseenter(function() {
            $(this).find('span.caption').slideDown();
        }).mouseleave(function() {
            $(this).find('span.caption').slideUp();
        });
    });
}