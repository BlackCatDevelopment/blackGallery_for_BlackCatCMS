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

$val        = CAT_Helper_Validate::getInstance();
$section_id = $val->sanitizePost('section_id');
include dirname(__FILE__).'/../init.php';

$cat_id     = $val->sanitizePost('cat_id');
$reorder    = $val->sanitizePost('order');

if($reorder && $reorder!='')
{
    $data = array();
    foreach($reorder as $item)
        $data[] = str_replace('cat_','',$item);
    require(CAT_PATH . '/framework/class.order.php');
    $order		= new order(CAT_TABLE_PREFIX.'mod_blackgallery_categories', 'position', 'cat_id');
    $reordered	= $order->reorder_by_array( $data );
    if ( $reordered === true )
    {
    	$ajax	= array(
    		'message'	=> $val->lang()->translate('Re-ordered successfully'),
    		'success'	=> true
    	);
    }
    else
    {
        $ajax	= array(
    		'message'	=> $val->lang()->translate( $reordered.': Error re-ordering categories'),
    		'success'	=> false
    	);
    }
	print json_encode( $ajax );
	exit();
}
elseif($val->sanitizePost('switch_active'))
{
    $current     = blackGallery::fgGetCatDetail($cat_id,'is_active');
    $database->query(sprintf(
        'UPDATE `%smod_blackgallery_categories` SET `is_active`="%s" WHERE `cat_id`="%d"',
        CAT_TABLE_PREFIX, ( $current == 0 ? 1 : 0 ), $cat_id
    ));
    print json_encode(array(
        'success' => ( $database->is_error() ? false : true ),
        'message' => ( $database->is_error() ? $database->get_error() : 'Success' )
    ));
    exit();
}
else
{
    $cat_name    = $val->sanitizePost('cat_name');
    $description = $val->sanitizePost('description');
    $is_active   = ( $val->sanitizePost('is_active') == 1 )
                 ? 1
                 : 0;
    $allow_fe_up = ( $val->sanitizePost('allow_fe_upload') == 1 && $is_active == 1 )
                 ? 1
                 : 0;
    $cat_pic     = $val->sanitizePost('cat_pic');
    $database->query(sprintf(
        'UPDATE `%smod_blackgallery_categories` SET `cat_name`="%s", `cat_pic`="%s", `description`="%s", `is_active`="%s", `allow_fe_upload`="%s" WHERE `cat_id`="%d"',
        CAT_TABLE_PREFIX, $cat_name, $cat_pic, $description, $is_active, $allow_fe_up, $cat_id
    ));
    print json_encode(array(
        'success' => ( $database->is_error() ? false : true ),
        'message' => ( $database->is_error() ? $database->get_error() : 'Success' )
    ));
    exit();
}