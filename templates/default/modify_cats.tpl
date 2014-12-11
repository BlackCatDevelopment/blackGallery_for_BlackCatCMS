<form action="{$CAT_URL}/modules/blackGallery/ajax/sync_categories.php" method="get" class="fgAjaxForm">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <button type="submit">
        <img src="{$IMG_URL}/reload.png" /> {translate('Sync categories')}
    </button>
</form>

<form action="{$CAT_URL}/modules/blackGallery/ajax/sync_images.php" method="post" class="fgAjaxForm">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <input type="hidden" name="all" value="true" />
    <button type="submit">
        <img src="{$IMG_URL}/reload.png" /> {translate('Sync all images')}
    </button>
</form>

<br /><br />

<div>
{translate('The categories are based upon the folder hierarchy in the root folder, so it is not possible to add or delete categories here.')}
{translate('You may deactivate subfolders that are not to be used for the gallery.')}
</div>


{if $cat_tree}
<p>
 {translate('Double click on a list item to edit. Drag & drop to sort.')}
</p>

<table>
    <tr>
        <th style="width:50%" class="fc_gradient1">&nbsp;</th>
        <th class="fc_gradient1">
            <table style="width:500px;border:0;">
                <th>{translate('Edit')}</th>
                <th>{translate('Active')}</th>
                <th>{translate('Frontend upload')}</th>
                <th>{translate('Subfolders')}</th>
                <th>{translate('Pictures')}</th>
            </table>
        </th>
    </tr>
</table>

{$cat_tree}
{else}
<div class="fginfo rounded border">
   <span class="icon icon-warning"></span>
   {translate('No categories found, try [Sync categories] button!')}<br />
   {translate('Please make sure to select a root folder first!')} <span class="icon icon-arrow-right"></span> <a href="{$CAT_ADMIN_URL}/pages/modify.php?page_id={$page_id}&amp;do=options">{translate('Options')}</a><br />
</div>
{/if}

<div id="options" style="display:none" title="{translate('Category options')}">
    <form id="form_cat_options" action="{$CAT_URL}/modules/blackGallery/ajax/update_cat.php" method="post">
        <input type="hidden" name="cat_id" id="cat_id" value="" />
        <input type="hidden" name="section_id" value="{$section_id}" />
        <input type="hidden" name="_cat_ajax" value="1" />
        <label for="cat_name">{translate('Category name')}</label>
            <input type="text" name="cat_name" id="cat_name" value="" /><br />
        <label for="description">{translate('Description')}</label>
            <textarea id="description" name="description"></textarea>
        <label for="is_active">{translate('Active')}</label>
            <input type="checkbox" id="is_active" name="is_active" value="1" /><br />
        <label for="allow_fe_upload">{translate('Allow frontend upload')}</label>
            <input type="checkbox" id="allow_fe_upload" name="allow_fe_upload" value="1" /><br />
        <label for="cat_pic_method">{translate('Category picture')}</label>
            <select id="cat_pic_method" name="cat_pic_method">
                <option value="">{translate('use global default')}</option>
                <option value="first">{translate('first')}</option>
                <option value="last">{translate('last')}</option>
                <option value="random">{translate('random')}</option>
                <option value="spec">{translate('specify')}</option>
            </select><br />
        <div id="cat_pic_select_div" style="display:none;">
        <label for="cat_pic_select">{translate('Category picture')}</label>
            <select id="cat_pic_select" name="cat_pic_select">
             
            </select><br />
        </div>
    </form>
</div>

