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

include dirname(__FILE__).'/../init.php';

$cat = $_GET['cat'];
if(!$cat || !is_numeric($cat) || $cat == '' )
    echo json_encode( array( 'success' => 'false' ) );

$section_id = $_GET['section_id'];
if(!$section_id || !is_numeric($section_id) || $section_id == '' )
    echo json_encode( array( 'success' => 'false' ) );

$imgs   = blackGallery::bgGetImages($section_id,$cat,false);
$output = array();

foreach($imgs as $img) {
    $output[] = "<option value=\"".$img['file_name']."\">".$img['file_name']."</option>";
}

echo json_encode( array(
        'success' => true,
        'imgs'    => implode("\n",utf8_encode_all($output))
    ),
    JSON_UNESCAPED_UNICODE
);
exit();

// http://de1.php.net/manual/de/function.json-encode.php#100492
function utf8_encode_all($dat) // -- It returns $dat encoded to UTF8
{
    if (is_string($dat)) return utf8_encode($dat);
    if (!is_array($dat)) return $dat;
    $ret = array();
    foreach($dat as $i=>$d) $ret[$i] = utf8_encode_all($d);
    return $ret;
}