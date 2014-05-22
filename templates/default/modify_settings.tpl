<form action="{$CAT_ADMIN_URL}/pages/modify.php" method="post">
    <input type="hidden" name="page_id" value="{$page_id}" />
    <input type="hidden" name="do" value="options" />
    <input type="hidden" name="save" value="1" />
    <fieldset>
        <legend>{translate('Path settings')}</legend>
        <label for="root_dir">{translate('Root folder')}</label>
            <span class="tooltip icon icon-help" title="{translate('This is the folder to start with')}"></span>
            <select id="root_dir" name="root_dir">
            {foreach $fg_settings.arr_root_dir dir}
                <option value="{$dir}"{if isset($selected['root_dir']) && $selected['root_dir'] == $dir}selected="selected"{/if}>{$dir}</option>
            {/foreach}
            </select>
            <br />
        <label for="exclude_dirs">{translate('Exclude dirs')}</label>
            <span class="tooltip icon icon-help" title="{translate('You may exclude subfolders from being listed here')}"></span>
            <select id="exclude_dirs" multiple="multiple" class="bG_resizable" name="exclude_dirs[]">
            {foreach $fg_settings.arr_exclude_dirs dir}
                <option value="{$dir}"{if isset($selected.exclude_dirs) && isset($selected.exclude_dirs.$dir)}selected="selected"{/if}>{$dir}</option>
            {/foreach}
            </select>
            <br />
        <label for="default_cat" id="default_cat">{translate('Default category / folder')}</label>
            <span class="tooltip icon icon-help" title="{translate('This is the default category (shown when the page is opened the first time)')}"></span>
            <select id="default_cat" name="default_cat">
                <option value="/">[{translate('root')}]</option>
            {foreach $fg_settings.arr_exclude_dirs dir}
                {if ! isset($selected.exclude_dirs) || ! isset($selected.exclude_dirs.$dir)}
                <option value="{$dir}"{if $selected.default_cat == $dir}selected="selected"{/if}>{$dir}</option>
                {/if}
            {/foreach}
            </select>
            <br />
        <span class="label">{translate('Show empty categories')}</span>
            <span class="tooltip icon icon-help" title="{translate('In most cases, empty categories (not having sub folders and/or images) should be hidden')}"></span>
            <input type="radio" name="show_empty" id="show_empty_yes" value="1" {if $selected.show_empty == 1}checked="checked"{/if} />
                <label for="show_empty_yes" class="small">{translate('Yes')}</label>
            <input type="radio" name="show_empty" id="show_empty_no" value="0" {if $selected.show_empty == 0}checked="checked"{/if} />
                <label for="show_empty_no" class="small">{translate('No')}</label>
            <br />
        <span class="label">{translate('Allow overwrite')}</span>
            <span class="tooltip icon icon-help" title="{translate('If set to No, images with the same name as existing images will be renamed automatically.')}"></span>
            <input type="radio" name="allow_overwrite" id="allow_overwrite_yes" value="1" {if $selected.allow_overwrite == 1}checked="checked"{/if} />
                <label for="allow_overwrite_yes" class="small">{translate('Yes')}</label>
            <input type="radio" name="allow_overwrite" id="allow_overwrite_no" value="0" {if $selected.allow_overwrite == 0}checked="checked"{/if} />
                <label for="allow_overwrite_no" class="small">{translate('No')}</label>
            <br />
    </fieldset>

    <fieldset>
        <div style="float:right;">
            {translate('Please note: You will have to re-sync all thumbs in all categories if you make any changes here!')}
        </div>
        <legend>{translate('Thumbnail settings')} <span class="tooltip icon icon-help" title="{translate('Thumbnails are reduced-size versions of pictures. The Gallery will auto-create them when new images are uploaded or missing thumbs are identified.')}"></span></legend>
        <label for="thumb_foldername">{translate('Thumb foldername')}</label>
            <span class="tooltip icon icon-help" title="{translate('Thumbnail images are created in this subfolder. The subfolder will always be hidden.')}"></span>
            <input type="text" name="thumb_foldername" value="{$fg_settings.thumb_foldername}" /><br />
        <label for="thumb_height">{translate('Thumb height')}</label>
            <span class="tooltip icon icon-help" title="{translate('Height in Pixel for thumbnail images')}"></span>
            <input type="text" name="thumb_height" value="{$fg_settings.thumb_height}" /><br />
        <label for="thumb_width">{translate('Thumb width')}</label>
            <span class="tooltip icon icon-help" title="{translate('Width in Pixel for thumbnail images')}"></span>
            <input type="text" name="thumb_width" value="{$fg_settings.thumb_width}" /><br />
        <label for="thumb_method">{translate('Thumb creation method')}</label>
            <span class="tooltip icon icon-help" title="{translate('The thumb creation method defines how to handle overflow')}"></span>
            <select name="thumb_method" id="thumb_method">
                <option value="fit">fit ({translate('Fits image into width and height while keeping original aspect ratio')})</option>
                <option value="crop">crop ({translate('Crops image to fill the area while keeping original aspect ratio')})</option>
                <option value="fill">fill ({translate('Fits image into the area without taking care of any ratios. Expect your image to get deformed.')})</option>
            </select>
    </fieldset>

    <fieldset>
        <legend>{translate('Upload options')}</legend>
        <label for="allow_fe_upload">{translate('Allow frontend upload')}</label>
            <span class="tooltip icon icon-help" title="{translate('Allows guest users to add images. Please use with care!')}"></span>
            <select name="allow_fe_upload">
                <option value="no"{if $selected.allow_fe_upload=='no'} selected="selected"{/if}>{translate('No')}</option>
                <option value="be_users"{if $selected.allow_fe_upload=='be_users'} selected="selected"{/if}>{translate('Yes, but backend users only')}</option>
                <option value="everyone"{if $selected.allow_fe_upload=='everyone'} selected="selected"{/if}>{translate('Yes, everyone (attention!)')}</option>
            </select><br />
        <span class="label">{translate('Allowed mime types')}</span>
            <span class="tooltip icon icon-help" title="{translate('The suffix list is derived from the global settings of the CMS')}"></span>
            {foreach $fg_settings.allowed_suffixes suffix}
            {if ! $dwoo.foreach.default.first}<span class="label" style="width:230px !important">&nbsp;</span>{/if}
            <input type="checkbox" id="suffix_{$suffix}" name="suffixes[]" value="{$suffix}"{if in_array($suffix,$fg_settings.suffixes)} checked="checked"{/if} />
            <label for="suffix_{$suffix}" style="text-align:left;">{$suffix}</label><br />
            {/foreach}
        <span class="label">{translate('Image dimensions')}</span>
            <span class="tooltip icon icon-help" title="{translate('Uploaded images are resized to all sizes marked here.')}"></span>
            <input type="checkbox" name="sizes[]" id="size_original" value="original"{if in_array('original',$fg_settings.sizes)} checked="checked"{/if} /> <label style="text-align:left;" for="size_original">{translate('Keep original size')}</label><br />
            <span class="label" style="width:230px !important">&nbsp;</span>
            <input type="checkbox" name="sizes[]" id="size_800_600" value="800_600"{if in_array('800_600',$fg_settings.sizes)} checked="checked"{/if} /> <label style="text-align:left;" for="size_800_600">800 x 600</label><br />
            <span class="label" style="width:230px !important">&nbsp;</span>
            <input type="checkbox" name="sizes[]" id="size_1024_768" value="1024_768"{if in_array('1024_768',$fg_settings.sizes)} checked="checked"{/if} /> <label style="text-align:left;" for="size_1024_768">1024 x 768</label><br />
            <span class="label" style="width:230px !important">&nbsp;</span>
            <input type="checkbox" name="sizes[]" id="size_1600_1000" value="1600_1000"{if in_array('1600_1000',$fg_settings.sizes)} checked="checked"{/if} /> <label style="text-align:left;" for="size_1600_1000">1600 x 1000</label><br />
        <label for="thumb_method">{translate('Resize method')}</label>
            <span class="tooltip icon icon-help" title="{translate('The resize method defines how to handle overflow')}"></span>
            <select name="resize_method" id="resize_method">
                <option value="fit">fit ({translate('Fits image into width and height while keeping original aspect ratio')})</option>
                <option value="crop">crop ({translate('Crops image to fill the area while keeping original aspect ratio')})</option>
                <option value="fill">fill ({translate('Fits image into the area without taking care of any ratios. Expect your image to get deformed.')})</option>
            </select>
    </fieldset>

    <fieldset>
        <legend>{translate('Other settings')}</legend>
        <label for="default_action">{translate('Default backend tab')}</label>
            <span class="tooltip icon icon-help" title="{translate('Tab to open by default when the page is edited')}"></span>
            <select name="default_action" id="default_action">
             <option value="cats">{translate('Categories')}</option>
             <option value="options">{translate('Options')}</option>
             <option value="images">{translate('Images')}</option>
            </select><br />
        <label for="view_title">{translate('Global title')}</label>
            <span class="tooltip icon icon-help" title="{translate('This is the overall title, shown on all pages')}"></span>
            <input type="text" name="view_title" id="view_title" value="{$fg_settings.view_title}" /><br />
        <label for="categories_title">{translate('Categories title')}</label>
            <span class="tooltip icon icon-help" title="{translate('Title for categories overview')}"></span>
            <input type="text" name="categories_title" id="categories_title" value="{$fg_settings.categories_title}" /><br />
        <label for="images_title">{translate('Images title')}</label>
            <span class="tooltip icon icon-help" title="{translate('Title for images overview')}"></span>
            <input type="text" name="images_title" id="images_title" value="{$fg_settings.images_title}" /><br />
        <label for="cat_pic">{translate('Category picture')}</label>
            <span class="tooltip icon icon-help" title="{translate('Image to be shown as placeholder for category (navigation view)')}"></span>
            <select id="cat_pic" name="cat_pic">
            {foreach $fg_settings.cat_pic val}
                <option value="{$val}"{if isset($selected['cat_pic']) && $selected['cat_pic'] == $val} selected="selected"{/if}>{translate($val)}</option>
            {/foreach}
            </select><br />
        <label for="lightbox">{translate('Lightbox')}</label>
            <span class="tooltip icon icon-help" title="{translate('Lightbox plugin to use; you can add and configure the Lightbox plugins on the Lightbox tab')}"></span>
            <select id="lightbox" name="lightbox">
            {foreach $fg_settings.arr_lboxes box}<option value="{$box}"{if $box==$selected.lightbox} selected="selected"{/if}>{$box}</option>{/foreach}
            </select><br />
        <label for="use_skin">{translate('Use skin')}</label>
            <span class="tooltip icon icon-help" title="{translate('The skin is a set of output templates. By default, only one skin is installed, but you can create your own.')}"></span>
            <select id="use_skin" name="use_skin">
                <option value="default">{translate('default')}</option>
                <option value="custom">{translate('custom')}</option>
            </select><br />
    </fieldset>
    <button>
        {translate('Save')}
    </button>
