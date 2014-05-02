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

$mod_headers = array(
    'frontend' => array(
        'jquery' => array(
			array(
				'core'			=> true,
				'ui'			=> true,
			)
		),
    ),
    'backend' => array(
        'js' => array(
            '/modules/blackGallery/js/nestedSortable/jquery.nestedSortable.js',
        ),
        'css' => array()
    ),
);

// special backend sections
if(CAT_Backend::isBackend())
{
    // upload tab
    if ( CAT_Helper_Validate::sanitizeGet('do') == 'upload' )
    {
        $mod_headers = array(
            'backend' => array(
                'jquery' => array(
                    'ui' => true,
                ),
                'js' => array(
                    '/js/dropzone/dropzone.min.js',
                ),
                'css' => array(
                   array( 'media' => 'screen', 'file' => '/modules/blackGallery/js/dropzone/dropzone.css' ),
                ),
            ),
        );
    }
    // lightbox tab
    if ( CAT_Helper_Validate::sanitizeGet('do') == 'lbox' )
    {
        if(file_exists(CAT_PATH.'/modules/ckeditor4/ckeditor/plugins/codemirror/js/codemirror.js'))
        {
            array_push($mod_headers['backend']['js'],'/modules/ckeditor4/ckeditor/plugins/codemirror/js/codemirror.js');
            array_push($mod_headers['backend']['js'],'/modules/ckeditor4/ckeditor/plugins/codemirror/js/javascript.js');
            array_push($mod_headers['backend']['css'], array('media' => 'screen', 'file' => '/modules/ckeditor4/ckeditor/plugins/codemirror/css/codemirror.css') );
        }
    }
}
// frontend
else
{
    global $current_section;

    include dirname(__FILE__).'/init.php';
    include dirname(__FILE__).'/inc/class_foldergallery.inc.php';

    $lboxes = blackGallery::fgGetLightboxes();
    $name   = blackGallery::$fg_settings['lightbox'];

    if( isset( $lboxes[$name] ) )
    {
        $lbox_data = blackGallery::fgGetLightbox();
        if(isset($lbox_data['css']))
            $mod_headers['frontend']['css'] = $lbox_data['css'];
        if(isset($lbox_data['js']))
            $mod_headers['frontend']['js'] = $lbox_data['js'];
    }
}

