<div id="mod_blackgallery">
    <div id="tabs">
        <nav class="tabs gradient1">
            <a href="{$url}&amp;do=cats" {if $current_tab=='cats'}class="current"{/if} id="tab_cats">{translate("Categories")}</a>
            <a href="{$url}&amp;do=images" {if $current_tab=='images'}class="current"{/if} id="tab_images">{translate("Images")}</a>
            <a href="{$url}&amp;do=upload" {if $current_tab=='upload'}class="current"{/if} id="tab_upload">{translate("Upload")}</a>
    		<a href="{$url}&amp;do=options" {if $current_tab=='options'}class="current"{/if} id="tab_options">{translate("Options")}</a>
            <a href="{$url}&amp;do=lbox" {if $current_tab=='lbox'}class="current"{/if} id="tab_lbox">{translate("Lightbox")}</a>
            <div style="float:right;padding: 10px 20px 0 40px;">
            {translate('Root folder')}: <span style="color:#407cb4;">{$settings.root_dir}</span><br />
            </div>
    	</nav>
        <div id="inner">