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

include dirname(__FILE__).'/init.php';
include dirname(__FILE__).'/inc/class_foldergallery.inc.php';

if(!is_array(blackGallery::$fg_settings) || !isset(blackGallery::$fg_settings['root_dir']))
{
    echo 'Invalid settings!';
    exit();
}

// global template data
$_tpl_data = array(
    'content'    => '',
    'section_id' => $section_id,
    'settings'   => blackGallery::$fg_settings,
    'IMG_URL'    => CAT_URL.'/modules/blackGallery/images',
    'BASE_URL'   => CAT_URL.blackGallery::$fg_settings['root_dir'],
    'PAGE_LINK'  => CAT_Helper_Page::getLink($page_id),
);

// get all categories that are visible in frontend
$visible_cats = blackGallery::fgGetCategories($section_id);

// ****************************
// figure out which cat to show
// ****************************

// if a cat path is given...
if(isset($_SERVER['PATH_INFO']))
{
    //echo "getting cat from PATH_INFO<br />";
    $cat = blackGallery::fgGetCat($_SERVER['PATH_INFO']);
    if(is_array($cat) && count($cat))
    {
        // child categories
        $cats = blackGallery::fgGetCategories($section_id,false,false,$cat['cat_id']);
    }
}
// default category specified...
elseif(isset(blackGallery::$fg_settings['default_cat']) && blackGallery::$fg_settings['default_cat']!='')
{
    //echo "getting default cat<br />";
    $cat = blackGallery::fgGetCat(
        blackGallery::$fg_settings['default_cat'],
        (is_numeric(blackGallery::$fg_settings['default_cat']) ? true : false)
    );
}
// find first cat having content
else
{
    //echo "getting first cat in list<br />";
    if(is_array($visible_cats) && count($visible_cats))
        $cat = $visible_cats[key($visible_cats)];
    else
        $cat = NULL;
}

$_tpl_data['current_id']    = $cat['cat_id'];
$_tpl_data['current_level'] = $cat['level'];
$_tpl_data['current_cat']   = $cat['cat_name'];
$_tpl_data['current_path']  = $cat['folder_name'];

// category list
$list = CAT_Helper_ListBuilder::getInstance(1)
        ->config(array('__parent_key'=>'parent','__id_key'=>'cat_id'));
$tree = $list->sort($visible_cats,271);

// breadcrumb path
$_tpl_data['path'] = CAT_Helper_ListBuilder::getInstance(1)
                     ->config(array('__parent_key'=>'parent','__id_key'=>'cat_id'))
                     ->breadcrumb($visible_cats,$cat['cat_id']);

// sub categories
$categories = blackGallery::fgGetCategories($section_id,false,false,$cat['cat_id']);
fgGetCategoryPics($categories,$section_id);
$_tpl_data['categories'] = $categories;

// pictures in this category
$_tpl_data['images'] = blackGallery::fgGetImages($section_id,$cat['cat_id']);

// javascript
// Lightbox settings
$lbox = blackGallery::fgGetLightbox();
$_tpl_data['javascript_code'] = isset($lbox['lbox_code']) ? $lbox['lbox_code']: '';

$_tpl_data['li_width'] = blackGallery::$fg_settings['thumb_width'] + 9;
$_tpl_data['li_height'] = blackGallery::$fg_settings['thumb_height'] + 9;

$parser->output('view',$_tpl_data);

function fgGetCategoryPics(&$categories,$section_id)
{
    // get category pics
    foreach($categories as $cat_path => $cat)
    {
        if(isset($categories[$cat_path]['cat_pic']) && $categories[$cat_path]['cat_pic'] != '')
        {
            continue;
        }
        else
        {
            // get all images
            $img  = blackGallery::fgGetImages($section_id,$categories[$cat_path]['cat_id']);
            if(is_array($img) && count($img))
            {
                switch(blackGallery::$fg_settings['cat_pic'])
                {
                    case 'first':
                        $item = $img[key($img)];
                        break;
                    case 'last':
                        end($img);
                        $item = $img[key($img)];
                        break;
                    case 'random':
                    default:
                        $item = array_rand($img);
                        break;
                }
                $img[$item]['file_name'] = utf8_decode($img[$item]['file_name']);
                if(file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir'].'/'.utf8_decode($cat_path).'/'.$img[$item]['file_name'])))
                {
                    if(!file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir'].'/'.utf8_decode($cat_path).'/'.blackGallery::$fg_settings['thumb_foldername'].'/thumb_'.$img[$item]['file_name'])))
                        CAT_Helper_Image::getInstance()->make_thumb(
                            CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir'].'/'.utf8_decode($cat_path).'/'.$img[$item]['file_name']),
                            CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir'].'/'.utf8_decode($cat_path).'/'.blackGallery::$fg_settings['thumb_foldername'].'/thumb_'.$img[$item]['file_name']),
                            blackGallery::$fg_settings['thumb_width'],
                            blackGallery::$fg_settings['thumb_height'],
                            blackGallery::$fg_settings['thumb_method']
                        );
                    $categories[$cat_path]['cat_pic'] = $cat_path.'/'.blackGallery::$fg_settings['thumb_foldername'].'/thumb_'.utf8_encode($img[$item]['file_name']);
                }
            }
        }
    }
}   // end function fgGetCategoryPics()