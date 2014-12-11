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

include dirname(__FILE__).'/init.php';
include dirname(__FILE__).'/inc/blackGallery.inc.php';

$_tpl_data = array('content'=>'','page_id'=>$page_id,'section_id'=>$section_id,'IMG_URL'=>CAT_URL.'/modules/blackGallery/images','CAT_ADMIN_URL'=>CAT_ADMIN_URL,'settings'=>blackGallery::$bg_settings);
$action    = CAT_Helper_Validate::get('_REQUEST','do');
$c         = blackGallery::bgGetCategories($section_id,true);

if ( ! count(blackGallery::$bg_settings) ) $action = 'options';
if ( ! $action || $action == '' )          $action = blackGallery::$bg_settings['default_action'];

$media_folder
    = ( CAT_Users::get_user_id() == 1 || (HOME_FOLDERS && CAT_Users::get_home_folder()=='') || !HOME_FOLDERS )
    ? MEDIA_DIRECTORY
    : CAT_Helper_Directory::sanitizePath(MEDIA_DIRECTORY.CAT_Users::get_home_folder());

$parser->setGlobals('BASE_URL',CAT_Helper_Validate::sanitize_url(CAT_URL.'/'.blackGallery::$bg_settings['root_dir']));

switch ($action)
{
    case 'options':
        fgShowOptions($media_folder);
        break;
    case 'cats':
        fgRenderCategories($section_id);
        break;
    case 'upload':
        $_tpl_data['max_file_size']  = blackGallery::$bg_settings['max_file_size'];
        $_tpl_data['current_tab']    = "upload";
        $_tpl_data['categories']     = blackGallery::bgGetCategories($section_id,false,false);
        $_tpl_data['content']        = $parser->get('modify_upload',$_tpl_data);
        break;
    case 'lbox':
        fgRenderLightboxForm($section_id);
        break;
    case 'images':
    default:
        fgRenderImages($section_id);
        break;

}

$parser->output('modify',$_tpl_data);


/**
 * show/save settings
 **/
function fgShowOptions($media_folder)
{
    global $_tpl_data, $parser, $page_id, $section_id, $database;
    $_tpl_data['current_tab'] = "options";

    $selected = blackGallery::bgCheckSettings($media_folder);

    if(CAT_Helper_Validate::sanitizePost('save'))
    {
        $old_settings = blackGallery::$bg_settings;
        blackGallery::bgUpdateSettings($section_id);
        // reload settings
        fgGetSettings();
        $selected = blackGallery::bgCheckSettings($media_folder);
        // if lightbox was changed and has own template...
        if($old_settings['lightbox'] != blackGallery::$bg_settings['lightbox'])
            blackGallery::bgSetLightboxTpl();
    }

    $_tpl_data['content']
        = $parser->get(
              'modify_settings',
              array(
                  'page_id'     => $page_id,
                  'selected'    => $selected,
                  'bg_settings' => blackGallery::$bg_settings
              )
          );
}

/**
 * render the category list
 **/
function fgRenderCategories($section_id)
{
    global $_tpl_data, $parser;
    $cats                     = blackGallery::bgGetCategories($section_id,true);
    $_tpl_data['categories']  = $cats;
    if(is_array($cats) && count($cats))
    {
        foreach($cats as $k => $i)
        {
            $add_info = $parser->get('modify_cats_infos',array('cat'=>$cats[$k]));
            $cats[$k]['cat_name'] = '<div title="'.$cats[$k]['description'].'" id="div_cat_'.$i['cat_id'].'">'
                                  . '<span class="cat_name">'.$cats[$k]['cat_name'].'</span>'
                                  . $add_info
                                  . '</div>';
        }
        if(count($cats) > 0)
            $_tpl_data['cat_tree'] = \wblib\wbList::getInstance(
                                         array(
                                             '__id_key'       => 'cat_id',
                                             '__title_key'    => 'cat_name',
                                             '__is_open_key'  => 'cat_id',
                                             'li_id_prefix' => 'cat_',
                                             'li_class'     => 'editable',
                                             'ul_class'     => 'cattree',
                                             'ul_level_css' => true
                                         )
                                     )
                                     ->buildList($cats,array('root_id'=>0));
    }
    $_tpl_data['current_tab'] = "cats";
    $_tpl_data['content']     = $parser->get('modify_cats',$_tpl_data);
}   // end function fgRenderCategories()

