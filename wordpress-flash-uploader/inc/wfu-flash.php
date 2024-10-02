<?php
/**
 *   Wordpress Flash uploader 3.2.x
 *   This file contains all methods used on the main wfu page from the WFU class
 *
 *   Copyright (c) 2004-2017 TinyWebGallery
 *   Author: Michael Dempfle
 *   Author URI: http://www.tinywebgallery.com 
 */

if (!class_exists("WFUFlash")) {
    class WFUFlash {

        static function get_user_groups($id) {
           global $wpdb;
          
          if($wpdb->get_var("SHOW TABLES LIKE 'ps_groups'") != 'ps_groups') {
            return null;
          } else {
          $currGroups = array();
          $sqlCurrGroups = $wpdb->prepare("
              SELECT 
                  {$wpdb->prefix}ps_groups.ID,
                  {$wpdb->prefix}ps_groups.group_title
              FROM {$wpdb->prefix}ps_groups
              JOIN {$wpdb->prefix}ps_group_relationships
                  ON {$wpdb->prefix}ps_group_relationships.grel_group_id = {$wpdb->prefix}ps_groups.ID
              WHERE {$wpdb->prefix}ps_group_relationships.grel_user_id = '%s'
              ",$id);
          foreach($wpdb->get_results($sqlCurrGroups) as $curGrp){
              $currentGroups[$curGrp->ID] = $curGrp->group_title;
          }
          return  $currentGroups;
          }
        }

        static function printWFU($devOptions, $istab) {
            $current_user = wp_get_current_user();
            
            $show_flash = true;
            $htaccess_path = dirname(__FILE__) . '/../tfu/.htaccess';
            $reg_path = dirname(__FILE__) . '/../tfu/twg.lic.php';
           
            // now we check all possible actions if the correct nonce is set.
            if (isset($_POST['upload_media'])|| isset($_POST['upload_wordpress']) || isset($_POST['create_htaccess']) || isset($_POST['delete_htaccess']) ) {
                $nonce=$_POST['wfunonce'];
                if (! wp_verify_nonce($nonce, 'wfu-nonce') ) die('Security check failed!');
            } 
            // nounce is set porperly - we continue...   
               
            ob_start();
            @session_start();
            ob_end_clean();
            $_SESSION["IS_ADMIN"] = "true";
            $_SESSION["WPINC"] = WPINC;
            $_SESSION["WP_PLUGIN_FOLDERNAME"] = WP_CONTENT_FOLDERNAME . '/' . basename(WP_PLUGIN_DIR);
            $_SESSION["WFU_USER_LOGIN"] = $current_user->user_login;

            $_SESSION["WFU_USER_ROLE"] = array_shift($current_user->roles);
            // need an easy way to get the groups of a user...
            $_SESSION["WFU_USER_GROUPS"] = WFUFlash::get_user_groups($current_user->ID);
             
            WFUFlash::storeSettingsToSession($devOptions);
            WFUFlash::setUploadFolder();

            if ($istab) {
                echo '<div style="clear: both;"></div>';
            }
            $nonce= wp_create_nonce ('wfu-nonce'); 
            echo '<div id="wfu">';
            echo '<form method="post" action="'. $_SERVER["REQUEST_URI"] . '">';       
            echo '<input type="hidden" name="wfunonce" value="'.$nonce.'">';
            echo '<div class="wrap wfupadding">';
            echo '<div id="icon-upload" class="icon_jfu"><br></div>';
            echo '<h2>WP Flash Uploader</h2>';

            if (current_user_can('manage_options') && !$istab) {
                echo '<p>Please select if you want to upload a media file or if you want to manage Wordpress.</p>';
                echo '<div class="submit" style="padding:0px;">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="button-primary" name="upload_media" value="';
                echo _e('Media', 'WFU');
                echo  '" />&nbsp;&nbsp;&nbsp;&nbsp;';
                echo '<input type="submit" name="upload_wordpress" class="button-primary" value="';
                echo _e('Wordpress', 'WFU');
                echo '" />';

                echo '<p class="howto">If you select \'Media\' the files are copied where the normal wordpress upload would upload the file (upload/&lt;year&gt;/&lt;month&gt;).<br>If you select \'Wordpress\' you can upload to writeable wordpress folders and manage your Wordpress installation. Only files in the media folder are synched!</p>';
            }
            echo '<p>Choose files to upload. You can add titles and description for the media files after the upload on the media library page.</p>';
            if (false) { // !file_exists($reg_path)
                if ($istab) {
                    echo '<p>Please <strong>synchronize</strong> the media library after the upload on the "Sync" tab.</p>';
                } else {
                    echo '<p>Please <strong>synchronize</strong> the media library after the upload on the "Sync media library" menu entry.</p>';
                }
            }
            
            // could be set in the frontedn and has to be reset.
            if (isset($_SESSION["WFU_SHORTCODE_CONFIG"])) {
              unset($_SESSION["WFU_SHORTCODE_CONFIG"]);
            }
            echo WFUFlash::printFlash($devOptions);
   
            echo '<br>&nbsp;';
            if ($devOptions['enable_auto_sync'] == 'true') {
                echo '<div id="status" name="status"><strong>Synchronisation status:</strong> <span id="status_text">Files will be automatically synchronized after upload.</span></div><br>
                      <div id="statusframediv" style="display:none;" name="statusframediv"><iframe id="statusframe" name="statusfame" src="about:blank"></iframe></div>';
                echo '<script type="text/javascript">';
                echo 'function uploadFinished(loc) {';
                echo 'document.getElementById("status_text").innerHTML = "Starting synchronisation. Please wait..."; ';
                echo 'document.getElementById("statusframe").src="upload.php?page=wordpress-flash-uploader.php?printSync=true&import_media_library=true&isFlash=true"';
                echo '}';  
                echo 'function deleteFile(loc) {';
                //echo 'document.getElementById("statusframe").src="upload.php?page=wordpress-flash-uploader.php?printSync=true&clean_media_library=true"';
                echo '}'; 
                echo '</script>';
            } else {
                echo '<div id="status" name="status"><strong>Synchronisation status:</strong> Auto synch disabled. Please synchronize the files manually.</div><br>';
            }

            if (!$istab && current_user_can('manage_options') && $devOptions['hide_htaccess'] == 'false') {
                if (!file_exists($htaccess_path)) {
                    echo '<div class="setting-description" style="float:left">If you get the error message in the flash that you have to copy the provided <br>.htaccess file please click on the button on the right to create this file.</div>';
                    echo '<div class="submit" style="padding:5px; style="float:left">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="create_htaccess" class="button-primary" value="';
                    echo _e('Create .htaccess', 'WFU');
                    echo  '" />';
                } else {
                    echo '<div class="howto" style="float:left">You have a .htaccess file in your flash directory. If your upload <br>still not work please remove this file by clicking on the right button.<br>Please go to <a target="blank" class="nounderline" href="http://blog.tinywebgallery.com/wfu/wfu-faq/">blog.tinywebgallery.com/wfu/wfu-faq/</a> for more help.</div>';
                    echo '<div class="submit" style="padding:5px; margin-left:20px; float:left;">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="delete_htaccess" value="';
                    echo _e('Delete .htaccess', 'WFU');
                    echo  '" />';
                }
            }
            echo '</div></form></div><div style="clear:both" />';

            if (!$istab && $devOptions['hide_donate'] == 'false') {
                echo '<br><table><tr><td>You like this plugin? Support the development with a small donation. </td><td>&nbsp;&nbsp;&nbsp;<A target="_blank" HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40mdempfle%2ede&item_name=WP%20Flash%20Uploader&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=EN&bn=PP%2dDonationsBF&charset=UTF%2d8"><img src="../wp-content/plugins/wordpress-flash-uploader/img/btn_donate_SM.gif"></A></td></tr></table>';
            }
        }

        static function create_htaccess() {
            $filename = dirname(__FILE__) . "/../tfu/.htaccess";
            ob_start();
            $file = @fopen($filename, 'w');
            @fputs($file, "SecFilterEngine Off\nSecFilterScanPOST Off");
            @fclose($file);
            ob_end_clean();
            if (file_exists($filename)) {
                echo '<div class="updated"><p><strong>';
                echo 'The .htaccess file was created successfully.';

            } else {
                echo '<div class="error"><p><strong>';
                echo 'The .htaccess file could not be created. Please make the folder wp-content/plugins/tfu writable. You can change this permission back after the file was created.';
            }
            echo '</p></strong></div>';
        }

        static function delete_htaccess() {
            $file = dirname(__FILE__) . "/../tfu/.htaccess";
            @unlink($file);
            echo '<div class="updated"><p><strong>';
            echo 'The .htaccess file was deleted.';
            echo '</p></strong></div>';

        }

        static function storeSettingsToSession($devOptions) {
            global $current_user;

            if (!empty($devOptions)) {
                foreach ($devOptions as $key => $option) {
                    $_SESSION['TFU_' . strtoupper($key)] = $option;
                }
            }
            wp_get_current_user();
            $_SESSION['TFU_USER'] =  $current_user->user_login;
            $_SESSION['TFU_USER_EMAIL'] = $current_user->user_email;
            $_SESSION['TFU_USER_ID'] = $current_user->ID;
        }
        
        static function mkdir_recursive($pathname, $dir_chmod=0)
        {             
            is_dir(dirname($pathname)) || WFUFlash::mkdir_recursive(dirname($pathname), $dir_chmod);
            return is_dir($pathname) || @mkdir($pathname);
        }
        
        static function printFlash($devOptions, $rel_dir = "../", $admin = 'true', $siteurl = '../') {    
            $htaccess_path = dirname(__FILE__) . '/../tfu/.htaccess'; 
            $relative_dir = dirname($_SERVER['PHP_SELF']);
            $relative_dir = rtrim($relative_dir,"\\/.") . '/'; // we replace to get a consistent output with different php versions!
            // use WP_CONTENT_FOLDERNAME
            // https://codex.wordpress.org/Determining_Plugin_and_Content_Directories
            $base_dir = $rel_dir . $_SESSION["WP_PLUGIN_FOLDERNAME"] . "/wordpress-flash-uploader/tfu";
            $width = $devOptions['flash_size'];   
  
            ob_start();
            // stores the session to the session_cache folder if the workaround is activated!
            store_temp_session();
            $id = session_id();
            session_write_close();
            ob_end_clean();
          
           $height=floor($width*(340/650));
           if ($height > 390) $height = floor($height * 0.95);
         
           $output = '';
           $js_file = dirname(__FILE__) . '/../wordpress-flash-uploader.js'; 
           if ($siteurl != "../" && file_exists($js_file)) {            
              $output .= '<script type="text/javascript">';
              $output .= file_get_contents($js_file); 
              $output .= '</script>'; 
           }
            
            $output .= '
           <div style="height:'.$height.'px;">
           <script type="text/javascript" src="'.plugins_url().'/wordpress-flash-uploader/tfu/swfobject.js"></script>
           <script type="text/javascript">
           function debugError(errorString) { }
           function refreshFileList() {
             var obj = document.getElementById("flash_tfu");
             if (obj && typeof obj.refreshFileList != "undefined") {
             obj.refreshFileList();
             }
           }           
               document.write(\'<div id="flashcontent"><div class="noflash">TWG Flash Uploader requires at least Flash 8.<br>Please go to <a target="blank" href="http://www.adobe.com/go/EN_US-H-GET-FLASH">adobe</a> and install it.';
            if (file_exists($htaccess_path)) {
                $output .= '<p>You have created a .htaccess file which seems not to help on your server. Please go to <a target="blank" class="nounderline" href="http://blog.tinywebgallery.com/wfu/wfu-faq/">blog.tinywebgallery.com/wfu/wfu-faq/</a> for more help.</p>';
            }
            $output .= '<\/div><\/div>\');

          var flashvars = {};
          var params = {};
          var attributes = { id: "flash_tfu", name: "flash_tfu" };
          
          params.allowfullscreen = "true";
          flashvars.wordpress="'.$admin.'";
          flashvars.session_id="'. $id .'";	
          flashvars.base="'.$base_dir.'";
          flashvars.relative_dir="'.$relative_dir.'";';
          if ($width == '650') {
            $output .= 'params.scale = "noScale";';
          } 
          if ($devOptions['swf_text']) {
            $elements = explode('&',$devOptions['swf_text']);
            foreach ($elements as $element) {
              $output .= "flashvars." . str_replace("=", "=\"", $element) . "\";";
            }
          }
          $output .= '
          swfobject.embedSWF("'.plugins_url().'/wordpress-flash-uploader/tfu/tfu_3.2.swf", "flashcontent", "'.$width.'", "'.$height.'", "8.0.0", "", flashvars, params, attributes);

          </script>
          </div>
          ';          
          return $output;
     }
     
     static function setUploadFolder($subdir='', $dir_chmod = 0) {
            // this is the default where no form is set.
            if (!isset($_POST['upload_wordpress'])) {
                $wp_upload_dir_array = wp_upload_dir();
                $path = $wp_upload_dir_array['path'];
                $folder =  '../../../../'. substr($path, strlen(get_home_path())); 
            }            
            if (isset($_POST['upload_wordpress'])) {
                $path = '../';
                $folder = '../../../../';      
           } else if (isset($_POST['create_htaccess'])) {
                WFUFlash::create_htaccess();
            } else if (isset($_POST['delete_htaccess'])) {
                WFUFlash::delete_htaccess();
            }
            if (!@is_writeable($path)) {
                // we try to chmod to make the folder writeable. We increase the permissions step by step
                // (755, 775 and finally 777). If nothing works we display a message
                ob_start();
                @chmod($path, 0755);
                ob_end_clean();
                @clearstatcache();
                if (!@is_writeable($path)) {
                    ob_start();
                    @chmod($path, 0775);
                    @clearstatcache();
                    if (!@is_writeable($path)) {
                        @chmod($path, 0777);
                        @clearstatcache();
                    }
                    ob_end_clean();
                    if (!@is_writeable($path)) {
                        echo '<div class="error"><p>';
                        echo 'The upload folder "'. substr(stristr($path, '../../../../'),12) .'" is not writeable. Please change the permissions with a FTP program.</p>';
                        echo '</p></div>';
                    }
                }
            }
            $_SESSION["TFU_FOLDER"] =  $folder;
            }
    }}
?>