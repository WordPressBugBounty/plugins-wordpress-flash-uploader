<?php
/**
 *   Wordpress Flash uploader 3.2.x  
 *
 *   This file contains the methods used by the synch part from the WFU class
 *
 *   Copyright (c) 2004-2017 TinyWebGallery
 *   Author: Michael Dempfle
 *   Author URI: http://www.tinywebgallery.com 
 */
 
if (!class_exists("WFUSync")) {
    class WFUSync {

        function printSync($devOptions, $istab = false, $check_nonce = true) {    
            clearstatcache();
            $synch_start_time = time ();
           
            if (!(isset($_POST['synchronize_media_library']) || isset($_POST['clean_media_library'])  || 
                  isset($_GET['clean_media_library']) || isset($_GET['synchronize_media_library']) || 
                  isset($_POST['import_media_library']) || isset($_GET['import_media_library']))) {
              unset($_SESSION['fuo_backup']);
            }
            if (isset($_GET['isFlash'])) {
                unset($_SESSION['fuo_backup']);
            }

            // now we check all possible actions if the correct nonce is set.           
            $wfuOptions = $this->getAdminOptions();  
            for ($i = 0; $i < ob_get_level(); $i++) { @ob_end_flush(); }
            ob_implicit_flush(1);
            
            if ($wfuOptions['sync_time'] != '0' && $wfuOptions['sync_time'] != '') {
              $time = intval($wfuOptions['sync_time']);
              // Both settings should do the same! Only works with safemode off!
              @set_time_limit($time);
              @ini_set('max_execution_time', $time);
            }              
            $max_execution_time = intval(ini_get('max_execution_time'));
            
            if ($check_nonce) {
              if (isset($_POST['synchronize_media_library']) || isset($_GET['synchronize_media_library']) || 
              isset($_POST['clean_media_library']) || isset($_GET['clean_media_library']) ) {
                  $nonce= isset($_GET['wfunonce']) ? $_GET['wfunonce'] : $_POST['wfunonce'];
                  if (! wp_verify_nonce($nonce, 'wfu-nonce') ) die('Security check failed!');
              } 
            }
            echo "<!-- DEBUG: wpnounce set properly -->\n";
            // nounce is set porperly - we continue...   
            echo '<div id="wfu" class="wrap wfupadding">';
            $nonce= wp_create_nonce ('wfu-nonce'); 
            echo '<form method="post" action="'. $_SERVER["REQUEST_URI"] . '">';       
            echo '<input type="hidden" name="wfunonce" value="'.$nonce.'">';
            // this is printed first to get a header while generating thumbnails.
            echo '<div id="icon-upload" class="icon_jfu"><br></div>
                  <h2>Synchronize Media Library</h2>';
                  
            if  ($wfuOptions['sync_warning_message'] == 'true') {     
                echo '<div class="error" style="padding:10px;">If you are using the synch the first time please make a backup of you upload folder and your database first!<br />Please disable this message in the settings once you have done this!</div>';      
            }
            @flush();
            //@wp_ob_end_flush_all();
            echo "\n<!-- DEBUG: before getMediaLibraryFiles -->\n";
            $mlf = WFUSync::getMediaLibraryFiles();
            echo "<!-- DEBUG: before getUploadFolderFiles -->\n";
            $uff = WFUSync::getUploadFolderFiles('../' . WFUSync::getBaseUploadPath(), !$check_nonce);
            $enable_sych = ($uff !== false);
            if (!$enable_sych) {
              $uff = array();
            } 
            echo "<!-- DEBUG: before getMediaLibraryOnly -->\n"; 
            $mfo = WFUSync::getMediaLibraryOnly($mlf);
            echo "<!-- DEBUG: before findUploadOnly -->\n"; 
            $fuo = WFUSync::findUploadOnly($mlf, $uff);

            @flush(); // is done to see the debug stuff
            @wp_ob_end_flush_all();
            
            if (isset($_POST['clean_media_library']) || isset($_GET['clean_media_library'])) {
                // we remove the ones tat are not in the upload folder anymore.
 
                echo '<div class="updated"><p><strong>';
                if (count($mfo) > 0) {
                  foreach($mfo as $item) {
                      if ($item->type == 'main') {
                          wp_delete_post($item->post_id);
                      } else { // metadata stuff! we update the database table!
                          $data = $item->data;
                          wp_update_attachment_metadata($item->post_id, $data);
                      }
                  } 
                  echo _e("Invalid media library entries where removed.", "WFU");  
                } else {
                  echo _e("No invalid media library entries found.", "WFU");    
                }
                echo '</strong></p></div>';                 
            }
            
            $current = 0;
            if (isset($_POST['synchronize_media_library']) || isset($_GET['synchronize_media_library']) || 
                isset($_POST['import_media_library']) || isset($_GET['import_media_library'])) {
                $sum = count ($fuo);  
                $synch_max_files = $wfuOptions['synch_max_files'];
                
                $start_time = time ();
                $synch_before_time =  $start_time - $synch_start_time;
                $num = count($fuo);
                echo "<br />Calculating data for needed media library modifications: ". ceil($synch_before_time)."s<br />&nbsp;<br />"; 
                
                foreach($fuo as $key => $item) {
                    $current++;
                    if(!ini_get('safe_mode') ){
                       @set_time_limit($max_execution_time);
                    }
                    
                    WFUSync::handle_import_file($item, $current, $sum);
                    unset($_SESSION['fuo_backup'][$key]);
                    $executed_time = time() - $start_time;                   
                    $average = $executed_time / $current; 
                    if ($num != $current) {  // not the last one!
                        // we leave 1 calculation +3 sec as buffer
                        $executed_time = $synch_before_time + $executed_time + 3 + $average;
                       
                        if ($synch_max_files == 'auto' && (($max_execution_time - $executed_time) < 0) || ($synch_max_files != 'auto' && ($current >= intval($synch_max_files)))  ) {
                          if ($check_nonce) {
                            echo "<br />Maximum number of files or maximum execution time reached.<br />Estimated remaining time for remaining files: ". ceil(($sum-$current) * $average) . "s<br /><br />Reload and continue...<br />"; 
                            
                            $action_url = '';
                            if (isset($_POST['synchronize_media_library']) || isset($_GET['synchronize_media_library'])) {
                              $action_url .= '&synchronize_media_library=true';
                            } 
                            if (isset($_POST['import_media_library']) || isset($_GET['import_media_library'])) {
                               $action_url .= '&import_media_library=true';
                            }
                            $nonce= wp_create_nonce ('wfu-nonce'); 
                                                      
                            $reload_url = 'upload.php?page=wordpress-flash-uploader.php?printSync=true&wfunonce=' . $nonce . $action_url; 
                            // 
                            echo '<script type="text/javascript">                         
                            function wp_reload() {
                              window.location.href="'.$reload_url.'";
                            }
                            window.setTimeout("wp_reload()", 2000);
                            </script>';
                            @flush();
                            //@wp_ob_end_flush_all();
                            return;
                          } else {
                              // cronjob does only the maximum of the allows files...
                              return;
                          }
                        }
                    }
                    
                }
                echo '<div class="updated"><p><strong>';
                if ($current > 0) {
                  echo _e("Files imported to media library.", "WFU");
                } else {
                  echo _e("No files found which are not already in the media library.", "WFU");
                }
                echo '</strong></p></div>';
            }

            echo '<script type="text/javascript">
      if (window.parent.frames[window.name] && (parent.document.getElementsByTagName(\'frameset\').length <= 0)) {
        window.parent.document.getElementById("status_text").innerHTML = "Synchronisation finished.";
        if (window.parent.refreshFileList) {
          window.parent.refreshFileList();
        }
      }</script>';

      if (isset($_GET['isFlash'])) {
         return;
      }

            if (isset($_POST['synchronize_media_library']) || isset($_POST['clean_media_library']) || isset($_POST['import_media_library']) || 
                isset($_GET['synchronize_media_library']) || isset($_GET['clean_media_library']) || isset($_GET['import_media_library']) ) {
                // we reload the data.
                $mlf = WFUSync::getMediaLibraryFiles();
                $uff = WFUSync::getUploadFolderFiles('../' . WFUSync::getBaseUploadPath(), !$check_nonce);
                if (!$enable_sych) {
                  $uff = array();
                } 
                $mfo = WFUSync::getMediaLibraryOnly($mlf);
                $fuo = WFUSync::findUploadOnly($mlf, $uff);
            }

            $count_mfo = 0;
            foreach($mfo as $item) {
                if ( $item->type == 'main') $count_mfo++;
            }

            $nr_ok = count($mlf) - $count_mfo;
if ($enable_sych) {
            echo '
<p>
If you upload files by WFU or FTP or by any other tool than the internal uploader of Wordpress the files do not get listed in the media library.
<div class="wfu_reg">
';
            if (!$istab) {
                echo '
<p><b>Import files to Media Library:</b> All files below the "'.WFUSync::getBaseUploadPath().'" folder are checked if they do already exist in the media library. If they don\'t exist they are entered and can be managed in the media library. Image exif/iptc data are used as defaults for title and caption if possible.</p>
';
if ($wfuOptions['remove_invalid'] == "true") {
  echo '<p><b>Remove invalid Media Library entries:</b> The database is checked if all files still exist. Data of deleted files (link, title, caption ...) are removed from the media library.</p>';
}
// <p><b>Synchronize Media Library:</b> Import and Remove.</p>

            }
            echo '
<div class="submit">';
            if (false) {
                echo '
<input type="submit" class="button action" name="synchronize_media_library" value="';
                echo _e('Synchronize Media Library', 'WFU');
                echo '" />';
            }
            echo '
<input type="submit" class="button action button-primary" name="import_media_library" value="';
            echo _e('Import files to Media Library', 'WFU');
            echo '" />';
            if ($wfuOptions['remove_invalid'] == "true") {
                echo '
<input type="submit" class="button action" name="clean_media_library" value="';
                echo _e('Remove invalid Media Library entries', 'WFU');
                echo '" />';
            }

            echo '</div>';

            echo '<h3>Current status</h3>';
            echo '<table><tr valign="top"><td>Files in upload folder and media library<br>which are in sync.<br>&nbsp;<br></td><td style="padding-left:20px;">'.$nr_ok.'<br>&nbsp;</td><td> </td></tr>
<tr valign="top"><td>Files only in upload folder</td><td style="padding-left:20px;">'.count($fuo) . '</td><td style="padding-left:20px;">';
            foreach($fuo as $item) {
                echo htmlentities(WFUSync::stripAboveUpload($item)) . '<br>';
            }
            echo '&nbsp;';
            echo '</td></tr>
<tr valign="top"><td>Files only in media library</td><td style="padding-left:20px;">'.count($mfo) . '</td><td style="padding-left:20px;">';
            foreach($mfo as $item) {
                echo htmlentities(WFUSync::stripAboveUpload($item->meta_value)) . (($item->type == 'main') ? '': ' <span style="color:#666;"><small>('.$item->type.')</small></span>') .'<br>';
            }
            echo '&nbsp;';
            echo '</td></tr>
</table></form>';
            echo '<br>';
            echo '<div class="howto"><small>* Please note: The numbers are always the number of original images.<br>Thumbnails, medium and large images are not counted here but they are synchronized as well.<br>Not existing thumbnails, medium and large images are removed from the meta data of the image.</small></div>';
            echo '</div>';

            if (!$istab && $devOptions['hide_donate'] == 'false') {
                echo '
    <br>&nbsp;
    <table><tr><td>You like this plugin? Support the development with a small donation. </td><td>&nbsp;&nbsp;&nbsp;<A target="_blank" HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40mdempfle%2ede&item_name=WP%20Flash%20Uploader&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=EN&bn=PP%2dDonationsBF&charset=UTF%2d8"><img src="../wp-content/plugins/wordpress-flash-uploader/img/btn_donate_SM.gif"></A></td></tr></table>          
    ';    
            }
            }
        }
        
              
        function stripAboveUpload($str) {
            $str = str_replace("\\","/",$str);
            if (stristr($str, WFUSync::getBaseUploadPath()) === false) {
                return $str;
            } else {
                $pos = stripos($str, WFUSync::getBaseUploadPath() . '/');
                return substr($str, $pos + strlen(WFUSync::getBaseUploadPath())+1);
            }
        }
      
        function stripAfterUpload($str) {
            echo $str . "<br>";
            $str = str_replace("\\","/",$str); 
            
            $pos = stripos($str, WFUSync::getBaseUploadPath() . '/');
            $str =  substr($str, $pos + strlen(WFUSync::getBaseUploadPath())+1);
             
           /* $str =  stristr($str, WFUSync::getBaseUploadPath());
            echo $str . "<br>";
            $str = substr(stristr($str, '/'),1); 
            echo $str . "<br>";
            */
            echo $str . "<br>";
            return $str;
        }
      
        function getMediaLibraryFiles() {
            $start_time = time();
            global $wpdb;
            $sql= "SELECT pm.post_id, pm.meta_id, pm.meta_value, pma.meta_value as meta_att FROM $wpdb->posts p,$wpdb->postmeta pm, $wpdb->postmeta pma WHERE pm.post_id=p.id and pma.post_id=pm.post_id and p.post_type = 'attachment' and pm.meta_key='_wp_attached_file' and pm.meta_value <> pma.meta_value order by pm.meta_value ";
            $mlf = $wpdb->get_results( $sql );
            echo '<!-- DEBUG: getMediaLibraryFiles: ' . count($mlf) . " Duration: " . (time() -$start_time) . "s -->\n"; 
            return $mlf;
        }

        function getUploadFolderFiles( $from = '', $is_cron) {
            if ($from == '') {
                $from =  '../'. WFUSync::getBaseUploadPath();  
            }
            $start_time = time();
            if(!is_dir($from)) {
               echo '<div class="updated"><p><strong>';
               echo _e("Upload folder does not exist yet. Please upload at least one file.", "WFU");
               echo '</strong></p></div>';
               return false;
            }

            $files = array();
            $dirs = array( $from);
            while( NULL !== ($dir = array_pop( $dirs)))
            {
                if( $dh = opendir($dir))
                {
                    while( false !== ($file = readdir($dh)))
                    {
                        if( $file == '.' || $file == '..')
                        continue;
                        $path = $dir . '/' . $file;
                        if( is_dir($path))
                        $dirs[] = $path;
                        else {
                            if (WFUSync::isSupportedExtension($path, $is_cron)) {
                            $files[] = $path;
                          }
                        }
                    }
                    closedir($dh);
                }
            }
            
             // all filesizes are read - then we wait and then we read again 
             // only the ones who stay the same are "stable" and not files currently 
             // uploaded
             $size_array = array();
             foreach ($files as $file) {
                 $size_array[$file] = filesize($file);
             }
             if ($is_cron) {
               sleep(5);
             } else {
               sleep(1);
             }
             clearstatcache();
             foreach ($files as $key => $file) {
                 if ($size_array[$file] != filesize($file)) {
                   unset($files[$key]); 
                 }
             }  
             echo '<!-- DEBUG: getUploadFolderFiles: ' . count($files) . " Duration: " . (time() -$start_time) . "s -->\n"; 
            return $files;
        }

        function getMediaLibraryOnly($mlf) {
            $start_time = time();
            $mfo = array();
            $upload_path =  '../' . WFUSync::getBaseUploadPath();
            
            foreach($mlf as $item) {
                if (!WFUSync::isSupportedExtension($item->meta_value, false)) {
                    continue;
                }
                $main = false;
                
                if (!file_exists($item->meta_value) && !file_exists( $upload_path . '/' . $item->meta_value)) {
                    $item->type = 'main';
                    $main = true;
                    $mfo[] = $item;
                }

                if (!$main) { // we check the meta data if the main image is o.k.
                    $data = @unserialize($item->meta_att);
                    $base = dirname($data['file']);
                    if (isset($data['sizes'])) {            
                        // $img_types = array('thumbnail', 'medium','large','Slideshow','Homepage','Sidebar'); 
                        // print_r($data['sizes']);
                        $img_types = array_keys($data['sizes']);
                        foreach ($img_types as $img_type) {
                          if (isset($data['sizes'][$img_type]) && isset($data['sizes'][$img_type]['file'])) {
                              $media_file =  $base . '/' . $data['sizes'][$img_type]['file'];
                              if (!file_exists($media_file) && !file_exists( $upload_path.'/' . $media_file)) {
                                  unset($error);
                                  $error->meta_value = $media_file;
                                  $error->type = $img_type;
                                  $error->post_id = $item->post_id;
                                  unset($data['sizes'][$img_type]);
                                  $error->data = $data;
                                  $mfo[] = $error;
                              }
                          }
                        } 
                    }
                }
            }
            echo '<!-- DEBUG: getMediaLibraryOnly: ' . count($mfo) . " Duration: " . (time() -$start_time) . "s -->\n";  
            return $mfo;
        }

        function findUploadOnly($media, $filesystem) {
            $start_time = time();            
            if (isset($_SESSION['fuo_backup'])) {      
              $fuo =  $_SESSION['fuo_backup'];
            } else {
            $unserialize = array();
            $media_cache = array();           
            $fuo = array();
            $wfuOptions = $this->getAdminOptions();
            $uploadPath = WFUSync::getBaseUploadPath(); 
           
            //echo "<br>Files found on the filesystem : " . count($filesystem);
            //echo "<br>Entires found in the media library : " . count($media);
            
            // a media cach is build that does all the expensive calculation!
             foreach($media as $item) {
                  $v1 =  realpath('../'.$uploadPath.'/' . $item->meta_value);
                  $v3 = ($v1) ? $v1:realpath($item->meta_value);
                  $rbase = realpath(dirname($v3)) . DIRECTORY_SEPARATOR;
                  $nv3 =  WFUSync::normalizeFileNames($v3);
                  $data = @unserialize($item->meta_att);
                                
                  $media_cache[$item->meta_value . '-v'] =  $v3;
                  $media_cache[$item->meta_value . '-nv'] =  $nv3; 
                  $media_cache[$item->meta_value . '-rbase'] =  $rbase;  
                  $media_cache[$item->meta_value . '-data'] =  $data;   
             }

            $counter = 0;
            $filesystem_local = $filesystem; 

            foreach($filesystem_local as $fkey => $fitem) {
                
                $realFitem = realpath($fitem);
                $normRealFitem = WFUSync::normalizeFileNames($realFitem);
                $found = false;
                foreach($media as $item) {
                   $v3 = $media_cache[$item->meta_value . '-v'];
                   $nv3 = $media_cache[$item->meta_value . '-nv'] ; 
                   $rbase = $media_cache[$item->meta_value . '-rbase'];   
                     
                    if ($realFitem == $v3 || $nv3 == $normRealFitem) { 
                        $found = true;
                        // remove from checks that is it a resized file.
                        unset($filesystem_local[$fkey]); 
                        break; // we have found this element - we search the next one.
                    }   
                    // now we check the metadata
                    $data =  $media_cache[$item->meta_value . '-data'];                     
                    
                    if (isset($data['sizes'])) {
                        // $img_types = array('thumbnail', 'medium','large','Slideshow','Homepage','Sidebar'); 
                        $img_types = array_keys($data['sizes']);
                        foreach ($img_types as $img_type)
                            if (isset($data['sizes'][$img_type]) && isset($data['sizes'][$img_type]['file'])) {
                              $type_file =  $rbase . $data['sizes'][$img_type]['file'];
                              //echo "<br>" . $realFitem . ' - ' . $type_file;
                              if ($realFitem == $type_file) {
                                $found = true; 
                                // remove from checks that is it a reasized file.
                                unset($filesystem[$fkey]); 
                                break 2; 
                                }
                            }                        
                       }         
                  }
                 
 
                 if (!$found) { 
                    $add = true;
                    if ($wfuOptions['detect_resized'] == "true") {
                      $len_fitem = strlen($fitem);
                      $fitem_base = strtolower(WFUSync::removeExtension($fitem));
                      if (preg_match('/\-[0-9]{1,5}x[0-9]{1,5}$/', $fitem_base) == 1) {
                         $main_file = preg_replace ('/\-[0-9]{1,5}x[0-9]{1,5}$/', "", $fitem_base) . "." . WFUSync::getExtension($fitem);
                         if (file_exists($main_file)) {
                           $add = false;
                         }
                      }                      
                    }                 
                    if ($add) {
                      $fuo[] = $realFitem;
                    }
                }  
                  
                
                if (($counter++ % 100) == 99) {
                  if(!ini_get('safe_mode') ){
                     $max_execution_time = intval(ini_get('max_execution_time'));
                     @set_time_limit($max_execution_time);
                  }
                }
                
                }
                
                $_SESSION['fuo_backup'] = $fuo;
            } 
            echo '<!-- DEBUG: findUploadOnly: ' . count($fuo) . " Duration: " . (time() -$start_time) . "s -->\n"; 
            return $fuo;
        }

        //Handle an individual file import. This function is based on the one from add-from-server
        function handle_import_file($file, $current, $sum, $post_id = 0) {
            $start_time = time ();
            $debug_string = '    Request: ' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . "\n";
            $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;

            $file = str_replace('\\', '/',$file);

            // we have to replace special characters because wordpress does not handle them properly.
            $filenorm = WFUSync::normalizeFileNames($file);
            if ($filenorm !== $file) { 
              if (rename ($file, $filenorm)) {
                $file =  $filenorm;
              } else {
                echo $file . ' can not be renamed to a normalized file name. Please check the file permissions.';
              } 
            }
            // $path = WFUSync::stripAfterUpload($file);
            $path = WFUSync::stripAboveUpload($file);
            
            $time = current_time('mysql');
            $uploads = wp_upload_dir();
            $wp_filetype = wp_check_filetype( $file, null );
            extract( $wp_filetype );
            $filename = basename($file);
            $new_file = $path;
            $url = $uploads['baseurl'] . '/' . $path;

            // get the right time.
            // it it is in an folder with date we use this one
            // if not we use the current time
            // the handling if it can from a post has to be handled in the flash implementation!
            $time = current_time('mysql');
            if ( $post = get_post($post_id) ) {
                if ( substr( $post->post_date, 0, 4 ) > 0 )
                $time = $post->post_date;
            } else {
                $time = filemtime($file);
            }

            $post_date = date( 'Y-m-d H:i:s', $time);
            $post_date_gmt = gmdate( 'Y-m-d H:i:s', $time);
            
            //Apply upload filters
            $return = apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ) );
            $new_file = $return['file'];
            $url = $return['url'];
            $type = $return['type'];
            $title = preg_replace('!\.[^.]+$!', '', basename($file));
            $content = '';
           
            // use image exif/iptc data for title and caption defaults if possible
            // TODO: fix path!!!
            $new_file_path = '../'.WFUSync::getBaseUploadPath()  . "/" . $new_file;
            if (file_exists($new_file_path) && function_exists("wp_read_image_metadata")) {
              if ( $image_meta = wp_read_image_metadata($new_file_path) ) {     // add @ again.
                  if ( '' != trim($image_meta['title']) )
                  $title = trim($image_meta['title']);
                  if ( '' != trim($image_meta['caption']) )
                  $content = trim($image_meta['caption']);
              }
            }
            // Construct the attachment array
            $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_name' => $title,
            'post_content' => $content,
            'post_date' => $post_date,
            'post_date_gmt' => $post_date_gmt
            );
            // Save the data
            $id = wp_insert_attachment($attachment, $new_file, $post_id);
            
            if ( !is_wp_error($id) ) {
                ob_implicit_flush();
                echo 'Crunching ('.$current.'/'.$sum.'): ' . htmlentities($filename);
                echo '<script type="text/javascript">
      if (window.parent.frames[window.name] && (parent.document.getElementsByTagName(\'frameset\').length <= 0)) {
        window.parent.document.getElementById("status_text").innerHTML = "Crunching ('.$current.'/'.$sum.'): ' . htmlentities($filename).'";
      }</script>';
                @flush();
                //@wp_ob_end_flush_all();
                $data = wp_generate_attachment_metadata( $id, $file );
                $data['file'] = $new_file; // fix to get the right file name into the database!
                wp_update_attachment_metadata( $id, $data );
                // hotfix for wordpress 3.4
                update_attached_file( $id, $file );
                $end_time = time ();
                
                echo ', duration: ' . ($end_time-$start_time) .'s';
                // echo some spaces to the the browser to start rendering
                echo str_repeat(" \n", 2100);
                echo '<br />';
                @ob_flush();
                @flush();
                //@wp_ob_end_flush_all();    
            } 
            return $id;
        }

       function normalizeFileNames($imageName){
         global $normalizeSpaces;
      
        // it's needed to decode first because str_replace does not handle str_replace in utf-8
        $imageName = utf8_decode($imageName);
        // we make the file name lowercase ÄÖÜ as well.
        if (function_exists("mb_strtolower")) { 
          $imageName = mb_strtolower($imageName); 
        } else {
          $imageName = strtolower($imageName); 
        }  
        
        if ($normalizeSpaces == 'true') {
          $imageName=str_replace(' ','_',$imageName);
        }
        // Some characters I know how to fix ;).
        $imageName=str_replace(array('ä','ö','ü','ß'),array('ae','oe','ue','ss'),$imageName);
        // and some others might need
        $imageName=str_replace(array('á','à','ã','â','ç','¢','é','ê','è','ë','í','î','ï','ì','ñ','ô','ó','õ','ò','š','ú','ù','û','ü','ý','ÿ','ž'),
                               array('a','a','a','a','c','c','e','e','e','e','i','i','i','i','n','o','o','o','o','s','u','u','u','u','y','y','z'),$imageName);
       
        // we remove the rest of unwanted chars
        $patterns[] = '/[\x7b-\xff]/';  // remove all characters above the letter z.  This will eliminate some non-English language letters
        $patterns[] = '/[\x21-\x2c]/'; // remove range of shifted characters on keyboard - !"#$%&'()*+
        $patterns[] = '/[\x5b-\x60]/'; // remove range including brackets - []\^_`
        // we remove all kind of special characters for utf8 encoding as well
        $patterns[] = '/[\x7b-\xff]/u';  // remove all characters above the letter z.  This will eliminate some non-English language letters
        $patterns[] = '/[\x21-\x27]/u'; // remove range of shifted characters on keyboard - !"#$%&'
        $patterns[] = '/[\x2a-\x2c]/u'; // remove range of shifted characters on keyboard - *+,
        $patterns[] = '/[\x5b-\x60]/u'; // remove range including brackets - []\^_`
        $replacement ="_";
        return utf8_encode(preg_replace($patterns, $replacement, $imageName));
      }

        function getBaseUploadPath() {
          $wp_upload_dir_array = wp_upload_dir();
          $upload_path = $wp_upload_dir_array['baseurl']; 
         // we need the path relative! if we find WP_CONTENT_FOLDERNAME we remove everything before!
          if (stristr($upload_path, WP_CONTENT_FOLDERNAME) !== false) {
              $upload_path = stristr($upload_path, WP_CONTENT_FOLDERNAME);
          }
          return $upload_path;
        }
        
        function removeExtension($name) {
            return substr($name, 0, strrpos ($name, '.'));
        }
        function getExtension($name) {
	          return strtolower(substr (strrchr ($name, '.'), 1));
        }
        /**
         * We check the extension + the if this is a cron a file has to be at least 1 min old!
         */                 
        function isSupportedExtension($filename, $is_cron) {
          $wfuOptions = $this->getAdminOptions();
          if ($is_cron) {
             clearstatcache();
             if (file_exists($filename)) {
               // check if the file was not modified in the last 10 sec
               // and is therefore in an upload
               // this does only work on some systems. Therefore the          
               if ((time() - filemtime($filename)) < 60) {
                 return false;
               }   
             } else {
               die("check :" . $filename);
             }
          }
          if (!isset($wfuOptions['sync_extensions']) || ($wfuOptions['sync_extensions'] == '')) {
            return true;
          } else {  
            $ae = array_map("trim", explode(",", strtolower($wfuOptions['sync_extensions']))); // we need an array here and trim spaces too.
            $ext = WFUSync::getExtension($filename);
            return in_array($ext, $ae); 
         }
       }

    }}
?>