<form action="{$CAT_URL}/modules/blackGallery/ajax/sync_categories.php" enctype="application/x-www-form-urlencoded " method="get" class="fgAjaxForm">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <button type="submit">
        <img src="{$IMG_URL}/reload.png" /> {translate('Sync categories')}
    </button>
</form>

<br /><br />
{if $categories}
<p>
 {translate('Double click on a list item to edit. Drag & drop to sort.')}
</p>
<table>
    <thead>
        <tr>
            <th class="fc_gradient1">{translate('Active')}</th>
            <th class="fc_gradient1">&nbsp;</th>
            <th class="fc_gradient1">{translate('Category name')}</th>
            <th class="fc_gradient1">{translate('Allow frontend upload')}</th>
            <th class="fc_gradient1">{translate('Subfolders')}</th>
            <th class="fc_gradient1">{translate('Pictures')}</th>
        </tr>
    </thead>
    <tbody>
    {foreach $categories cat}
        <tr id="cat_{$cat.id}" class="editable{if $dwoo.foreach.default.index % 2} stripe{/if}">
            <td><span class="cat_is_active icon icon-{if $cat.is_active=='0'}cancel{else}checkmark{/if}"></span></td>
            <td style="min-width:25px;"><span class="ui-icon ui-icon-arrowthick-2-n-s hidden" ></span></td>
            <td>
                {if $cat.level > 0}<span class="indent" style="margin-left:{$cat.level*20-20}px">{/if}
                    {if $cat.level > 0}<span class="arrow"></span>{/if}<span class="cat_name" title="{$cat.description}">{$cat.cat_name}</span><br />
                    <span class="small">{$cat.folder_name}</span>
                {if $cat.level > 0}</span>{/if}
            </td>
            <td><span class="cat_allow_fe_upload icon icon-{if $cat.allow_fe_upload=='0'}cancel{else}checkmark{/if}"></span></td>
            <td>{$cat.subdirs}</td>
            <td>{$cat.cnt}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
<div id="cat_options" style="display:none" title="{translate('Category options')}">
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
    </form>
</div>
{else}
{translate('No categories found')}
{/if}

<script charset=windows-1250 type="text/javascript">
if(typeof jQuery != 'undefined') {
    jQuery(document).ready(function($) {
        var afterSend		= function()
    	{
    		location.reload(true);
    	};
        $('span.cat_name').tooltip({
            track:    true,
            content:  function() {
                return  $( this ).attr( "title" );
            }
        });
        $('tbody').sortable({
            axis: "y"
            ,revert: true
            ,cursor: "move"
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
        });
        $('tbody').disableSelection();
        $('tbody tr').mouseenter(function() {
            $(this).find('.ui-icon-arrowthick-2-n-s').removeClass('hidden');
        }).mouseleave(function() {
            $(this).find('.ui-icon-arrowthick-2-n-s').addClass('hidden');
        });
        $('tr.editable').dblclick(function() {
            var cat_id = $(this).prop('id').replace('cat_','');
            var _that  = $(this);
            var descr  = $( '#' + $(this).find('span.cat_name').attr('aria-describedby') ).children().html();
            if(typeof descr=='undefined')
            {
                descr = $(this).find('span.cat_name').prop('title');
            }
            $('div#cat_options input#cat_name').val($(this).find('span.cat_name').text());
            $('div#cat_options textarea#description').val(descr);
            if( $(this).find('span.cat_is_active').hasClass('icon-cancel') === false )
            {
                $('div#cat_options input#is_active').prop('checked','checked');
            }
            else
            {
                $('div#cat_options input#is_active').prop('checked','');
            }
            if( $(this).find('span.cat_allow_fe_upload').hasClass('icon-cancel') === false )
            {
                $('div#cat_options input#allow_fe_upload').prop('checked','checked');
            }
            else
            {
                $('div#cat_options input#allow_fe_upload').prop('checked','');
            }
            $('div#cat_options input#cat_id').val(cat_id);
            $('div#cat_options').dialog({
                width: 600
                ,height: 400
                ,modal: true
                ,buttons: {
                    "{translate('Save')}": function() {
                        $(this).dialog("close");
                        $.ajax({
                            type: "POST",
                            url:  $('div#cat_options').find('form').prop('action'),
                            data: $('div#cat_options').find('form').serialize(),
                            success: function() {
                                $(_that).find('span.cat_name').text($('div#cat_options input#cat_name').val());
                                $(_that).find('span.cat_name').prop('title',$('div#cat_options input#description').val());
                                $( '#' + $(_that).find('span.cat_name').attr('aria-describedby') ).children().html($('div#cat_options input#description').val());
                                if($('div#cat_options input#is_active:checked').length)
                                {
                                    $(_that).find('span.cat_is_active').removeClass('icon-cancel').addClass('icon-checkmark');
                                }
                                else
                                {
                                    $(_that).find('span.cat_is_active').removeClass('icon-checkmark').addClass('icon-cancel');
                                }
                                if($('div#cat_options input#allow_fe_upload:checked').length)
                                {
                                    $(_that).find('span.cat_allow_fe_upload').removeClass('icon-cancel').addClass('icon-checkmark');
                                }
                                else
                                {
                                    $(_that).find('span.cat_allow_fe_upload').removeClass('icon-checkmark').addClass('icon-cancel');
                                }
                                $(_that).effect( 'highlight', 1500 );
                            }
                        });
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