if(typeof jQuery != 'undefined')
{
    jQuery(document).ready(function($) {
        $uniformed = $("form").find("input,textarea,select").not(".skip");
        $uniformed.uniform();
    });
}