</form>
<div id="fgDlg" title="{translate('Please note!')}" style="display:none;">
    <span class="icon icon-warning" style="float:left;margin:0 7px 40px 0;color:#f00;font-size:1.4em;text-shadow: 3px 3px 3px #ccc;"></span>
    {translate('You will loose all category and picture settings if you change the root directory!')}
</div>

<script charset="windows-1250" type="text/javascript">
    if(typeof jQuery != 'undefined')
    {
        jQuery(document).ready(function($) {
            $('.bG_resizable').not('select').resizable({
                helper: "ui-resizable-helper"
                ,minWidth: 390
                ,maxWidth: 800
                ,minHeight: 22
                ,maxHeight: 24
            });
            $('select.bG_resizable').resizable({
                helper: "ui-resizable-helper"
                ,minWidth: 390
                ,minHeight: 22
            });
            $('.tooltip').tooltip({
                content: function() {
                    return $( this ).attr( "title" );
                },
                position: {
                    my: "center bottom-20",
                    at: "center top",
                    using: function( position, feedback ) {
                        $( this ).css( position );
                        $( "<div>" )
                        .addClass( "arrow" )
                        .addClass( feedback.vertical )
                        .addClass( feedback.horizontal )
                        .appendTo( this );
                    }
                }
            });
// for styling, activate next line (opens first tooltip on page)
//$( ".tooltip:first" ).tooltip( "open" );
            $('select#root_dir').change(function(e) {
                $('div#fgDlg').dialog({
                    modal:true
                    ,buttons: {
                        "{translate('Ok')}": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        });
    }
</script>