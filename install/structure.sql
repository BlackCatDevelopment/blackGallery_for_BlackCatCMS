CREATE TABLE IF NOT EXISTS `__PREFIX__mod_blackgallery_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `folder_name` varchar(255) NOT NULL DEFAULT '',
  `cat_name` varchar(255) NOT NULL DEFAULT '',
  `cat_pic` varchar(255) NOT NULL DEFAULT '',
  `cat_pic_method` VARCHAR(255) NOT NULL DEFAULT '',
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

CREATE TABLE IF NOT EXISTS `__PREFIX__mod_blackgallery_images` (
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

CREATE TABLE IF NOT EXISTS `__PREFIX__mod_blackgallery_settings` (
  `section_id` int(10) NOT NULL,
  `set_name` varchar(50) NOT NULL,
  `set_value` text NOT NULL,
  UNIQUE KEY `section_id_set_name` (`section_id`,`set_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `__PREFIX__mod_blackgallery_lboxes` (
	`lbox_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`section_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`lbox_name` VARCHAR(50) NOT NULL,
	`lbox_path` TEXT NOT NULL,
	`lbox_js` TEXT NOT NULL,
	`lbox_css` TEXT NOT NULL,
	`lbox_code` TEXT NOT NULL,
	`lbox_template` TEXT NOT NULL,
	`lbox_use_default` ENUM('Y','N') NOT NULL DEFAULT 'N',
	PRIMARY KEY (`lbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
