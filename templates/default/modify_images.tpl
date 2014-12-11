{if $cat_is_active == 0}
<div class="highlight right">
    {translate('This category is deactivated!')}
</div>
{/if}


<form id="form_sync_images" action="{$CAT_URL}/modules/blackGallery/ajax/sync_images.php" enctype="application/x-www-form-urlencoded " method="post">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <input type="hidden" name="cat_id" value="{$cat_id}" />
    <input type="hidden" name="_cat_ajax" value="1" />
    <button type="submit" id="btn_sync_images" title="{translate('Checks underlying folder for new and/or removed images')}">
        <img src="{$IMG_URL}/reload.png" /> {translate('Sync images')}
    </button>
</form>
<form action="{$CAT_URL}/modules/blackGallery/ajax/sync_thumbs.php" enctype="application/x-www-form-urlencoded " method="get" class="fgAjaxForm">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <input type="hidden" name="cat_id" value="{$cat_id}" />
    <button type="submit">
        <img src="{$IMG_URL}/reload.png" /> {translate('Sync thumbs')}
    </button>
</form><br />
<form id="secselect" action="{$CAT_ADMIN_URL}/pages/modify.php" method="get">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <input type="hidden" name="page_id" value="{$page_id}" />
    <input type="hidden" name="do" value="images" />
    <label for="cat_id">{translate('Choose category')}</label>
    {$cat_select}
</form>

{if $images}
<p>
 {translate('Double click on a list item to edit. Drag & drop to sort.')}
</p>
<div class="heading fc_gradient1">
    <span class="col4">{translate('Active')}</span>
    <span class="col4">{translate('Thumb')}</span>
    <span class="col4">{translate('Edit')}</span>
    <span class="col1" style="width:{$settings.thumb_width}px;">{translate('Image')}</span>
    <span class="col2">{translate('Details')}</span>
</div>
<div class="sortable">
{foreach $images img}
    <div class="preview" id="img_{$img.pic_id}" style="min-height:{$settings.thumb_height}px;">
        <span class="img_is_active icon icon-{if $img.is_active=='0'}cancel{else}checkmark{/if}"></span>
        <span class="icon icon-{if $img._has_thumb==false}cancel{else}checkmark{/if}" title="{translate('Thumbnail image')} {if $img._has_thumb==false}{translate('not')} {/if}{translate('available')}"></span>
        <span class="icon icon-tools"></span>
        <img src="{$BASE_URL}{$img.folder_name}/{if $img._has_thumb!=false}{$settings.thumb_foldername}/thumb_{$img.file_name}{else}{$img.file_name}{/if}" style="width:{$settings.thumb_width}px;" title="{$img.caption}" />
        <span class="dz-details rounded">
            <span class="icon icon-remove" style="float:right;"></span>
            <span class="caption fc_gradient2">"{if $img.caption}{$img.caption}{else}{$img.file_name}{/if}"</span>
            <span class="two_rows">
                <span class="dz-filename">{translate('File name')}: {$img.file_name}</span>
                <span class="size">{if $img.file_size}{string_format($img.file_size/1024, '%.2f')} KiB{/if}</span>
            </span><br />
            {if $img.description}
            <span class="description">{$img.description}</span>
            {/if}
        </span>
        <span class="pointer" style="display:none">
            <span class="icon icon-arrow-up"></span><br />
            <span class="icon icon-arrow-down"></span>
        </span>
    </div><br style="clear:left;" />
{/foreach}
</div>
<div id="options" style="display:none" title="{translate('Image options')}">
    <form id="form_img_options" action="{$CAT_URL}/modules/blackGallery/ajax/update_image.php" method="post">
        <input type="hidden" name="do" value="images" />
        <input type="hidden" name="pic_id" id="pic_id" value="" />
        <input type="hidden" name="section_id" value="{$section_id}" />
        <input type="hidden" name="_cat_ajax" value="1" />
        <label for="img_caption">{translate('Image caption')}</label>
            <input type="text" name="img_caption" id="img_caption" value="" /><br />
        <label for="description">{translate('Description')}</label>
            <textarea id="description" name="description"></textarea><br />
        <label for="is_active">{translate('Active')}</label>
            <input type="checkbox" id="is_active" name="is_active" value="1" /><br />
    </form>
</div>
{else}
<table>
    <thead>
        <tr><th class="fc_gradient1" style="font-size:1.2em;">{translate('Category')}: {$current_cat}</th></tr>
    </thead>
    <tbody>
        <tr><td>{translate('No images (try Sync)')}</td></tr>
    </tbody>
</table>
{/if}

<div style="display:none" id="fgDialog"></div>

