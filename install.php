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

// add files to class_secure
$addons_helper = new CAT_Helper_Addons();
foreach(
	array(
		'ajax/del_image.php',
		'ajax/get_subdirs.php',
        'ajax/sync_categories.php',
        'ajax/sync_images.php',
        'ajax/sync_thumbs.php',
        'ajax/update_cat.php',
        'ajax/update_image.php',
        'ajax/update_lightbox.php',
        'ajax/upload.php',
	)
	as $file
) {
	if ( false === $addons_helper->sec_register_file( 'blackGallery', $file ) )
	{
		 error_log( "Unable to register file -$file-!" );
	}
}

$import = file_get_contents( dirname(__FILE__)."/install/structure.sql" );
_ar_import($import);

// check if lightbox already there
$lb = CAT_Helper_DB::getInstance()->query(
    'SELECT * FROM `:prefix:mod_blackgallery_lboxes` WHERE `lbox_name`=:name',
    array('name'=>'Slimbox2')
);
if($lb->fetchColumn() !== 1) {
    CAT_Helper_DB::getInstance()->query(
          "INSERT INTO `:prefix:mod_blackgallery_lboxes` (`lbox_id`, `lbox_name`, `lbox_path`, `lbox_js`, `lbox_css`, `lbox_code`, `lbox_template`) "
        . "VALUES (NULL, 'Slimbox2', '/modules/lib_jquery/plugins/Slimbox2', "
        . "'a:1:{i:0;s:59:\"/modules/lib_jquery/plugins/Slimbox2/jquery-slimbox2-min.js\";}', "
        . "'a:1:{i:0;a:2:{s:5:\"media\";s:6:\"screen\";s:4:\"file\";s:56:\"/modules/lib_jquery/plugins/Slimbox2/jquery-slimbox2.css\";}}', "
        . "'', '    <h2>{\$settings.images_title}</h2>\r\n    <ul class=\"fgLightbox\">\r\n"
        . "    {foreach \$images img}\r\n    	<li class=\"rounded\" style=\"width:{\$li_width}px;height:{\$li_height}px;\">\r\n"
        . "    		<a style=\"line-height:{\$settings.thumb_height}px;\" rel=\"lightbox-grouped\" href=\"{\$BASE_URL}{\$current_path}/{\$img.file_name}\" title=\"{\$img.caption}\">\r\n"
        . "                <img src=\"{\$BASE_URL}{\$current_path}/{\$settings.thumb_foldername}/thumb_{\$img.file_name}\" alt=\"{\$img.caption}\" title=\"{\$img.caption}\" />\r\n"
        . "                <span class=\"caption rounded gradient1\">{\$img.description}</span>\r\n            </a>\r\n    	</li>\r\n"
        . "    {/foreach}\r\n    </ul>\r\n    <script charset=windows-1250 type=\"text/javascript\">\r\n"
        . "    {\$javascript_code}\r\n    </script>');"
    );
}

// install the validate jquery plugin
if(!file_exists(CAT_PATH.'/modules/lib_jquery/plugins//jquery.validation'))
{
    $z = CAT_Helper_Zip::getInstance(dirname(__FILE__).'/install/jquery.validation.zip')
         ->config('Path',CAT_PATH.'/modules/lib_jquery/plugins//jquery.validation');
    $z->extract();
}

// create default subfolder
$fulldir = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/media/blackGallery');
if(!CAT_Helper_Directory::isDir($fulldir))
{
    CAT_Helper_Directory::createDirectory($fulldir);
}

function _ar_import($import) {
    global $database;
    $errors = array();
    $import = preg_replace( "%/\*(.*)\*/%Us", ''          , $import );
    $import = preg_replace( "%^--(.*)\n%mU" , ''          , $import );
    $import = preg_replace( "%^$\n%mU"      , ''          , $import );
    $import = preg_replace( "%__PREFIX__%"  , CAT_TABLE_PREFIX, $import );
    $import = preg_replace( "%\r?\n%"       , ''          , $import );
    $import = str_replace ( '\\\\r\\\\n'    , "\n"        , $import );
    $import = str_replace ( '\\\\n'         , "\n"        , $import );
    // split into chunks
    $sql = preg_split(
        '~(insert\s+ignore\s+into\s+|insert\s+into\s+|update\s+|replace\s+into\s+|create\s+table|truncate\s+table|delete\s+from)~i',
        $import,
        -1,
        PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
    );
    if(!count($sql) || !count($sql)%2)
        return false;
    // index 1,3,5... is the matched delim, index 2,4,6... the remaining string
    $stmts = array();
    for($i=0;$i<count($sql);$i++)
        $stmts[] = $sql[$i] . $sql[++$i];
    foreach ($stmts as $imp) {
        if ($imp != '' && $imp != ' ') {
            $ret = $database->query($imp);
            if($database->isError())
                $errors[] = $database->getError();
        }
    }
    return ( count($errors) ? false : true );
}   // end function _ar_import()