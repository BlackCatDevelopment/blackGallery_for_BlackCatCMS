        <h2>{$settings.categories_title}</h2>
        <ul class="fgCategories">
            {foreach $categories cat}
        	<li class="fgCat rounded" style="width:{$li_width}px;height:{$li_height}px;">
                <a style="line-height:{$settings.thumb_height}px;" href="{$PAGE_LINK}{$cat.folder_name}"{if ! $cat.cat_pic} class="info"{/if}>
                    {if $cat.cat_pic}
                    <img src="{$BASE_URL}{$cat.cat_pic}" />
                    {else}
                    {translate('No picture(s)')}
                    {/if}
            		<span class="caption rounded gradient1">{$cat.cat_name}</span>
                </a>
        	</li>
            {/foreach}
        </ul>