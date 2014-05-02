<div class="mod_blackgallery">
    <div class="title rounded gradient1 shadow">
        <h1>{$settings.view_title}</h1>
        {if $current_cat}<h3>{$current_cat}</h3>{/if}
        {if $path}
        <div class="breadcrumb">
        <a href="{$PAGE_LINK}">{translate('Root')}</a>
        {foreach $path item}
        <a href="{$PAGE_LINK}{$item.folder_name}">{$item.cat_name}</a>
        {/foreach}
        </div>
        {/if}
    </div>
    {if $categories}
    <div class="mod_blackgallery_wrapper">
        {include categories.tpl}
    </div>
    <br clear="left" />
    {/if}
    {if $images}
    <div class="mod_blackgallery_wrapper">
        {include lightbox.tpl}
    </div>
    {/if}
</div>