function fgRenderImages($section_id)
{
    global $_tpl_data, $parser;

    $cat_id                  = CAT_Helper_Validate::sanitizeGet('cat_id');
    $_tpl_data['categories'] = blackGallery::bgGetCategories($section_id,true);

    if(!$cat_id && is_array($_tpl_data['categories']) && count($_tpl_data['categories']))
        $cat_id = $_tpl_data['categories'][key($_tpl_data['categories'])]['cat_id'];
    if(!$cat_id)
        $cat_id = -1;

    // add folder name to category name for dropdown (if different)
    foreach(array_keys($_tpl_data['categories']) as $i)
    {
        $_tpl_data['categories'][$i]['cat_name'] .= ' ('.blackGallery::bgCountImages($_tpl_data['categories'][$i]['cat_id']).')';
        if($_tpl_data['categories'][$i]['cat_name'] != pathinfo($_tpl_data['categories'][$i]['folder_name'],PATHINFO_BASENAME))
        {
            $_tpl_data['categories'][$i]['cat_name'] .= ' - ('.utf8_encode($_tpl_data['categories'][$i]['folder_name']).')';
        }

    }
    $_tpl_data['cat_id']         = $cat_id;
    $_tpl_data['current_cat']    = blackGallery::bgGetCatName($cat_id);
    $_tpl_data['cat_is_active']  = blackGallery::bgGetCatDetail($cat_id,'is_active');
    $_tpl_data['images']         = blackGallery::bgGetAllImageData($section_id,$cat_id);
    $_tpl_data['current_tab']    = "images";
    $_tpl_data['cat_select']     = CAT_Helper_ListBuilder::getInstance()
                                   ->config(
                                       array(
                                           '__id_key'      => 'cat_id',
                                           '__title_key'   => 'cat_name',
                                           '__is_open_key' => 'cat_id',
                                           'space'         => '--'
                                       ))
                                   ->dropdown(
                                       'cat_id',
                                       $_tpl_data['categories'],
                                       0,
                                       $cat_id
                                   );
    $_tpl_data['content']        = $parser->get('modify_images',$_tpl_data);
}   // end function fgRenderImages()

function fgRenderLightboxForm($section_id)
{
    global $_tpl_data, $parser, $database;

    if(CAT_Helper_Validate::sanitizeGet('del') != '')
    {
        $database->query(sprintf(
            'DELETE FROM `%smod_blackgallery_lboxes` WHERE `lbox_name`="%s"',
            CAT_TABLE_PREFIX,CAT_Helper_Validate::sanitizeGet('del')
        ));
        if(blackGallery::$bg_settings['lightbox'] == CAT_Helper_Validate::sanitizeGet('del'))
        {
            $database->query(sprintf(
                'UPDATE `%smod_blackgallery_settings` SET `set_value`="Slimbox2" WHERE `set_name`="%s" AND `section_id`="%d"',
                CAT_TABLE_PREFIX, 'lightbox', $section_id
            ));
            blackGallery::$bg_settings['lightbox'] = 'Slimbox2';
        }
    }

    $name      = blackGallery::$bg_settings['lightbox'];

    if(CAT_Helper_Validate::sanitizeGet('lbox_name') != '')
        $name  = CAT_Helper_Validate::sanitizeGet('lbox_name');

    $lbox      = blackGallery::bgGetLightbox($name); // Lightbox settings
    $lboxes    = blackGallery::bgGetLightboxes();
    $js_files  = CAT_Helper_Directory::getInstance()
                 ->maxRecursionDepth(5)
                 ->setSuffixFilter(array('js'))
                 ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins',true,true,CAT_PATH.'/modules/lib_jquery/plugins');
    $css_files = CAT_Helper_Directory::getInstance()
                 ->maxRecursionDepth(5)
                 ->setSuffixFilter(array('css'))
                 ->scanDirectory(CAT_PATH.'/modules/lib_jquery/plugins',true,true,CAT_PATH.'/modules/lib_jquery/plugins');
    // add jQuery UI themes
    $ui_themes = CAT_Helper_Directory::getInstance()
                 ->maxRecursionDepth(5)
                 ->setSuffixFilter(array('css'))
                 ->scanDirectory(CAT_PATH.'/modules/lib_jquery/jquery-ui/themes',true,true,CAT_PATH.'/modules/lib_jquery/jquery-ui/themes');
    // jquery plugins list
    $jq_plugins = CAT_Helper_Directory::getInstance()
                 ->maxRecursionDepth(0)
                 ->getDirectories(CAT_PATH.'/modules/lib_jquery/plugins',CAT_PATH.'/modules/lib_jquery/plugins/');
    if(count($jq_plugins))
    {
        $known = array_keys($lboxes);
        // remove already known lightbox plugins
        for($i=count($jq_plugins)-1;$i>=0;$i--)
        {
            if(in_array($jq_plugins[$i],$known))
                unset($jq_plugins[$i]);
        }
    }

    $_tpl_data['js_files']       = $js_files;
    $_tpl_data['css_files']      = $css_files;
    $_tpl_data['ui_themes']      = $ui_themes;
    $_tpl_data['jq_plugins']     = $jq_plugins;

    $_tpl_data['lboxes']         = $lboxes;
    $_tpl_data['lbox_name']      = $name;
    $_tpl_data['lbox_code']      = $lbox['lbox_code'];
    $_tpl_data['lbox_js']        = $lbox['js'];
    $_tpl_data['lbox_css']       = $lbox['css'];
    $_tpl_data['lbox_section']   = $lbox['section_id'];
    $_tpl_data['lbox_use_def']   = $lbox['lbox_use_default'];
    $_tpl_data['current_tab']    = "lbox";

    if($lbox['lbox_template'] == '')
        $_tpl_data['lbox_template'] = fgFetchOldTemplate($_tpl_data['lbox_name']);
    else
        $_tpl_data['lbox_template'] = $lbox['lbox_template'];
    $_tpl_data['content']        = $parser->get('modify_lbox',$_tpl_data);
}

