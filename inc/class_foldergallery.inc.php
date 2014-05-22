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

if(!class_exists('blackGallery',false))
{
    class blackGallery extends CAT_Object
    {

        public static $fg_settings = array();

        /**
         *
         * @access public
         * @return
         **/
        public static function fgCountImages($cat_id)
        {
            global $database;
            $r = $database->query(sprintf(
                'SELECT COUNT(`pic_id`) AS cnt FROM `%smod_blackgallery_images` WHERE `cat_id`="%d"',
                CAT_TABLE_PREFIX, $cat_id
            ));
            if( $r && $r->numRows() )
            {
                $row = $r->fetchRow(MYSQL_ASSOC);
                return $row['cnt'];
            }
            return '-';
        }   // end function fgCountImages()

        /**
         * get details for an image
         *
         * @access public
         * @param  integer  $pic_id
         * @return array
         **/
        public static function fgGetImage($pic_id)
        {
            global $database;
            $q = sprintf(
                'SELECT `folder_name`, `file_name` FROM `%smod_blackgallery_images` AS t1
                RIGHT OUTER JOIN `%smod_blackgallery_categories` AS t2
                ON `t1`.`cat_id`=`t2`.`cat_id`
                WHERE `pic_id`="%d"',
                CAT_TABLE_PREFIX,CAT_TABLE_PREFIX,$pic_id
            );
            $r = $database->query($q);
            if($r->numRows())
                return $r->fetchRow(MYSQL_ASSOC);
        }   // end function fgGetImage()

        /**
         * get images
         *
         * @access public
         * @param  integer $section_id
         * @param  integer $cat_id
         * @param  boolean $all - active only (default) or all (for backend)
         * @return array
         **/
        public static function fgGetImages($section_id,$cat_id=NULL,$all=false)
        {
            global $database;
            $imgs = array();
            $sql  = $all
                  ? ''
                  : ' AND `is_active`="1"'
                  ;
            $sql  .= $cat_id
                  ? sprintf(' AND `cat_id`="%d"',$cat_id)
                  : ''
                  ;
            $q    = sprintf(
                'SELECT * FROM `%smod_blackgallery_images` WHERE `section_id`="%d"%s ORDER BY `position`',
                CAT_TABLE_PREFIX, $section_id, $sql
            );
            $r    = $database->query($q);
            if( $r && $r->numRows() )
                while( false !== ( $row = $r->fetchRow(MYSQL_ASSOC) ) )
                    $imgs[$row['file_name'].'#'.$row['cat_id']] = $row;
            return $imgs;
        }   // end function fgGetImages()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetAllImageData($section_id,$cat_id)
        {
            global $database;
            $imgs = array();
            $r    = $database->query(sprintf(
                'SELECT `pic_id`, `folder_name`, `file_name`, `file_size`, `t1`.`position`, `caption`, `t1`.`description`, `t1`.`is_active`, `t2`.`cat_name`
                FROM `%smod_blackgallery_images` AS t1
                RIGHT OUTER JOIN `%smod_blackgallery_categories` AS t2
                ON `t1`.`cat_id`=`t2`.`cat_id`
                WHERE `t1`.`section_id`="%d" AND `t1`.`cat_id`="%d" ORDER BY `t1`.`position`',
                CAT_TABLE_PREFIX, CAT_TABLE_PREFIX, $section_id, $cat_id
            ));
            if( $r && $r->numRows() )
            {
                while( false !== ( $row = $r->fetchRow(MYSQL_ASSOC) ) )
                {
                    $row['_has_thumb']
                        = ( file_exists(CAT_Helper_Directory::sanitizePath(
                              CAT_PATH.'/'.self::$fg_settings['root_dir'].'/'.utf8_decode($row['folder_name']).'/'
                              .self::$fg_settings['thumb_foldername'].'/thumb_'.utf8_decode($row['file_name'])))
                          )
                        ? true
                        : false;
                    $imgs[$row['file_name']] = $row;
                }
            }
            return $imgs;
        }   // end function fgGetAllImageData()


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
        public static function fgGetCategories($section_id,$all=false,$fe_only=false,$parent=0)
        {
            global $database;
            $cats = array();
            $sql  = $all
                  ? ''
                  : ' AND `t1`.`is_active`="1"'
                  ;
            $sql  .= $fe_only
                  ? ' AND `allow_fe_upload`="1"'
                  : ''
                  ;
            $sql  .= $parent
                  ? sprintf(' AND `parent`="%d"', $parent)
                  : ''
                  ;
            $sql .= ( !$all && self::$fg_settings['show_empty']==0)
                  ? sprintf(' AND `is_empty`="0"')
                  : ''
                  ;
            $query = sprintf(
                'SELECT `t1`.*, count(`t2`.`pic_id`) AS cnt
                FROM `%smod_blackgallery_categories` AS t1
                LEFT OUTER JOIN `%smod_blackgallery_images` AS t2
                ON t1.cat_id=t2.cat_id
                WHERE `t1`.`section_id`="%d"%s
                GROUP BY `t1`.`cat_id`
                ORDER BY `position`,`level`',
                CAT_TABLE_PREFIX, CAT_TABLE_PREFIX, $section_id, $sql
            );
            $r    = $database->query($query);
            if( $r && $r->numRows() )
                while( false !== ( $row = $r->fetchRow(MYSQL_ASSOC) ) )
                    $cats[$row['folder_name']] = $row;
            return $cats;
        }   // end function fgGetCategories()

        /**
         * get category details
         *
         * @access public
         * @param  string  $path   - folder name
         * @param  boolean $is_id - $path is the cat_id, not the folder name
         * @return
         **/
        public static function fgGetCat($path,$is_id=false)
        {
            global $database;
            $q = sprintf(
                'SELECT * FROM `%smod_blackgallery_categories` WHERE `%s`="%s"',
                CAT_TABLE_PREFIX, ($is_id ? 'cat_id' : 'folder_name' ), $path
            );
            $r = $database->query($q);
            if( $r && $r->numRows() )
            {
                $row = $r->fetchRow(MYSQL_ASSOC);
                return $row;
            }
            return NULL;
        }   // end function fgGetCat()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetCatDetail($cat_id,$key)
        {
            global $database;
            $r    = $database->query(sprintf(
                'SELECT `%s` FROM `%smod_blackgallery_categories` WHERE `cat_id`="%d"',
                $key, CAT_TABLE_PREFIX, $cat_id
            ));
            if( $r && $r->numRows() )
            {
                $row = $r->fetchRow(MYSQL_ASSOC);
                return $row[$key];
            }
            return NULL;
        }   // end function fgGetCatDetail()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetCatName($cat_id)
        {
            return self::fgGetCatDetail($cat_id,'cat_name');
        }   // end function fgGetCatName()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetCatPath($cat_id)
        {
            return self::fgGetCatDetail($cat_id,'folder_name');
        }   // end function fgGetCatPath()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetLightbox()
        {
            global $database;
            $lbox = self::$fg_settings['lightbox'];
            if(!$lbox) $lbox = 'Slimbox2';
            $data = array($lbox=>array());
            $q = sprintf(
                'SELECT * FROM `%smod_blackgallery_lboxes` WHERE `lbox_name`="%s"',
                CAT_TABLE_PREFIX, $lbox
            );
            $r = $database->query($q);
            if($r->numRows())
            {
                $row = $r->fetchRow(MYSQL_ASSOC);
                $data[$lbox] = $row;
                if(isset($row['lbox_js']) && $row['lbox_js']!='')
                    $data[$lbox]['js'] = unserialize($row['lbox_js']);
                else
                    $data[$lbox]['js'] = array();
                if(isset($row['lbox_css']) && $row['lbox_css']!='')
                    $data[$lbox]['css'] = unserialize($row['lbox_css']);
                else
                    $data[$lbox]['css'] = array();
            }
            return $data[$lbox];
        }   // end function fgGetLightbox()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgGetLightboxes()
        {
            global $database;
            $b = array();
            $q = sprintf(
                'SELECT * FROM `%smod_blackgallery_lboxes`',
                CAT_TABLE_PREFIX
            );
            $r = $database->query($q);
            if($r->numRows())
                while( false !== ( $row = $r->fetchRow(MYSQL_ASSOC) ) )
                    $b[$row['lbox_name']] = 1;
            return $b;
        }   // end function fgGetLightboxes()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgSyncAllImages($root_dir,$allowed)
        {
            global $section_id;

            $result = CAT_Helper_Directory::getInstance($root_dir,$allowed)
                      ->setSuffixFilter($allowed)
                      ->getFiles(CAT_Helper_Directory::sanitizePath(
                          $root_dir,
                          $root_dir
                      ));

            if(count($result))
            {
                $images = blackGallery::fgGetImages($section_id,NULL,true);
                return self::fgUpdateImages($images,$result,$root_dir);
            }
        }   // end function fgSyncAllImages()

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
        public static function fgSyncImagesForCat($cat_id,$root_dir,$allowed,$checkonly=false)
        {
            global $section_id;

            $cat_path = self::fgGetCatPath($cat_id);
            $result   = CAT_Helper_Directory::getInstance()
                        ->setSuffixFilter($allowed)
                        ->setRecursion(false)
                        ->getFiles(CAT_Helper_Directory::sanitizePath(
                            utf8_decode($root_dir).'/'.utf8_decode($cat_path)),
                            utf8_decode($root_dir)
                        );

            if(count($result))
            {
                if($checkonly) return(count($result));
                $images     = blackGallery::fgGetImages($section_id,$cat_id,true);
                return self::fgUpdateImages($images,$result,$root_dir);
            }
            else
            {
                if($checkonly) return(count($result));
                else           return false;
            }
        }   // end function fgSyncImagesForCat()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgUpdateImages($images,$result,$root_dir)
        {
            global $section_id, $database;

            $categories = blackGallery::fgGetCategories($section_id,true);
            $new        = 0;
            $removed    = 0;
            $path_seen  = array();
            $cats_seen  = array();
            $pic_seen   = array();

            // add
            foreach($result as $item)
            {
                
                $path = str_replace('\\', '/', pathinfo($item,PATHINFO_DIRNAME));
                $file = pathinfo($item,PATHINFO_BASENAME);
                $pic_seen[$file] = 1;

                // remove root_dir from path
                $path = str_ireplace($root_dir,"",$path);

                // get category by file path
                if(!array_key_exists(utf8_encode($path),$categories))
                    continue;
                else
                    $cat_id = $categories[utf8_encode($path)]['cat_id'];

                if(file_exists(CAT_Helper_Directory::sanitizePath($root_dir.'/'.$path.'/'.$file)))
                    $size = filesize(CAT_Helper_Directory::sanitizePath($root_dir.'/'.$path.'/'.$file));
                else
                    $size = filesize(CAT_Helper_Directory::sanitizePath($path.'/'.$file));

                if(array_key_exists(utf8_encode($path),$categories))
                {
                    $cat_id = $categories[utf8_encode($path)]['cat_id'];
                    if(!array_key_exists(utf8_encode($file).'#'.$cat_id,$images))
                    {
                        $r = $database->query(sprintf(
                            'INSERT INTO `%smod_blackgallery_images` VALUES (NULL,"%d","%d","%s","%s",0,"","",1)',
                            CAT_TABLE_PREFIX, $section_id, $cat_id, utf8_encode($file), $size
                        ));
                        $new++;
                        $cats_seen[$cat_id] = 1;
                    }
                }
                else
                {
                    if(array_key_exists(utf8_encode($path),$categories))
                    {
                        // check category (maybe moved)
                        if($images[utf8_encode($file)]['cat_id'] != $cat_id)
                        {
                            $r = $database->query(sprintf(
                                'UPDATE `%smod_blackgallery_images` SET `cat_id`="%d" WHERE `pic_id`="%d"',
                                CAT_TABLE_PREFIX, $cat_id, $images[utf8_encode($file)]['pic_id']
                            ));
                        }
                    }
                }

                if(!isset($path_seen[$path]))
                    self::fgUpdateThumbs($cat_id);

                $path_seen[$path] = 1;

            }

            // remove
            if(count($path_seen))
            {
                foreach(array_keys($path_seen) as $path)
                {
                    if(array_key_exists($path,$categories))
                    {
                        $cat    = $categories[$path];
                        $images = blackGallery::fgGetImages($section_id,$cat['cat_id'],true);
                        foreach($images as $img)
                        {
                            if(!array_key_exists($img['file_name'],$pic_seen))
                            {
                                $q = sprintf(
                                    'DELETE FROM `%smod_blackgallery_images` WHERE `pic_id`="%d"',
                                    CAT_TABLE_PREFIX, $img['pic_id']
                                );
                                $r = $database->query($q);
                                $removed++;
                            }
                        }
                    }
                }
            }


            // set categories to 'not empty'
            if(count($cats_seen))
            {
                foreach(array_keys($cats_seen) as $cat_id)
                {
                    $q = sprintf(
                        'UPDATE `%smod_blackgallery_categories` SET `is_empty`="%d" WHERE `cat_id`="%d"',
                        CAT_TABLE_PREFIX, 0, $cat_id
                    );
                    $database->query($q);
                }
            }

            return array( 'added' => $new, 'removed' => $removed );
        }   // end function fgUpdateImages()

        /**
         *
         * @access public
         * @return
         **/
        public static function fgUpdateThumbs($cat_id)
        {
            global $section_id;

            $root_dir  = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.blackGallery::$fg_settings['root_dir']);
            $thumb_dir = blackGallery::$fg_settings['thumb_foldername'];
            $allowed   = CAT_Helper_Mime::getAllowedFileSuffixes('image/*');
            $cat_path  = blackGallery::fgGetCatPath($cat_id);
            $base_path = CAT_Helper_Directory::sanitizePath($root_dir.'/'.utf8_decode($cat_path));
            $result    = CAT_Helper_Directory::getInstance()
                         ->setSuffixFilter($allowed)
                         ->setSkipDirs(array(blackGallery::$fg_settings['thumb_foldername']))
                         ->setRecursion(false)
                         ->getFiles($base_path,$root_dir);

            if(count($result))
            {
                $images = blackGallery::fgGetImages($section_id,$cat_id,true);
                if(!is_dir($base_path.'/'.$thumb_dir))
                    CAT_Helper_Directory::createDirectory($base_path.'/'.$thumb_dir,NULL,true);
                foreach($result as $item)
                {
                    $path  = pathinfo($item,PATHINFO_DIRNAME);
                    $file  = pathinfo($item,PATHINFO_BASENAME);
                    $item  = CAT_Helper_Directory::sanitizePath($base_path.'/'.$file);
                    $thumb = $base_path.'/'.$thumb_dir.'/thumb_'.$file;
                    if(!file_exists($thumb))
                        CAT_Helper_Image::getInstance()->make_thumb(
                            $item,
                            $thumb,
                            blackGallery::$fg_settings['thumb_width'],
                            blackGallery::$fg_settings['thumb_height'],
                            blackGallery::$fg_settings['thumb_method']
                        );
                }
            }
        }   // end function fgUpdateThumbs()


        /**
         *
         * @access public
         * @return
         **/
        public static function fgSyncCategories($section_id)
        {
            global $database;

            $root_dir = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.self::$fg_settings['root_dir']);
            $dirs     = CAT_Helper_Directory::getInstance()
                        ->setSkipDirs(array(self::$fg_settings['thumb_foldername']))
                        ->getDirectories( CAT_PATH.self::$fg_settings['root_dir'], CAT_PATH.self::$fg_settings['root_dir'] );

            // add root dir
            array_unshift($dirs,'/');

            $skip     = explode(',',self::$fg_settings['exclude_dirs']);
            $errors   = 0;

            if(count($skip))
                for($i=count($dirs)-1;$i>=0;$i--)
                    if(in_array($dirs[$i],$skip))
                        unset($dirs[$i]);

            // sort dirs by length; this makes sure we get the parents first
            usort($dirs,array('blackGallery','lensort'));

            if(count($dirs))
            {
                $categories     = blackGallery::fgGetCategories($section_id,true);
                $root_dir_depth = count(explode('/', CAT_Helper_Directory::sanitizePath(self::$fg_settings['root_dir'])))-1;
                $allowed        = CAT_Helper_Mime::getAllowedFileSuffixes('image/*');
                $is_new         = false;
                $root_cat       = $categories[key($categories)]['cat_id'];

                foreach($dirs as $dir)
                {
                    $fulldir = CAT_Helper_Directory::sanitizePath(CAT_PATH.self::$fg_settings['root_dir'].$dir);
                    $dir     = str_ireplace(CAT_Helper_Directory::sanitizePath(CAT_PATH.self::$fg_settings['root_dir']),'',$fulldir);
                    $cat_id  = NULL;
                    if($dir == '') // root directory
                        continue;
                    if(!array_key_exists(utf8_encode($dir),$categories))
                    {
                        // parent
                        $path   = pathinfo($dir,PATHINFO_DIRNAME);
                        $parent = 0;
                        if(strlen($path)>1 && array_key_exists(utf8_encode($path),$categories))
                            $parent = $categories[utf8_encode($path)]['cat_id'];
                        else
                            $parent = $root_cat;
                        // id, section_id, folder_name, cat_name, cat_pic, description, parent, position, level, subdirs, is_active, is_empty, allow_fe_upload
                        $q = sprintf(
                            'INSERT INTO `%smod_blackgallery_categories` VALUES ( NULL, "%d", "%s", "%s", "", "", "%d", 0, "%d", "%d", "0", "0", "0" )',
                            CAT_TABLE_PREFIX, $section_id, utf8_encode($dir), utf8_encode(pathinfo($dir,PATHINFO_BASENAME)), $parent, 0, 0
                        );
                        $database->query($q);
                        if($database->is_error())
                            $errors++;
                        else
                            $cat_id = $database->insert_id(); $is_new = true;
                        // reload
                        $categories = blackGallery::fgGetCategories($section_id,true);
                    }
                    else
                    {
                        $cat_id = $categories[utf8_encode($dir)]['cat_id'];
                    }

                    if($cat_id)
                    {
                        // always update subdir count, image count and level
                        // get level
                        $level    = explode('/', str_ireplace(self::$fg_settings['root_dir'],'',CAT_Helper_Directory::sanitizePath($dir)));
                        // number of subdirs
                        $subs     = CAT_Helper_Directory::getDirectories($fulldir);
                        // images
                        $imgcount = self::fgSyncImagesForCat($cat_id,$root_dir,$allowed,true);
                        $q = sprintf(
                            'UPDATE `%smod_blackgallery_categories` SET `level`="%d", `subdirs`="%d", `is_empty`="%d" WHERE `cat_id`="%d"',
                            CAT_TABLE_PREFIX, (count($level)-$root_dir_depth), count($subs), ( (count($subs)+$imgcount) > 0 ? 0 : 1 ), $cat_id
                        );
                        $database->query($q);
                        if($database->is_error())
                        {
                            $errors++;
                        }
                        else
                        {
                            if($is_new)
                            {
                                $q = sprintf(
                                    'UPDATE `%smod_blackgallery_categories` SET `is_active`="%d" WHERE `cat_id`="%d"',
                                    CAT_TABLE_PREFIX, ( (count($subs)+$imgcount) > 0 ? 1 : 0 ), $cat_id
                                );
                                $database->query($q);
                                if($database->is_error())
                                    $errors++;
                            }
                        }
                    }
                }
                // removed folders
                foreach(array_keys($categories) as $dir)
                {
                    $fulldir = CAT_Helper_Directory::sanitizePath(CAT_PATH.self::$fg_settings['root_dir'].$dir);
                    if(!CAT_Helper_Directory::isDir($fulldir))
                    {
                        $q = sprintf(
                            'DELETE FROM `%smod_blackgallery_categories` WHERE `section_id`="%d" AND `folder_name`="%s"',
                            CAT_TABLE_PREFIX, $section_id, $dir
                        );
                        //$database->query($q);
                        if($database->is_error())
                            $errors++;
                    }
                }
            }

            return $errors;
        }   // end function fgSyncCategories()



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