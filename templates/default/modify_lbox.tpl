<div id="lbox_settings">

    <div style="float:right;width:363px;">
        <button id="fgDelLB" style="float:right;" class="icon icon-minus">
            {translate('Remove this plugin')}
        </button>
        <button id="fgAddLB" style="float:right;" class="icon icon-plus">
            {translate('Add jQuery plugin')}
        </button><br style="clear:right;" /><br />
        <span>{translate('If you need to add a jQuery Plugin first, please use the jQuery Plugin Manager Admin Tool.')}</span>
    </div>

    <span class="label offset">{translate('Lightbox')}</span>
        <select id="lightbox_name" name="lightbox_name" class="rounded">
        {foreach $lboxes name ignore}<option value="{$name}"{if $name == $lbox_name} selected="selected"{/if}>{$name}</option>{/foreach}
        </select>
        <br />

    <span class="label">{translate('Javascript files')}</span>
        <span class="fgAdd fgAddJS icon icon-plus rounded"></span>
        {if $lbox_js && is_array($lbox_js)}
        {foreach $lbox_js file}
        {if ! $dwoo.foreach.default.first}<br /><span class="label" style="width:223px !important;">&nbsp;</span>{/if}
        <span>
            <span class="lbox_js rounded border" title="{$file}">{$file}</span>
            <span class="fgDel fgDelJS icon icon-minus rounded"></span>
        </span>
        {/foreach}
    {/if}
    <br />

    <span class="label">{translate('CSS files')}</span>
        <span class="fgAdd fgAddCSS icon icon-plus rounded"></span>
        {if $lbox_css && is_array($lbox_css)}
        {foreach $lbox_css item}
        {if ! $dwoo.foreach.default.first}<br /><span class="label" style="width:223px !important;">&nbsp;</span>{/if}
        <span>
            <span class="lbox_css rounded border" title="{$item.file}">{$item.file}</span>
            media:
            <select name="lbox_css_media" class="small">
                <option value="screen"{if $item.media=='screen'} selected="selected"{/if}>screen</option>
                <option value="projection"{if $item.media=='projection'} selected="selected"{/if}>projection</option>
                <option value="screen,projection"{if $item.media=='screen,projection'} selected="selected"{/if}>screen,projection</option>
                <option value="all"{if $item.media=='all'} selected="selected"{/if}>all</option>
            </select>
            <span class="fgDel fgDelCSS icon icon-minus rounded"></span>
        </span>
        {/foreach}
        {/if}
    <br />

    <form action="{$CAT_ADMIN_URL}/pages/modify.php" method="post" id="fgLBoxForm">
        <input type="hidden" name="page_id" value="{$page_id}" />
        <input type="hidden" name="do" value="lbox" />
        <input type="hidden" name="save" value="1" />
            <label for="global" class="offset">{translate('Globally available')}</label>
                <input type="checkbox" name="global" id="global" value="1" title="{translate('can be used in all blackGallery sections')}"{if $lbox_section==0} checked="checked"{/if} />
            <br />
            <label for="lbox_code">{translate('Javascript code')}</label>
                <textarea id="lbox_code" name="lbox_code">{$lbox_code}</textarea><br />
            <span class="label offset">{translate('Use default output template')}</span>
                <input type="radio" name="use_default_template" id="use_default_template_yes" value="y"{if $lbox_use_def == 'Y'} checked="checked"{/if} />
                    <label class="small" for="use_default_template_yes">{translate('Yes')}</label>
                <input type="radio" name="use_default_template" id="use_default_template_no" value="n"{if $lbox_use_def == 'N'} checked="checked"{/if} />
                    <label class="small" for="use_default_template_no">{translate('No')}</label><br />
            <div id="lbox_template_input"{if $lbox_use_def == 'Y'} style="display:none;"{/if}>
                <label for="lbox_template">{translate('Output template')}</label>
                    <textarea id="lbox_template" name="lbox_template">{$lbox_template}</textarea><br />
            </div>
            <button class="button" id="fgSave">
                {translate('Save')}
            </button>
    </form>

    <div id="dlgAddJS" style="display:none;">
        {translate('Please choose an item from the list')}
        <select name="">
        {foreach $js_files dir}<option value="{$dir}">{$dir}</option>{/foreach}
        </select>
    </div>

    <div id="dlgAddCSS" style="display:none;">
        {translate('Please choose an item from the list')}
        <select name="dlgAddCSSFile">
        {if $ui_themes}
        <optgroup label="jQuery UI">
            {foreach $ui_themes dir}<option value="{$dir}">{$dir}</option>{/foreach}
        </optgroup>
        <optgroup label="jQuery Plugins">
            {foreach $css_files dir}<option value="{$dir}">{$dir}</option>{/foreach}
        </optgroup>
        </select><br />
        {translate('Media')}
        <select name="dlgAddCSSMedia" class="small">
            <option value="screen"{if $item.media=='screen'} selected="selected"{/if}>screen</option>
            <option value="projection"{if $item.media=='projection'} selected="selected"{/if}>projection</option>
            <option value="screen,projection"{if $item.media=='screen,projection'} selected="selected"{/if}>screen,projection</option>
            <option value="all"{if $item.media=='all'} selected="selected"{/if}>all</option>
        </select>
    </div>

    <div style="display:none;" id="dlgAddLB">
        <select id="newlb">
            {foreach $jq_plugins item}<option value="{$item}">{$item}</option>{/foreach}
        </select><br />
        {translate('Activate as current Lightbox')}
        <input type="checkbox" id="activate_new_lb" value="y" />
    </div>

    <div style="display:none;" id="dlgAlert">
    </div>
