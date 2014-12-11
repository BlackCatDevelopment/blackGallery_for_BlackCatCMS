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

global $section_id, $bg_settings;
$section_id = CAT_Helper_Validate::sanitizePost('section_id');
include dirname(__FILE__).'/../init.php';
include dirname(__FILE__).'/../inc/blackGallery.inc.php';

$dir    = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$bg_settings['root_dir']);
$cat    = CAT_Helper_Validate::sanitizePost('cat_id');
$folder = str_ireplace( CAT_Helper_Directory::sanitizePath(blackGallery::$bg_settings['root_dir']), '', blackGallery::bgGetCatPath($cat));

list( $ok, $errors ) = CAT_Helper_Upload::uploadAll(
    'files',
    utf8_decode(CAT_Helper_Directory::sanitizePath($dir.'/'.$folder)),
    true,
    (blackGallery::$bg_settings['allow_overwrite']=='no' ? false : true)
);

print_r($ok);

if(!count($errors) && count($ok))
{
    foreach($ok as $file => $size)
    {
        blackGallery::getDB()->insert(
            array(
                'tables' => 'mod_blackgallery_images',
                'fields' => array('section_id','cat_id','file_name','file_size','position','is_active'),
                'values' => array($section_id,$cat,$file,$size,0,1),
            )
        );
    }
    blackGallery::bgUpdateThumbs($cat);
}

print json_encode(array(
    'success' => ( count($errors) ? false : true ),
    'message' => ( count($errors) ? implode('<br />',$errors) : 'Success' )
));
exit();

