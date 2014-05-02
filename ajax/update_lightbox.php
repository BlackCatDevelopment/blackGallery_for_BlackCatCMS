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

$user       = CAT_Users::getInstance();

if(!$user->is_authenticated())
{
    print json_encode(array(
		'message'	=> $val->lang()->translate('You are not authenticated, please login!'),
		'success'	=> false
	));
    exit();
}

$val        = CAT_Helper_Validate::getInstance();
$section_id = $val->sanitizePost('section_id');

include dirname(__FILE__).'/../init.php';
include dirname(__FILE__).'/../inc/class_foldergallery.inc.php';

$lbox = blackGallery::fgGetLightbox(); // current details
$name = blackGallery::$fg_settings['lightbox'];

// Add JS
if($val->sanitizePost('add_js_file')!='')
{
    $file = $val->sanitizePost('add_js_file'); // file to add
    if(is_array($lbox['js']) && count($lbox['js']) && in_array($file,$lbox['js']))
    {
        print json_encode(array(
            'success' => false,
            'message' => $val->lang()->translate('The Javascript file is already listed')
        ));
        exit();
    }
    else
    {
        $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$file);
        if(file_exists($path))
        {
            $new = ( is_array($lbox['js']) && count($lbox['js']) )
                 ? $lbox['js']
                 : array();
            array_push($new,CAT_Helper_Directory::sanitizePath('/modules/lib_jquery/plugins/'.$file));
            $q   = sprintf(
                'UPDATE `%smod_blackgallery_lboxes` SET `lbox_js`=\'%s\' WHERE `lbox_name`="%s"',
                CAT_TABLE_PREFIX, serialize($new), $name
            );
            $database->query($q);
            print json_encode(array(
                'success' => $database->is_error() ? false : true,
                'message' => $database->is_error() ? $database->get_error() : 'Success'
            ));
            exit();
        }
        else
        {
            print json_encode(array(
                'success' => false,
                'message' => $val->lang()->translate('File not found!')
            ));
            exit();
        }
    }
}

// Remove JS
if($val->sanitizePost('del_js_file')!='')
{
    $file = $val->sanitizePost('del_js_file'); // file to remove
    if( false !== ( $i = array_search($file,$lbox['js'])) )
    {
        array_splice($lbox['js'],$i,1);
        $q   = sprintf(
            'UPDATE `%smod_blackgallery_lboxes` SET `lbox_js`=\'%s\' WHERE `lbox_name`="%s"',
            CAT_TABLE_PREFIX, serialize($lbox['js']), $name
        );
        $database->query($q);
        print json_encode(array(
            'success' => $database->is_error() ? false : true,
            'message' => $database->is_error() ? $database->get_error() : 'Success'
        ));
        exit();
    }
}

// Add CSS
if($val->sanitizePost('add_css_file')!='')
{
    $file  = $val->sanitizePost('add_css_file'); // file to add
    $media = $val->sanitizePost('media');

    foreach($lbox['css'] as $i => $item) {
        if($item['file'] == $file)
        {
            print json_encode(array(
                'success' => false,
                'message' => $val->lang()->translate('The CSS file is already listed')
            ));
            exit();
        }
    }

    foreach( array(
        CAT_PATH.'/modules/lib_jquery/plugins/',
        CAT_PATH.'/modules/lib_jquery/jquery-ui/themes/'
    ) as $base ) {
        $path = CAT_Helper_Directory::sanitizePath($base.$file);
        if(file_exists($path))
        {
            $new = $lbox['css'];
            array_push($new,array('media'=>$media,'file'=> str_ireplace( CAT_Helper_Directory::sanitizePath(CAT_PATH),'',CAT_Helper_Directory::sanitizePath($base.$file) )));
            $q   = sprintf(
                'UPDATE `%smod_blackgallery_lboxes` SET `lbox_css`=\'%s\' WHERE `lbox_name`="%s"',
                CAT_TABLE_PREFIX, serialize($new), $name
            );
            $database->query($q);
            print json_encode(array(
                'success' => $database->is_error() ? false : true,
                'message' => $database->is_error() ? $database->get_error() : 'Success'
            ));
            exit();
        }
    }

    print json_encode(array(
        'success' => false,
        'message' => $val->lang()->translate('File not found!')
    ));
    exit();

}

// remove css
if($val->sanitizePost('del_css_file')!='')
{
    $file = $val->sanitizePost('del_css_file'); // file to remove
    foreach($lbox['css'] as $i => $item) {
        if($item['file'] == $file)
        {
            array_splice($lbox['css'],$i,1);
            $q   = sprintf(
                'UPDATE `%smod_blackgallery_lboxes` SET `lbox_css`=\'%s\' WHERE `lbox_name`="%s"',
                CAT_TABLE_PREFIX, serialize($lbox['css']), $name
            );
            $database->query($q);
            print json_encode(array(
                'success' => $database->is_error() ? false : true,
                'message' => $database->is_error() ? $database->get_error() : 'Success'
            ));
            exit();
        }
    }
    print json_encode(array(
        'success' => false,
        'message' => $val->lang()->translate('File not found!')
    ));
    exit();
}

// JS code
if($val->sanitizePost('js_code') !='' || $val->sanitizePost('template') != '' )
{
    $new_code = $val->sanitizePost('js_code');
    $new_tpl  = $val->sanitizePost('template');
    $q   = sprintf(
        'UPDATE `%smod_blackgallery_lboxes` SET `section_id`=\'%d\' WHERE `lbox_name`="%s"',
        CAT_TABLE_PREFIX, ($val->sanitizePost('global')==1 ? 0 : $section_id), $name
    );
    $database->query($q);
    if($new_code != '')
    {
        $new_code = str_replace("'", "\'", $new_code);
        $q   = sprintf(
            'UPDATE `%smod_blackgallery_lboxes` SET `lbox_code`=\'%s\' WHERE `lbox_name`="%s"',
            CAT_TABLE_PREFIX, $new_code, $name
        );
        $database->query($q);
    }
    if($new_tpl != '')
    {
        $q   = sprintf(
            'UPDATE `%smod_blackgallery_lboxes` SET `lbox_template`=\'%s\' WHERE `lbox_name`="%s"',
            CAT_TABLE_PREFIX, $new_tpl, $name
        );
        $database->query($q);
    }
    print json_encode(array(
        'success' => $database->is_error() ? false : true,
        'message' => $database->is_error() ? $database->get_error() : 'Success'
    ));
    exit();
}

// add new lightbox
if($val->sanitizePost('new_lb')!='')
{
    $name = $val->sanitizePost('new_lb');
    if(!file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$name)))
    {
        print json_encode(array(
            'success' => false,
            'message' => $val->lang()->translate('File not found!')
        ));
        exit();
    }
    $q = sprintf(
        'INSERT INTO `%smod_blackgallery_lboxes` ( `section_id`, `lbox_name`, `lbox_path` )
        VALUES ( "%d", "%s", "%s" )',
        CAT_TABLE_PREFIX, $section_id, $name, '/modules/lib_jquery/plugins/'.$name
    );
    $database->query($q);
    if(!$database->is_error())
    {
        $q = sprintf(
            'UPDATE `%smod_blackgallery_settings` SET `set_value`="%s" WHERE `set_name`="%s"',
            CAT_TABLE_PREFIX, $name, 'lightbox'
        );
        $database->query($q);
    }
    print json_encode(array(
        'success' => $database->is_error() ? false : true,
        'message' => $database->is_error() ? $database->get_error() : 'Success'
    ));
    exit();
}