<script charset="windows-1250" type="text/javascript">
if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        function editpic(event) {
            event.stopPropagation();
            var pic_id = $(this).parent().prop('id').replace('img_','');
            var _that  = $(this).parent();
            $('div#options input#pic_id').val(pic_id);
            $('div#options input#img_caption').val($(this).parent().find('span.caption').text().replace(/\"/g,''));
            $('div#options textarea#description').val($(this).parent().find('span.description').text());
            if( $(this).parent().find('span.img_is_active:first').hasClass('icon-cancel') === false )
            {
                $('div#options input#is_active').prop('checked','checked');
                if(typeof $.uniform != 'undefined') {
                    $.uniform.update('input#is_active');
                }
            }
            else
            {
                $('div#options input#is_active').prop('checked','');
            }
            $('div#options').dialog({
                width: 600
                ,height: 400
                ,modal: true
                ,buttons: {
                    "{translate('Save')}": function() {
                        $(this).dialog("close");
                        $.ajax(
        				{
        					type:		'POST',
        					url:		CAT_URL + '/modules/blackGallery/ajax/update_image.php',
        					dataType:	'json',
        					data:		$('div#options').find('form').serialize(),
        					cache:		false,
        					beforeSend:	function( data )
        					{
        						data.process	= set_activity( 'Saving...' );
        					},
        					success:	function( data, textStatus, jqXHR  )
        					{
                                $(_that).find('span.caption').text('"' + $('div#options input#img_caption').val() + '"');
                                $(_that).find('span.description').text($('div#options textarea#description').val());
                                if($('div#options input#is_active:checked').length)
                                {
                                    $(_that).find('span.img_is_active').removeClass('icon-cancel').addClass('icon-checkmark');
                                }
                                else
                                {
                                    $(_that).find('span.img_is_active').removeClass('icon-checkmark').addClass('icon-cancel');
                                }
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
                        return false;
                    },
                    "{translate('Cancel')}": function() {
                        $(this).dialog("close");
                    }
                },
            });
        }
        $('select#cat_id').on('change',function() {
            $('form#secselect').submit();
        });
        var afterSend		= function()
    	{
    		location.reload(true);
    	};
        $('span.img_is_active').click(function(e) {
            var action = '';
            if($(this).hasClass('icon-checkmark'))
            {
                action = 'deactivate';
            }
            else
            {
                action = 'activate';
            }
            $.ajax(
			{
				type:		'POST',
				url:		CAT_URL + '/modules/blackGallery/ajax/update_image.php',
				dataType:	'json',
				data:		{ pic_id: $(this).parent().prop('id').replace('img_',''), action: action },
				cache:		false,
				beforeSend:	function( data )
				{
					data.process	= set_activity( 'Saving...' );
				},
				success:	function( data, textStatus, jqXHR  )
				{
                    if ( data.success === true )
				    {
                        location.reload(true);
                    }
                    else {
						return_error( jqXHR.process , data.message );
					}
                }
            });
        });
        $('button#btn_sync_images').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            $.ajax(
			{
				type:		'POST',
				url:		CAT_URL + '/modules/blackGallery/ajax/sync_images.php',
				dataType:	'json',
				data:		$('form#form_sync_images').serialize(),
				cache:		false,
				beforeSend:	function( data )
				{
					data.process	= set_activity( 'Sync...' );
				},
				success:	function( data, textStatus, jqXHR  )
				{
                    if ( data.success === true )
					{
                        if(data.added > 0 || data.removed > 0)
                        {
                            $('div#fgDialog')
                                .html(
                                    '<span class="icon icon-info" style="float:left;margin:0 7px 40px 0;color:#407cb4;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>' +
                                    '{translate("Added")}   ' + data.added + ' {translate("image(s)")}<br />' +
                                    '{translate("Removed")} ' + data.removed + ' {translate("image(s)")}<br />' +
                                    '{translate("Errors")}  ' + data.errors
                                ).dialog({
                                    modal: true
                                    ,buttons: {
                                        "{translate('Close')}": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            location.reload(true);
                        }
                        else
                        {
                            $('div#fgDialog')
                                .html(
                                    '<span class="icon icon-info" style="float:left;margin:0 7px 40px 0;color:#407cb4;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>' +
                                    '{translate("No changes found")}'
                                 )
                                .dialog({
                                    modal: true
                                    ,buttons: {
                                        "{translate('Close')}": function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            return_success( jqXHR.process, data.message );
                        }
					}
					else {
						return_error( jqXHR.process , data.message );
					}
                }
            });
        });
        $('div.preview').dblclick(function(event) {
            $(this).find('span.icon-tools').trigger('click');
        });
        $('span.icon-tools').click(function(event) {
            editpic.call(this, event);
        });
        $('span.icon-remove').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var img_id = $(this).parent().parent().prop('id').replace('img_','');
            var _that  = $(this);
            $('div#fgDialog')
                .html(
                    '<span class="icon icon-warning" style="float:left;margin:0 7px 40px 0;color:#f00;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>' +
                    '{translate("Are you sure that you wish to delete this image? Please note: This will also remove the file from the server!")}'
                )
                .dialog({
                    modal: true
                    ,buttons: {
                        "{translate('Yes')}": function() {
                            $(this).dialog("close");
                            $.ajax(
                			{
                				type:		'POST',
                				url:		CAT_URL + '/modules/blackGallery/ajax/del_image.php',
                				dataType:	'json',
                				data:		{ section_id: '{$section_id}', pic_id: img_id },
                				cache:		false,
                				beforeSend:	function( data )
                				{
                					data.process	= set_activity( 'Deleting...' );
                				},
                				success:	function( data, textStatus, jqXHR  )
                				{
                                    if ( data.success === true )
                					{
                                        $(_that).parent().parent().remove();
                						return_success( jqXHR.process, data.message );
                					}
                					else {
                						return_error( jqXHR.process , data.message );
                					}
                                }
                            });
                        },
                        "{translate('Cancel')}": function() {
                            $(this).dialog("close");
                        }
                    }
                });
        });
        $('div.preview').hover(
            function() {
                $(this).find('span.pointer').show();
            }, function() {
                $( this ).find('span.pointer').hide();
            }
        );
        $('div.sortable').sortable({
            axis: 'y'
            ,containment: "parent"
            ,cursor: "move"
            ,forcePlaceholderSize: true
            ,opacity: 0.5
            ,placeholder: "ui-sortable-placeholder"
            ,revert: true
            ,start: function( event, ui ) {

            }
            ,stop: function( event, ui ) {

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
					url:		CAT_URL + '/modules/blackGallery/ajax/update_image.php',
					dataType:	'json',
					data:		dates,
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Reorder images' );
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
    });
}
</script>
