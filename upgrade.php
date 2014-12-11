<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          BlackBird Webprogrammierung
 *   @copyright       2014, BlackBird Webprogrammierung
 *   @link            http://www.webbird.de
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         blackGallery
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// renamed (v0.9)
if(file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/blackGallery/inc/class_foldergallery.inc.php')))
    unlink(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/blackGallery/inc/class_foldergallery.inc.php'));
// moved (v0.9)
if(file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/blackGallery/css/frontend.css')))
    unlink(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/blackGallery/css/frontend.css'));
$database->query(
    "ALTER TABLE `:prefix:mod_blackgallery_categories`
	CHANGE COLUMN `allow_fe_upload` `allow_fe_upload` ENUM('no','everyone','be_users') NOT NULL DEFAULT 'no' AFTER `is_empty`;"
);
$database->query(
    "ALTER TABLE `:prefix:cat_mod_blackgallery_images`
	CHANGE COLUMN `caption` `caption` TEXT NULL AFTER `position`;"
);
