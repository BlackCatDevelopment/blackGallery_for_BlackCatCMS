<div class="fgactions" style="margin-right: {$cat.level * 0.55}em">
    <span class="first cat_is_active icon icon-{if $cat.is_active=='0'}cancel{else}checkmark{/if}"></span>
    <span class="cat_allow_fe_upload icon icon-{if $cat.allow_fe_upload=='0'}cancel{else}checkmark{/if}"></span>
    <span>{$cat.subdirs}</span>
    <span>{$cat.cnt}</span>
</div>
