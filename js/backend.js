if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        var afterSend = function()
    	{
    		location.reload(true);
    	};
        $('#root_dir').change(function() {
            $.ajax(
            {
                type:     'GET',
                url:      CAT_URL + '/modules/blackGallery/ajax/get_subdirs.php',
                dataType: 'json',
                data:     { dir: $('#root_dir').val(), _cat_ajax: 1 },
                cache:    false,
                success:  function( data, textStatus, jqXHR )
                {
                    var dirs = data['dirs'];
                    $('#exclude_dirs').empty(); // remove all options
                    for(i=0;i<dirs.length;i++) {
                        $('#exclude_dirs').append('<option value="' + dirs[i] + '">' + dirs[i] + '</option>');
                    }
                }
            });
        });
        $('.fgAjaxForm').each(function()
        {
            dialog_form( $(this), false, afterSend );
        });
    });
}