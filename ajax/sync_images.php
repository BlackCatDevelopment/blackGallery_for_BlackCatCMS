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

global $section_id;
$section_id = CAT_Helper_Validate::sanitizePost('section_id');
$cat_id     = CAT_Helper_Validate::sanitizePost('cat_id');
$all        = CAT_Helper_Validate::sanitizePost('all');
$user       = CAT_Users::getInstance();

if(!$user->is_authenticated())
{
    print json_encode(array(
		'message'	=> $val->lang()->translate('You are not authenticated, please login!'),
		'success'	=> false
	));
    exit();
}

include dirname(__FILE__).'/../init.php';
include dirname(__FILE__).'/../inc/class_foldergallery.inc.php';

if(!is_array(blackGallery::$fg_settings) || !isset(blackGallery::$fg_settings['root_dir']))
{
    print json_encode(array(
		'message'	=> $val->lang()->translate('Invalid data, unable to sync!'),
		'success'	=> false
	));
    exit();
}

$root_dir = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir']);
$allowed  = CAT_Helper_Mime::getAllowedFileSuffixes('image/*');

if($all)
{
    $result  = blackGallery::fgSyncAllImages($root_dir,$allowed);
}
else
{
    $result = blackGallery::fgSyncImagesForCat($cat_id,$root_dir,$allowed);
    blackGallery::fgUpdateThumbs($cat_id);
}

print json_encode(array(
    'success' => true,
    'added'   => isset($result['added']) ? $result['added'] : 0,
    'removed' => isset($result['removed']) ? $result['removed'] : 0,
    'message' => 'Success'
));
exit();