{include "fe_header.tpl"}

{if $categories}
    <div class="mod_blackgallery_wrapper">
        <ul class="bgCategories">
            {foreach $categories cat}
        	<li class="bgCat rounded" style="width:{$li_width}px;height:{$li_height}px;">
                <a style="line-height:{$settings.thumb_height}px;" href="{$PAGE_LINK}{$cat.folder_name}"{if ! $cat.cat_pic} class="info"{/if}>
                    {if $cat.cat_pic}
                    <img src="{$BASE_URL}{$cat.cat_pic}" />
                    {else}
                    {translate('No picture(s)')}
                    {/if}
            		<span class="caption rounded gradient1">{if $cat.cat_name}{$cat.cat_name}{else}{$cat.folder_name}{/if}</span>
                </a>
        	</li>
            {/foreach}
        </ul>
    </div>
{/if}

{if $images}
    <br style="clear:both;" />
    <div class="mod_blackgallery_wrapper">
        {include lightbox.tpl}
    </div>
{/if}

{include "fe_footer.tpl"}