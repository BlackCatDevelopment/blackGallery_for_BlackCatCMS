    {if $settings.images_title}<div class="bg_heading">{$settings.images_title}<hr /></div>{/if}
    <ul class="bgLightbox">
    {foreach $images img}
    	<li class="rounded" style="width:{$li_width}px;height:{$li_height}px;background-color:{$bg_settings.thumb_bgcolor};">
    		<a style="line-height:{$settings.thumb_height}px;" rel="lightbox-grouped" href="{$BASE_URL}{$current_path}/{$img.file_name}" title="{if $img.description}{$img.description}{else}{$img.file_name}{/if}">
                <img src="{$BASE_URL}{$current_path}/{$settings.thumb_foldername}/thumb_{$img.file_name}" alt="{if $img.caption}{$img.caption}{else}{$img.file_name}{/if}" />
                <span class="caption rounded" style="background-color:{$bg_settings.thumb_overlay};">{if $img.description}{$img.description}{else}{$img.file_name}{/if}</span>
            </a>
    	</li>
    {/foreach}
    </ul>
    <script charset=windows-1250 type="text/javascript">
    {$javascript_code}
    </script>