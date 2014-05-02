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

global $parser, $page_id, $section_id;
$parser->setPath(dirname(__FILE__).'/templates/custom');
$parser->setFallbackPath(dirname(__FILE__).'/templates/default');
$parser->setGlobals(array(
    'url' => $_SERVER['SCRIPT_NAME'].'?page_id='.$page_id,
    'version' => CAT_Helper_Addons::getModuleVersion('blackGallery')
));

require dirname(__FILE__).'/inc/class_foldergallery.inc.php';

if(!function_exists('fgGetSettings'))
{
    function fgGetSettings()
    {
        global $section_id, $current_section, $database;
        if(!$section_id && $current_section)
            $section_id = $current_section;

        // get settings
        $r = $database->query(sprintf(
            'SELECT * FROM `%smod_blackgallery_settings` WHERE `section_id`="%d"',
            CAT_TABLE_PREFIX, $section_id
        ));

        if( $r && $r->numRows() )
        {
            while( false !== ( $row = $r->fetchRow(MYSQL_ASSOC) ) )
            {
                blackGallery::$fg_settings[$row['set_name']] = $row['set_value'];
            }
        }
    }
}

fgGetSettings();