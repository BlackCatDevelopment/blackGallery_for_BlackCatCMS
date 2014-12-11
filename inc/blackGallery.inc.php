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

if(!class_exists('blackGallery',false))
{
    class blackGallery extends CAT_Object
    {

        public static $bg_settings = array();
        public static $bg_db       = NULL;

        /**
         *
         * @access public
         * @return
         **/
        public static function getDB()
        {
            if(!is_object(self::$bg_db)) {
                self::$bg_db = \wblib\wbQuery::getInstance(array(
                    'host'   => CAT_DB_HOST,
                    'port'   => CAT_DB_PORT,
                    'prefix' => CAT_TABLE_PREFIX,
                    'user'   => CAT_DB_USERNAME,
                    'pass'   => CAT_DB_PASSWORD,
                    'dbname' => CAT_DB_NAME,
                ));
            }
            return self::$bg_db;
        }   // end function getInstance()
        

        /**
         * check the settings
         *
         * @access public
         * @param  string  $media_folder
         * @return array
         **/
        public static function bgCheckSettings($media_folder)
        {
            $selected = blackGallery::$bg_settings;
            $lboxes   = blackGallery::bgGetLightboxes();
            if(isset(blackGallery::$bg_settings['exclude_dirs']) && blackGallery::$bg_settings['exclude_dirs']!=='')
            {
                $dirs = explode(',',blackGallery::$bg_settings['exclude_dirs']);
                $selected['exclude_dirs'] = array();
                foreach( $dirs as $dir )
                {
                    $selected['exclude_dirs'][$dir] = 1;
                }
            }
            foreach(array('thumb_bgcolor','thumb_overlay') as $key)
            {
                if(!isset(blackGallery::$bg_settings[$key]))
                    blackGallery::$bg_settings[$key] = '';
            }
            blackGallery::$bg_settings['arr_root_dir']     = array_merge( array($media_folder), CAT_Helper_Directory::getDirectories( CAT_PATH.$media_folder, CAT_PATH ));
            blackGallery::$bg_settings['arr_exclude_dirs'] = CAT_Helper_Directory::getInstance()
                                                             ->setSkipDirs(array(blackGallery::$bg_settings['thumb_foldername']))
                                                             ->getDirectories( CAT_PATH.$selected['root_dir'], CAT_PATH );
            blackGallery::$bg_settings['cat_pic']          = array('random','first','last');
            blackGallery::$bg_settings['allowed_suffixes'] = CAT_Helper_Mime::getAllowedFileSuffixes('image/*');
            if(!isset(blackGallery::$bg_settings['suffixes']))
                blackGallery::$bg_settings['suffixes']     = blackGallery::$bg_settings['allowed_suffixes'];
            if(!is_array(blackGallery::$bg_settings['suffixes']))
                blackGallery::$bg_settings['suffixes']     = explode(',',blackGallery::$bg_settings['suffixes']);
            blackGallery::$bg_settings['arr_lboxes']       = array_keys($lboxes);
            foreach( blackGallery::$bg_settings['arr_exclude_dirs'] as $i => $item )
            {
                blackGallery::$bg_settings['arr_exclude_dirs'][$i] = utf8_encode($item);
            }
            foreach( blackGallery::$bg_settings['arr_root_dir'] as $i => $item )
            {
                blackGallery::$bg_settings['arr_root_dir'][$i] = utf8_encode($item);
            }
            return $selected;
        }   // end function bgCheckSettings()

        /**
         * count images in cat
         *
         * @access public
         * @param  integer  $cat_id
         * @return string
         **/
        public static function bgCountImages($cat_id)
        {
            $data = self::getDB()->search(
                array(
                    'tables' => 'mod_blackgallery_images',
                    'where'  => 'cat_id == ?',
                    'params' => array($cat_id),
                    'fields' => 'COUNT(`pic_id`) AS cnt'
                )
            );
            if(is_array($data) && count($data) && isset($data[0])) {
                return $data[0]['cnt'];
            }
            return '-';
        }   // end function bgCountImages()

        /**
         * get details for an image
         *
         * @access public
         * @param  integer  $pic_id
         * @return array
         **/
        public static function bgGetImage($pic_id)
        {
            $data = self::getDB()->search(
                array(
                    'tables'   => array('mod_blackgallery_images','mod_blackgallery_categories'),
                    'join'     => '`t1`.`cat_id`=`t2`.`cat_id`',
                    'jointype' => 'RIGHT OUTER JOIN',
                    'where'    => 'pic_id == ?',
                    'params'   => array($pic_id),
                    'fields'   => array('folder_name','file_name'),
                )
            );
            if(is_array($data) && count($data) && isset($data[0])) {
                return $data[0];
            }
        }   // end function bgGetImage()

        /**
         * get images
         *
         * @access public
         * @param  integer $section_id
         * @param  integer $cat_id
         * @param  boolean $all - active only (default) or all (for backend)
         * @return array
         **/
        public static function bgGetImages($section_id,$cat_id=NULL,$all=false)
        {
            $imgs = array();
            $where  = array('t1.section_id == ?');
            $params = array($section_id);

            if($all) {
                $where[]  = 't1.is_active == ?';
                $params[] = '1';
            }
            if($cat_id) {
                $where[]  = 't1.cat_id == ?';
                $params[] = $cat_id;
            }

            $data = self::getDB()->search(
                array(
                    'tables'   => array('mod_blackgallery_images','mod_blackgallery_categories'),
                    'join'     => 't1.cat_id=t2.cat_id',
                    'jointype' => 'RIGHT OUTER JOIN',
                    'where'    => $where,
                    'params'   => $params,
                    'fields'   => array('folder_name','file_name'),
                    'order_by' => 't1.position'
                )
            );
            if($data && is_array($data) && count($data))
                foreach(array_keys($data) as $i)
                    $imgs[$data[$i]['file_name'].'#'.$data[$i]['cat_id']] = $data[$i];
            return $imgs;
        }   // end function bgGetImages()

        /**
         *
         * @access public
         * @return
         **/
        public static function bgGetImagesFromDisc($base_path)
        {
            $allowed   = isset(self::$bg_settings['allowed_suffixes']) && self::$bg_settings['allowed_suffixes'] != ''
                       ? explode(',',self::$bg_settings['allowed_suffixes'])
                       : CAT_Helper_Mime::getAllowedFileSuffixes('image/*')
                       ;
            $result    = CAT_Helper_Directory::getInstance()
                         ->setSuffixFilter($allowed)
                         ->setSkipDirs(array(self::$bg_settings['thumb_foldername']))
                         ->setRecursion(false)
                         ->getFiles($base_path,$base_path);
            return $result;
        }   // end function getImagesFromDisc()
        

        /**
         *
         * @access public
         * @return
         **/
        public static function bgGetAllImageData($section_id,$cat_id)
        {
            $imgs = array();
            $data = self::getDB()->search(
                array(
                    'tables'   => array('mod_blackgallery_images','mod_blackgallery_categories'),
                    'join'     => 't1.cat_id=t2.cat_id',
                    'jointype' => 'RIGHT OUTER JOIN',
                    'where'    => 't1.section_id == ? && t1.cat_id == ?',
                    'params'   => array($section_id,$cat_id),
                    'fields'   => array('pic_id','folder_name','file_name','file_size','t1.position','caption','t1.description','t1.is_active','t2.cat_name'),
                    'order_by' => 't1.position'
                )
            );

            if($data && is_array($data) && count($data)) {
                foreach($data as $i => $row) {
                    $row['_has_thumb']
                        = ( file_exists(CAT_Helper_Directory::sanitizePath(
                              CAT_PATH.'/'.self::$bg_settings['root_dir'].'/'.$row['folder_name'].'/'
                              .self::$bg_settings['thumb_foldername'].'/thumb_'.$row['file_name']))
                          )
                        ? true
                        : false;
                    $imgs[$row['file_name']] = $row;
                }
            }
            return $imgs;
        }   // end function bgGetAllImageData()


        /**
         * get category list
         *
         * @access public
         * @param  $section_id
         * @param  boolean $all     - get active only (false;default) or all (true)
         * @param  boolean $fe_only - get only categories where FE upload is allowed
         * @param  integer $parent
         * @return
         **/
        public static function bgGetCategories($section_id,$all=false,$fe_only=false,$parent=0)
        {
            $cats   = array();
            $where  = array('t1.section_id == ?');
            $params = array($section_id);

            if(!$all) {
                $where[]  = 't1.is_active == ?';
                $params[] = '1';
            }
            if($fe_only) {
                $where[]  = 'allow_fe_upload == ?';
                $params[] = '1';
            }
            if($parent) {
                $where[]  = 'parent == ?';
                $params[] = $parent;
            }
            if(!$all && self::$bg_settings['show_empty']==0) {
                $where[]  = 't1.is_empty == ?';
                $params[] = '0';
            }

            $data = self::getDB()->search(
                array(
                    'tables'   => array('mod_blackgallery_categories','mod_blackgallery_images'),
                    'join'     => 't1.cat_id=t2.cat_id',
                    'jointype' => 'LEFT OUTER JOIN',
                    'where'    => $where,
                    'params'   => $params,
                    'fields'   => '`t1`.*, count(`t2`.`pic_id`) AS cnt',
                    'group_by' => '`t1`.`cat_id`',
                    'order_by' => 't1.position,t1.level'
                )
            );

            if($data && is_array($data) && count($data)) {
                foreach($data as $i => $row) {
                    $row['cat_name'] = utf8_encode($row['cat_name']);
                    $cats[$row['folder_name']] = $row;
                }
            }
            return $cats;
        }   // end function bgGetCategories()

        /**
         * get category details
         *
         * @access public
         * @param  string  $path  - folder name
         * @param  boolean $is_id - $path is the cat_id, not the folder name
         * @return array
         **/
        public static function bgGetCat($path,$is_id=false)
        {
            $data = self::getDB()->search(
                array(
                    'tables' => 'mod_blackgallery_categories',
                    'where'  => ($is_id ? 'cat_id' : 'folder_name' ) . ' == ?',
                    'params' => array($path)
                )
            );
            if(is_array($data) && count($data) && isset($data[0])) {
                return $data[0];
            }
            return NULL;
        }   // end function bgGetCat()

        /**
         * get column $key from categories table
         *
         * @access public
         * @param  integer  $cat_id
         * @param  string   $key
         * @return array
         **/
        public static function bgGetCatDetail($cat_id,$key)
        {
            $data = self::getDB()->search(
                array(
                    'tables' => 'mod_blackgallery_categories',
                    'fields' => array($key),
                    'where'  => 'cat_id == ?',
                    'params' => array($cat_id)
                )
            );
            if(is_array($data) && count($data) && isset($data[0]) && isset($data[0][$key])) {
                return $data[0][$key];
            }
            return NULL;
        }   // end function bgGetCatDetail()

        /**
         * get the name of the given cat
         *
         * @access public
         * @param  integer  $cat_id
         * @return array
         **/
        public static function bgGetCatName($cat_id)
        {
            return self::bgGetCatDetail($cat_id,'cat_name');
        }   // end function bgGetCatName()

        /**
         * get the path of the given cat
         *
         * @access public
         * @param  integer  $cat_id
         * @return
         **/
        public static function bgGetCatPath($cat_id)
        {
            return self::bgGetCatDetail($cat_id,'folder_name');
        }   // end function bgGetCatPath()

        /**
         * get the lightbox settings
         *
         * @access public
         * @param  string  $lbox
         * @return array
         **/
        public static function bgGetLightbox($lbox)
        {
            if(!$lbox) $lbox = 'Slimbox2';
            $lbox_data = array($lbox=>array());
            $data = self::getDB()->search(
                array(
                    'tables' => 'mod_blackgallery_lboxes',
                    'where'  => 'lbox_name == ?',
                    'params' => $lbox
                )
            );
            if(is_array($data) && count($data) && isset($data[0])) {
                $row              = $data[0];
                $lbox_data[$lbox] = $row;
                if(isset($row['lbox_js']) && $row['lbox_js']!='')
                    $lbox_data[$lbox]['js'] = unserialize($row['lbox_js']);
                else
                    $lbox_data[$lbox]['js'] = array();
                if(isset($row['lbox_css']) && $row['lbox_css']!='')
                    $lbox_data[$lbox]['css'] = unserialize($row['lbox_css']);
                else
                    $lbox_data[$lbox]['css'] = array();
            }
            return $lbox_data[$lbox];
        }   // end function bgGetLightbox()

        /**
         * get a list of installed (known) lightboxes
         *
         * @access public
         * @return array
         **/
        public static function bgGetLightboxes()
        {
            $b = array();
            $data = self::getDB()->search(
                array(
                    'tables' => 'mod_blackgallery_lboxes',
                )
            );
            if(is_array($data) && count($data))
                foreach($data as $row)
                    $b[$row['lbox_name']] = 1;
            return $b;
        }   // end function bgGetLightboxes()

        /**
         * store the lightbox output template
         *
         * @access public
         * @return void
         **/
        public static function bgSetLightboxTpl()
        {
            $lbox = blackGallery::bgGetLightbox(blackGallery::$bg_settings['lightbox']);
            if($lbox['lbox_use_default'] == 'N' && $lbox['lbox_template'] != '')
            {
                $fh = fopen(CAT_PATH.'/modules/blackGallery/templates/default/lightbox.tpl','w');
                fwrite($fh,$lbox['lbox_template']);
                fclose($fh);
            }
            elseif($lbox['lbox_use_default'] == 'Y')
            {
                copy(CAT_PATH.'/modules/blackGallery/templates/default/lightbox_default.tpl', CAT_PATH.'/modules/blackGallery/templates/default/lightbox.tpl');
            }
        }   // end function bgSetLightboxTpl()
        

        /**
         * sync all images (all cats)
         *
         * @access public
         * @param  string  $root_dir
         * @param  array   $allowed
         * @return array
         **/
        public static function bgSyncAllImages($root_dir,$allowed)
        {
            global $section_id;
            $files = CAT_Helper_Directory::getInstance($root_dir,$allowed)
                         ->setSuffixFilter($allowed)
                         ->getFiles(
                             CAT_Helper_Directory::sanitizePath($root_dir),
                             $root_dir
                           );
            if(count($files))
            {
                $images = blackGallery::bgGetImages($section_id,NULL,true);
                return self::bgUpdateImages($images,$files,$root_dir);
            }
        }   // end function bgSyncAllImages()

        /**
         * sync categories (=folders)
         *
         * @access public
         * @param  integer  $section_id
         * @return
         **/
        public static function bgSyncCategories($section_id)
        {
            $root_dir   = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.self::$bg_settings['root_dir']);
            $dirs       = CAT_Helper_Directory::getInstance()
                          ->setSkipDirs(array(self::$bg_settings['thumb_foldername']))
                          ->getDirectories(CAT_PATH.self::$bg_settings['root_dir'], CAT_PATH.self::$bg_settings['root_dir']);
            $categories = blackGallery::bgGetCategories($section_id,true);
            $root_cat   = $categories[key($categories)]['cat_id'];
            $errors     = 0;

            // add root dir
            array_unshift($dirs,'/');

            // map categories to dirs
            $dir_map = array();
            foreach($categories as $cat) {
                $path = CAT_Helper_Directory::sanitizePath($cat['folder_name']);
                $dir_map[$path] = $cat['cat_id'];
            }

            // on disk but not in DB
            $to_add = array_diff($dirs,array_keys($dir_map));

            // in DB but not on disk
            $to_del = array_diff(array_keys($dir_map),$dirs);

            foreach($to_add as $item) {
                $parent  = 0;
                $level   = substr_count($item,'/') - 1;
                $path    = pathinfo($item,PATHINFO_DIRNAME);
                $imgs    = self::bgGetImagesFromDisc(CAT_Helper_Directory::sanitizePath(CAT_PATH.self::$bg_settings['root_dir'].$item));
                $subdirs = CAT_Helper_Directory::getInstance()
                           ->setSkipDirs(array(self::$bg_settings['thumb_foldername']))
                           ->setRecursion(false)
                           ->getDirectories(CAT_PATH.self::$bg_settings['root_dir'].$item,CAT_PATH.self::$bg_settings['root_dir'].$item);
                $empty   = ( count($imgs) + count($subdirs) ) > 0
                         ? '0'
                         : '1';
                if(strlen($path)>1 && array_key_exists($path,$categories))
                    $parent = $categories[$path]['cat_id'];
                else
                    $parent = $root_cat;

                self::getDB()->insert(
                    array(
                        'tables' => 'mod_blackgallery_categories',
                        'fields' => array('section_id','folder_name','cat_name','description','parent','level','subdirs','is_active','is_empty','allow_fe_upload'),
                        'values' => array($section_id,$item,pathinfo($item,PATHINFO_BASENAME),'',$parent,$level,count($subdirs),($empty?0:1),$empty,(self::$bg_settings['allow_fe_upload']=='no'?0:1)),
                    )
                );
                $cat_id = self::getDB()->lastInsertId();
            }

            foreach($to_del as $item) {
                self::getDB()->delete(
                    array(
                        'tables' => 'mod_blackgallery_categories',
                        'where'  => 'folder_name == ?',
                        'params' => array($item)
                    )
                );
            }

            return $errors;
        }   // end function bgSyncCategories()

        /**
         * reads available image files from the file system; uses allowed
         * suffixes as filter
         *
         * @access public
         * @param  integer $cat_id    - category to check
         * @param  string  $root_dir  - root folder
         * @param  array   $allowd    - list of allowed suffixes
         * @param  boolean $checkonly - (optional) true means do not update DB
         * @return
         **/
        public static function bgSyncImagesForCat($cat_id,$root_dir,$allowed,$checkonly=false)
        {
            global $section_id;

            $cat_path = self::bgGetCatPath($cat_id);
            $files    = CAT_Helper_Directory::getInstance()
                            ->setSuffixFilter($allowed)
                            ->setRecursion(false)
                            ->getFiles(
                                CAT_Helper_Directory::sanitizePath(
                                    utf8_decode($root_dir).'/'.utf8_decode($cat_path)
                                ),
                                utf8_decode($root_dir)
                            );

            if(count($files))
            {
                if($checkonly) return(count($files));
                $images     = blackGallery::bgGetImages($section_id,$cat_id,true);
                return self::bgUpdateImages($images,$files,$root_dir);
            }
            else
            {
                if($checkonly) return(count($files));
                else           return false;
            }
        }   // end function bgSyncImagesForCat()

        /**
         * update image info in the DB
         *
         * @access public
         * @param  array   $images   - images in DB
         * @param  array   $files    - files in folders
         * @param  string  $root_dir - root directory
         * @return
         **/
        public static function bgUpdateImages($images,$files,$root_dir)
        {
            global $section_id, $database;

            $categories = blackGallery::bgGetCategories($section_id,true);
            $new        = 0;
            $removed    = 0;
            $errors     = 0;

            // map DB files to paths
            $img_map = array();
            foreach($images as $img)
            {
                $filename = CAT_Helper_Directory::sanitizePath($img['folder_name'].'/'.$img['file_name']);
                $img_map[$filename] = $img['pic_id'];
            }

            // find files that are in the DB but not in the file system
            $to_del = array_diff(array_keys($img_map),$files);

            // find files that are in the file system but not in the DB
            $to_add = array_diff($files,array_keys($img_map));

            // remove additional
            foreach($to_del as $file)
            {
                $id = $img_map[$file];
                $q = sprintf(
                    'DELETE FROM `:prefix:mod_blackgallery_images` WHERE `pic_id`="%d"',
                    $id
                );
                $r = $database->query($q);
                if($database->isError())
                    $errors++;
                else
                    $removed++;
            }

            // add missing
            foreach($to_add as $item)
            {
                $path     = str_replace('\\', '/', pathinfo($item,PATHINFO_DIRNAME));
                $file     = pathinfo($item,PATHINFO_BASENAME);
                $path     = str_ireplace($root_dir,"",$path); // remove root_dir from path
                $fullpath = CAT_Helper_Directory::sanitizePath(CAT_PATH.self::$bg_settings['root_dir'].$item);
                $size     = filesize($fullpath);                  // get the size
                // get category by file path
                if(!array_key_exists(utf8_encode($path),$categories))
                    continue;
                else
                    $cat_id = $categories[utf8_encode($path)]['cat_id'];
                $r = $database->query(sprintf(
                    'INSERT INTO `:prefix:mod_blackgallery_images` VALUES (NULL,"%d","%d","%s","%s",0,"","",1)',
                    $section_id, $cat_id, utf8_encode($file), $size
                ));
                if($database->isError())
                    $errors++;
                else
                    $new++;
            }

            return array('added'=>$new,'removed'=>$removed,'errors'=>$errors);

        }   // end function bgUpdateImages()

        /**
         * update thumbs
         *
         * to recreate (delete and generate new) thumbs, set $recreate to true;
         * default is false
         *
         * @access public
         * @param  integer $cat_id
         * @param  boolean $recreate
         * @return
         **/
        public static function bgUpdateThumbs($cat_id,$recreate=false)
        {
            global $section_id;

            $root_dir  = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$bg_settings['root_dir']);
            $thumb_dir = blackGallery::$bg_settings['thumb_foldername'];
            $allowed   = CAT_Helper_Mime::getAllowedFileSuffixes('image/*');
            $cat_path  = blackGallery::bgGetCatPath($cat_id);
            $base_path = CAT_Helper_Directory::sanitizePath($root_dir.'/'.utf8_decode($cat_path));
            $result    = CAT_Helper_Directory::getInstance()
                         ->setSuffixFilter($allowed)
                         ->setSkipDirs(array(blackGallery::$bg_settings['thumb_foldername']))
                         ->setRecursion(false)
                         ->getFiles($base_path,$root_dir);

            if(count($result))
            {
                $images = blackGallery::bgGetImages($section_id,$cat_id,true);
                if(!is_dir($base_path.'/'.$thumb_dir))
                    CAT_Helper_Directory::createDirectory($base_path.'/'.$thumb_dir,NULL,true);
                foreach($result as $item)
                {
                    $path  = pathinfo($item,PATHINFO_DIRNAME);
                    $file  = pathinfo($item,PATHINFO_BASENAME);
                    $item  = CAT_Helper_Directory::sanitizePath($base_path.'/'.$file);
                    $thumb = $base_path.'/'.$thumb_dir.'/thumb_'.$file;
                    if($recreate || !file_exists($thumb))
                        CAT_Helper_Image::getInstance()->make_thumb(
                            $item,
                            $thumb,
                            blackGallery::$bg_settings['thumb_width'],
                            blackGallery::$bg_settings['thumb_height'],
                            blackGallery::$bg_settings['thumb_method']
                        );
                }
            }
        }   // end function bgUpdateThumbs()

        /**
         *
         * @access public
         * @return
         **/
        public static function bgUpdateSettings($section_id)
        {
            global $database;

            $thumb_settings_changed = false;

            foreach(blackGallery::$bg_settings as $key => $value)
            {
                $new = CAT_Helper_Validate::sanitizePost($key);
                if( is_scalar($value) && is_scalar($new) && $value != $new )
                {
                    $database->query(sprintf(
                        'UPDATE `:prefix:mod_blackgallery_settings` SET `set_value`="%s" WHERE `section_id`="%d" AND `set_name`="%s"',
                        $new, $section_id, $key
                    ));
                    // root dir changed?
                    if($key == 'root_dir')
                    {
// --------------------------TODO-----------------------------------------------
// remove all data
// maybe we should not allow this...?
// --------------------------TODO-----------------------------------------------
                    }
                    // thumb folder name changed?
                    if($key == 'thumb_foldername')
                    {
                        // find all folders with old name
                        $old_thumb_folders = CAT_Helper_Directory::getInstance()
                                             ->showHidden(true)
                                             ->findDirectories(blackGallery::$bg_settings['thumb_foldername'],CAT_PATH.'/'.blackGallery::$bg_settings['root_dir']);
                        // rename all
                        if(count($old_thumb_folders))
                        {
                            foreach($old_thumb_folders as $folder)
                            {
                                $new_folder_name = pathinfo($folder,PATHINFO_DIRNAME).'/'.$new;
                                rename($folder,$new_folder_name);
                            }
                        }
                    }
                    if($key == "thumb_height" || $key == "thumb_width" || $key == "thumb_method")
                        $thumb_settings_changed = true;
                }
                elseif( is_array($value) || is_array($new) )
                {
                    if(is_array($new))   { sort($new);   } else { $new = array($new);     }
                    if(is_array($value)) { sort($value); } else { $value = array($value); }
                    $new   = implode(',',$new);
                    $value = implode(',',$value);
                    if( $value != $new )
                    {
                        $database->query(sprintf(
                            'UPDATE `:prefix:mod_blackgallery_settings` SET `set_value`="%s" WHERE `section_id`="%d" AND `set_name`="%s"',
                            $new, $section_id, $key
                        ));
                    }
                }
            }
            if($thumb_settings_changed)
            {
                // reload settings
                fgGetSettings();
                // get categories
                $categories = self::bgGetCategories($section_id,true);
                // update thumbs per category
                foreach($categories as $cat)
                {
                    $cat_id = $cat['cat_id'];
                    blackGallery::bgUpdateThumbs($cat_id,true);
                }
            }
        }   // end function bgUpdateSettings()

        /**
         *
         * @access public
         * @return
         **/
        public static function lensort($a,$b)
        {
            $la = strlen( $a); $lb = strlen( $b);
            if( $la == $lb) {
                return strcmp( $a, $b);
            }
            return $la - $lb;
        }   // end function lensort()


    }
}