function fgFetchOldTemplate($name)
{
    $htt = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_jquery/plugins/'.$name.'/foldergallery_template.htt');
    //<!-- BEGIN images -->(.*)<!-- END images -->
    $regexp = '<\!--'
            .     '\s*?'
            .         'begin\s*?images'
            .     '\s*?'
            . '-->'
            . '('
            .     '.*'
            . ')'
            ;
    $regexp2 = '<\!--'
            .     '\s*?'
            .         'end\s*?images'
            .     '\s*?'
            . '-->';
    $ul = '';

    if(file_exists($htt))
    {
        $fh = fopen($htt,'r');
        $contents = fread($fh, filesize($htt));
        fclose($fh);
        preg_match('~'.$regexp.'~ismx',$contents,$m,PREG_OFFSET_CAPTURE);
        if(is_array($m))
        {
            $offset = $m[1][1] - 100;
            if(preg_match('~(<ul[^>].*)~i',$contents,$m3,PREG_OFFSET_CAPTURE,$offset))
                $ul = $m3[0][0];

            preg_match('~'.$regexp2.'~ismx',$contents,$m2,PREG_OFFSET_CAPTURE,$m[1][1]);
            if(is_array($m2))
            {
                $substr = substr($contents,$m[1][1],$m2[0][1]-$m[0][1]);
                preg_match('~<\!--\s*?begin\s*?thumbnails\s*?-->(.*)<\!--\s*?end\s*?thumbnails~ismx',$substr,$m3);
                if(is_array($m3))
                {
                    $result = $m3[1];
                    //<a href="{ORIGINAL}" title="{CAPTION}" rel="fancybox"><img src="{THUMB}" alt="{CAPTION}"/></a>
                    $return = $ul
                            . '{foreach $images img}'
                            . str_ireplace(
                                  array(
                                      '{original}',
                                      '{caption}',
                                      '{thumb}',
                                      '{ratingform}',
                                  ),
                                  array(
                                      '{$BASE_URL}{$current_path}/{$img.file_name}',
                                      '{$img.caption}',
                                      '{$BASE_URL}{$current_path}/{$settings.thumb_foldername}/thumb_{$img.file_name}',
                                      '',
                                  ),
                                  $result
                              )
                            . '{/foreach}';
                    if($ul) $return .= "\n".'</ul>';
                    $return = preg_replace('~(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+~',"\n",$return);
                    return $return;
                }
            }
        }
    }
}