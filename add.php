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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
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

$user = CAT_Users::getInstance();

$defaults = array(
    'root_dir'         => ( $user->get_user_id() == 1 || (HOME_FOLDERS && $user->get_home_folder()=='') || !HOME_FOLDERS )
                       ? MEDIA_DIRECTORY
                       : $dirh->sanitizePath(MEDIA_DIRECTORY.$user->get_home_folder()),
    'allow_fe_upload'  => 0,
    'allow_overwrite'  => 1,
    'allowed_suffixes' => '',
    'cat_pic'          => 'random',
    'categories_title' => 'Categories',
    'default_action'   => 'cats',
    'default_cat'      => '',
    'exclude_dirs'     => '',
    'images_title'     => 'Images',
    'lightbox'         => 'Slimbox2',
    'max_file_size'    => '10000',
    'show_empty'       => 0,
    'thumb_foldername' => '.thumbs',
    'thumb_height'     => 80,
    'thumb_method'     => 'fit',
    'thumb_width'      => 80,
    'use_skin'         => 'default',
    'view_title'       => 'blackGallery',
);

foreach($defaults as $key => $value )
{
    $q = sprintf(
        'INSERT INTO `%smod_blackgallery_settings` ( `section_id`, `set_name`, `set_value` ) VALUES
        ( "%d", "%s", "%s" )',
        CAT_TABLE_PREFIX, $section_id, $key, $value
    );
    $database->query($q);
}

// create root cat
$database->query(sprintf(
    'INSERT INTO `%smod_blackgallery_categories` ( `section_id`, `folder_name`, `cat_name`, `description`, `level` ) VALUES
    ( "%d", "%s", "%s", "%s", "%s" )',
    CAT_TABLE_PREFIX, $section_id, '/', "Root", "Gallery root", "-1"
));