<script charset=windows-1250 type="text/javascript">
if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        $('ul.cattree li').tooltip({
            track:    true,
            content:  function() {
                return  $( this ).attr( "title" );
            }
        });

        function fg_update_pic_select(cat_id) {
            var section_id = $('input[name="section_id"]').val();
            $.ajax({
                type    : "GET",
                url     :  CAT_URL + '/modules/blackGallery/ajax/get_pics.php',
                dataType: 'json',
                data    : { cat: cat_id, section_id: section_id },
                cache   : false,
                success : function( data, textStatus, jqXHR  )
				{
                    if ( data.success === true )
					{
                        $('select#cat_pic_select').html(data.imgs);
                        $('#cat_pic_select_div').show();
					}
                }
            });
        }

        $('span.cat_is_active').click(function() {
            var cat_id = $(this).parent().parent().parent().find('div').prop('id').replace('div_cat_','');
            var _that  = $(this);
            $.ajax({
                type    : "POST",
                url     :  CAT_URL + '/modules/blackGallery/ajax/update_cat.php',
                dataType: 'json',
                data    : { cat_id: cat_id, switch_active: true },
                cache   : false,
                success : function( data, textStatus, jqXHR  )
				{
                    if ( data.success === true )
					{
                        if( $(_that).hasClass('icon-cancel') )
                        {
                            $(_that).removeClass('icon-cancel').addClass('icon-checkmark');
                        }
                        else
                        {
                            $(_that).removeClass('icon-checkmark').addClass('icon-cancel');
                        }
					}
                }
            });
        });

        $('.icon-tools').click(function() {
            $(this).parent().parent().parent().trigger('dblclick');
        });

        var depth = 0;
        $('ul.cattree').sortable({
            axis: 'y'
            ,containment: "parent"
            ,cursor: "move"
            ,items: '> li'
            ,opacity: 0.5
            ,placeholder: "ui-sortable-placeholder"
//            ,revert: true
            ,tolerance: "pointer"
            ,update: function( event, ui ) {
                var dates = {
					'order':			$(this).sortable('toArray'),
                    'section_id':       '{$section_id}',
                    '_cat_ajax':        1
				};
				$.ajax(
				{
					type:		'POST',
					url:		CAT_URL + '/modules/blackGallery/ajax/update_cat.php',
					dataType:	'json',
					data:		dates,
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Reorder categories' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
                        if ( data.success === true )
						{
							return_success( jqXHR.process, data.message );
						}
						else {
							return_error( jqXHR.process , data.message );
						}
                    }
                });
            }
       }).disableSelection();

        $('ul.cattree li').dblclick(function(event) {
            event.stopPropagation();
            $('#cat_pic_select_div').hide();
            var cat_id = $(this).find('div:first').prop('id').replace('div_cat_','');
            var _that  = $(this);
            var descr  = $( '#' + $(this).find('div').attr('aria-describedby') ).children().html();
            if(typeof descr=='undefined')
            {
                descr = $(this).find('div').prop('title');
            }
            $('div#options input#cat_name').val( $(this).find('span.cat_name:first').text() );
            $('div#options textarea#description').val(descr);
            if( $(this).find('div.fgactions span.cat_is_active:first').hasClass('icon-cancel') === false )
            {
                $('div#options input#is_active').prop('checked','checked');
            }
            else
            {
                $('div#options input#is_active').prop('checked','');
            }
            if( $(this).find('span.cat_allow_fe_upload:first').hasClass('icon-cancel') === false )
            {
                $('div#options input#allow_fe_upload').prop('checked','checked');
            }
            else
            {
                $('div#options input#allow_fe_upload').prop('checked','');
            }
            if( $('select#cat_pic_method').val() == 'spec') {
                fg_update_pic_select(cat_id);
            }
            $('select#cat_pic_method').change( function() {
                if( $(this).val() == 'spec') {
                    fg_update_pic_select(cat_id);
                }
            });
            if(typeof $.uniform != 'undefined') {
                $.uniform.update('input#is_active');
                $.uniform.update('input#allow_fe_upload');
            }
            $('div#options input#cat_id').val(cat_id);
            // disable ENTER key
            $('div#options form').submit(function () { return false; });
            // bind ENTER to dialog
            $(document).delegate('.ui-dialog', 'keyup', function(e) {
                var tagName = e.target.tagName.toLowerCase();
                tagName = (tagName === 'input' && e.target.type === 'button') ? 'button' : tagName;
                if (e.which === $.ui.keyCode.ENTER && tagName !== 'textarea' && tagName !== 'select' && tagName !== 'button') {
                    $(this).find('.ui-dialog-buttonset button').eq(0).trigger('click');
                    return false;
                }
            });
            $('div#options').dialog({
                width: 600
                ,height: 400
                ,modal: true
                ,buttons: {
                    "{translate('Save')}": function() {
                        $(this).dialog("close");
                        $.ajax({
                            type: "POST",
                            url:  $('div#options').find('form').prop('action'),
                            data: $('div#options').find('form').serialize(),
                            success: function() {
                                $(_that).find('span.cat_name:first').text($('div#options input#cat_name').val());
                                $(_that).find('span.cat_name:first').parent().prop('title',$('div#options textarea#description').val());
                                $( '#' + $(_that).find('span.cat_name:first').parent().attr('aria-describedby') ).children().html($('div#options input#description').val());
                                if($('div#options input#is_active:checked').length)
                                {
                                    $(_that).find('span.cat_is_active:first').removeClass('icon-cancel').addClass('icon-checkmark');
                                }
                                else
                                {
                                    $(_that).find('span.cat_is_active:first').removeClass('icon-checkmark').addClass('icon-cancel');
                                }
                                if($('div#options input#allow_fe_upload:checked').length && $('div#options input#is_active:checked').length)
                                {
                                    $(_that).find('span.cat_allow_fe_upload:first').removeClass('icon-cancel').addClass('icon-checkmark');
                                }
                                else
                                {
                                    $(_that).find('span.cat_allow_fe_upload:first').removeClass('icon-checkmark').addClass('icon-cancel');
                                }
                                $(_that).effect( 'highlight', { color: '#407cb4' }, 1500 );
                            }
                        });
                        //location.reload(true);
                        return false;
                    },
                    "{translate('Cancel')}": function() {
                        $(this).dialog("close");
                    }
                },
            });
        });
    });
}
</script>