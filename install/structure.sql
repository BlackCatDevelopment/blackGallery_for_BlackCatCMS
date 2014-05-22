CREATE TABLE IF NOT EXISTS `cat_mod_blackgallery_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `folder_name` varchar(255) NOT NULL DEFAULT '',
  `cat_name` varchar(255) NOT NULL DEFAULT '',
  `cat_pic` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `subdirs` int(11) NOT NULL DEFAULT '0',
  `is_active` enum('1','0') NOT NULL DEFAULT '1',
  `is_empty` enum('1','0') NOT NULL DEFAULT '1',
  `allow_fe_upload` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_blackgallery_images` (
  `pic_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `file_size` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `caption` text NOT NULL,
  `description` text,
  `is_active` enum('1','0') DEFAULT '1',
  PRIMARY KEY (`pic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_blackgallery_settings` (
  `section_id` int(10) NOT NULL,
  `set_name` varchar(50) NOT NULL,
  `set_value` text NOT NULL,
  UNIQUE KEY `section_id_set_name` (`section_id`,`set_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cat_mod_blackgallery_lboxes` (
  `lbox_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lbox_name` varchar(50) NOT NULL,
  `lbox_path` text NOT NULL,
  `lbox_js` text NOT NULL,
  `lbox_css` text NOT NULL,
  `lbox_code` text NOT NULL,
  `lbox_template` text NOT NULL,
	PRIMARY KEY (`lbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `cat_mod_blackgallery_lboxes` (`lbox_id`, `lbox_name`, `lbox_path`, `lbox_js`, `lbox_css`, `lbox_code`, `lbox_template`)
VALUES (1, 'Slimbox2', '/modules/lib_jquery/plugins/Slimbox2', 'a:1:{i:0;s:59:"/modules/lib_jquery/plugins/Slimbox2/jquery-slimbox2-min.js";}', 'a:1:{i:0;a:2:{s:5:"media";s:6:"screen";s:4:"file";s:56:"/modules/lib_jquery/plugins/Slimbox2/jquery-slimbox2.css";}}', '', '    <h2>{$settings.images_title}</h2>\r\n    <ul class="fgLightbox">\r\n    {foreach $images img}\r\n    	<li class="rounded" style="width:{$li_width}px;height:{$li_height}px;">\r\n    		<a style="line-height:{$settings.thumb_height}px;" rel="lightbox-grouped" href="{$BASE_URL}{$current_path}/{$img.file_name}" title="{$img.caption}">\r\n                <img src="{$BASE_URL}{$current_path}/{$settings.thumb_foldername}/thumb_{$img.file_name}" alt="{$img.caption}" title="{$img.caption}" />\r\n                <span class="caption rounded gradient1">{$img.description}</span>\r\n            </a>\r\n    	</li>\r\n    {/foreach}\r\n    </ul>\r\n    <script charset=windows-1250 type="text/javascript">\r\n    {$javascript_code}\r\n    </script>');

