<div class="mod_blackgallery">
    <div class="bg_heading">
        {translate($settings.view_title)}
{if $current_cat && $current_cat != 'Root'}
        &raquo; {translate('Category')}: {$current_cat}
{else}
        &raquo; {translate('Categories')}
{/if}
        <hr />
    </div>