</div>

<script charset=windows-1250 type="text/javascript">
    if (typeof CodeMirror == 'function')
    {
        var editor = CodeMirror.fromTextArea(
            document.getElementById("lbox_code"),
            {
                lineNumbers: true,
                mode: "text/javascript"
            });
        var editor2 = CodeMirror.fromTextArea(
            document.getElementById("lbox_template"),
            {
                lineNumbers: true,
                mode: "text/html"
            });
    }
    if ( typeof jQuery != 'undefined' ) {
        jQuery(document).ready(function($) {
            $('#use_default_template_no').click(function(e) {
                $('#lbox_template_input').show();
            });
            $('#use_default_template_yes').click(function(e) {
                $('#lbox_template_input').hide();
            });
            $('select#lightbox_name').change(function(e) {
                $('body').append('<div id="fade"></div>');
                $('#fade').fadeIn();
                window.location = CAT_ADMIN_URL + '/pages/modify.php?page_id={$page_id}&do=lbox&lbox_name=' + $(this).val();
            });
            $('button#fgAddLB').click(function(e) {
                e.preventDefault();
                $('div#dlgAddLB').dialog({
                    modal:true
                    ,width: 600
                    ,buttons: {
                        "{translate('Save')}": function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
            					dataType:	'json',
            					data:		{
                                    section_id : '{$section_id}',
                                    new_lb     : $('div#dlgAddLB').find('select:first').val(),
                                    activate   : $('div#dlgAddLB').find('#activate_new_lb').val(),
                                    _cat_ajax  : 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
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
            $('button#fgDelLB').click(function(e) {
                e.preventDefault();
                $('div#dlgAlert').html(
                    '<span class="icon icon-warning" style="float:left;margin:0 7px 40px 0;color:#f00;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>' +
                    '{translate("Do you really want to remove this Lightbox?")}'
                ).dialog({
                    modal: true,
                    title: cattranslate('Are you sure?'),
                    buttons: {
                        "{translate('Yes')}": function() {
                            $(this).dialog('close');
                            window.location = CAT_ADMIN_URL + '/pages/modify.php?page_id={$page_id}&do=lbox&del=' + $('select#lightbox_name').val();
                        },
                        "{translate('No')}": function() {
                            $(this).dialog('close');
                        }
                    }
                });
            });
            $('span.fgDelJS').click(function(e) {
                var filename = $(this).parent().find('span.lbox_js').prop('title');
                $.ajax(
				{
					type:		'POST',
					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
					dataType:	'json',
					data:		{
                        section_id : '{$section_id}',
                        del_js_file: filename,
                        _cat_ajax  : 1
                    },
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Remove...' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
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
            $('span.fgAddJS').click(function(e) {
                $('div#dlgAddJS').dialog({
                    modal: true
                    ,width: 600
                    ,height: 150
                    ,buttons: {
                        "{translate('Save')}": function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
            					dataType:	'json',
            					data:		{
                                    section_id : '{$section_id}',
                                    add_js_file: $('div#dlgAddJS').find('select:first').val(),
                                    _cat_ajax  : 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
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
            $('span.fgDelCSS').click(function(e) {
                var filename = $(this).parent().find('span.lbox_css').prop('title');
                $.ajax(
				{
					type:		'POST',
					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
					dataType:	'json',
					data:		{
                        section_id : '{$section_id}',
                        del_css_file: filename,
                        _cat_ajax  : 1
                    },
					cache:		false,
					beforeSend:	function( data )
					{
						data.process	= set_activity( 'Remove...' );
					},
					success:	function( data, textStatus, jqXHR  )
					{
                        $('.popup').dialog('destroy').remove();
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
            $('span.fgAddCSS').click(function(e) {
                $('div#dlgAddCSS').dialog({
                    modal: true
                    ,width: 600
                    ,height: 250
                    ,buttons: {
                        "{translate('Save')}": function() {
                            $(this).dialog("close");
                            $.ajax(
            				{
            					type:		'POST',
            					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
            					dataType:	'json',
            					data:		{
                                    section_id  : '{$section_id}',
                                    add_css_file: $('div#dlgAddCSS').find('select:first').val(),
                                    media       : $('div#dlgAddCSS').find('select[name="dlgAddCSSMedia"]').val(),
                                    _cat_ajax   : 1
                                },
            					cache:		false,
            					beforeSend:	function( data )
            					{
            						data.process	= set_activity( 'Adding...' );
            					},
            					success:	function( data, textStatus, jqXHR  )
            					{
                                    $('.popup').dialog('destroy').remove();
                                    if ( data.success === true )
            						{
            							location.reload(true);
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
            $('button#fgSave').click(function(e) {
                e.preventDefault();
                var js_code = $('#lbox_code').val();
                if(typeof editor != 'undefined') {
                    js_code = editor.getValue();
                }
                var tpl_code = $('#lbox_template').val();
                if(typeof editor2 != 'undefined') {
                    tpl_code = editor2.getValue();
                }
                if( js_code != '' || tpl_code != '' || $('global').val() != '' )
                {
                    $.ajax(
    				{
    					type:		'POST',
    					url:		CAT_URL + '/modules/blackGallery/ajax/update_lightbox.php',
    					dataType:	'json',
    					data:		{
                            section_id  : '{$section_id}',
                            js_code     : js_code,
                            template    : tpl_code,
                            _cat_ajax   : 1,
                            lbox_use_default: $('#use_default_template_yes:checked').val(),
                        },
    					cache:		false,
    					beforeSend:	function( data )
    					{
    						data.process	= set_activity( 'Saving...' );
    					},
    					success:	function( data, textStatus, jqXHR  )
    					{
                            $('.popup').dialog('destroy').remove();
                            if ( data.success === true )
    						{
    							location.reload(true);
    						}
    						else {
    							return_error( jqXHR.process , data.message );
    						}
                        }
                    });
                }
                else
                {
                    $('div#dlgAlert').html(
                        '<span class="icon icon-warning" style="float:left;margin:0 7px 40px 0;color:#f00;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>' +
                        '{translate("Nothing to save")}'
                    ).dialog({
                        modal:true
                        ,height:250
                        ,width:250
                        ,buttons: {
                            OK: function() { $(this).dialog('close'); }
                        }
                    });
                }

            });
        });
    }
</script>