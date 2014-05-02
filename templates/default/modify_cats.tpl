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
{if $cat_tree}
<p>
 {translate('Double click on a list item to edit. Drag & drop to sort.')}
</p>

<table>
    <tr>
        <th style="width:50%" class="fc_gradient1">&nbsp;</th>
        <th class="fc_gradient1">
            <table style="width:400px;border:0;">
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
   {translate('Currently selected root folder')}: <span style="color:#407cb4;">{$settings.root_dir}</span><br />
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
        <label for="cat_pic">{translate('Category picture')}</label>
            <select id="cat_pic" name="cat_pic">
                <option value="">{translate('use global default')}</option>
                <option value="first">{translate('first')}</option>
                <option value="last">{translate('last')}</option>
                <option value="random">{translate('random')}</option>
                <option value="spec">{translate('specify')}</option>
            </select>
            <select id="cat_pic_select" name="cat_pic_select" style="display:none;">
             
            </select><br />
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

        var depth = 0;
        $('ul.cattree_0').sortable({
            axis: 'y'
            ,connectWith: ".ui-sortable"
            ,containment: 'parent'
            ,cursor: "move"
            ,forcePlaceholderSize: true
            ,handle: 'div'
            ,items: '> li'
            ,opacity: 0.5
            ,placeholder: "ui-sortable-placeholder"
            ,revert: true
            ,tolerance: "pointer"
            ,toleranceElement: '> div'
            ,start: function( event, ui ) {
                depth = ui.item.parentsUntil($('ul.cattree_0')).length;
                console.log("start depth:" + depth);
                ui.placeholder.height(ui.item.height());
                $('ul.cattree_0').css('padding-bottom',(ui.item.height()+15)+"px");
            }
            ,stop: function( event, ui ) {
                console.log('stop depth : ' + ui.item.parentsUntil($('ul.cattree_0')).length);
                if(ui.item.parentsUntil($('ul.cattree_0')).length != depth)
                {
                    $(this).sortable('cancel');           // cancel the sorting!
                }
                $('ul.cattree_0').css('padding-bottom','15px');
            }
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
            // nestedSortable options
            //,isTree: true
            //,listType: 'ul'
            //,protectRoot: true
        }).disableSelection();

        $('ul.cattree li').dblclick(function(event) {
            event.stopPropagation();
            var cat_id = $(this).prop('id').replace('cat_','');
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
            if(typeof $.uniform != 'undefined') {
                $.uniform.update('input#is_active');
                $.uniform.update('input#allow_fe_upload');
            }
            $('div#options input#cat_id').val(cat_id);
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