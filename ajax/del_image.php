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

$val        = CAT_Helper_Validate::getInstance();
$user       = CAT_Users::getInstance();
$section_id = $val->sanitizePost('section_id');

if(!$user->is_authenticated())
{
    $ajax	= array(
		'message'	=> $val->lang()->translate('You are not authenticated, please login!'),
		'success'	=> false
	);
}
else
{
    include dirname(__FILE__).'/../init.php';
    $pic_id = $val->sanitizePost('pic_id');
    $data   = blackGallery::bgGetImage($pic_id);
    $file   = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$bg_settings['root_dir'].'/'.$data['folder_name'].'/'.$data['file_name']);
    if(file_exists($file))
    {
        unlink($file);
        $thumb = CAT_Helper_Directory::sanitizePath(
            CAT_PATH.'/'.blackGallery::$bg_settings['root_dir'].'/'.$data['folder_name'].'/'
            .blackGallery::$bg_settings['thumb_foldername'].'/'.$data['file_name']
        );
        if(file_exists($thumb))
            unlink($thumb);
        $q      = sprintf(
            'DELETE FROM `%smod_blackgallery_images` WHERE `pic_id`="%d"',
            CAT_TABLE_PREFIX, $pic_id
        );
        $database->query($q);
        $ajax	= array(
    		'message'	=> $database->is_error() ? 'Unable to delete!' : 'Success',
    		'success'	=> $database->is_error() ? false               : true
    	);
    }
    else
    {
        $ajax	= array(
    		'message'	=> 'File not found!' . ' ('.$file.')',
    		'success'	=> false
    	);
    }
}

print json_encode( $ajax );
	exit();

