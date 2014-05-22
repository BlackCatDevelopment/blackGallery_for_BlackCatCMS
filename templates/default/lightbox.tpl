    <h2>{$settings.images_title}</h2>
    <ul class="fgLightbox">
    {foreach $images img}
    	<li class="rounded" style="width:{$li_width}px;height:{$li_height}px;">
    		<a style="line-height:{$settings.thumb_height}px;" class="lb_group" rel="lightbox-grouped"
               href="{$BASE_URL}{$current_path}/{$img.file_name}"
               title="{if $img.caption}{$img.caption}{else}{$img.file_name}{/if}">
                <img src="{$BASE_URL}{$current_path}/{$settings.thumb_foldername}/thumb_{$img.file_name}"
                     alt="{$img.caption}"
                     title="{if $img.caption}{$img.caption}{else}{$img.file_name}{/if}" />
                <span class="caption rounded gradient1">{if $img.description}{$img.description}{else}{$img.file_name}{/if}</span>
            </a>
    	</li>
    {/foreach}
    </ul>
    <script charset=windows-1250 type="text/javascript">
    {$javascript_code}
    </script>