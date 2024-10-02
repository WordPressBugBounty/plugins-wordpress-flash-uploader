<?php
/*
Plugin Name: Wordpress Flash Uploader
Plugin URI: http://www.tinywebgallery.com/blog/wfu
Description: The Wordpress Flash Uploader does contain 2 plugins: '<strong>Wordpress Flash Uploader</strong>' and '<strong>Sync Media Library</strong>'. The Wordpress Flash Uploader is a flash uploader that replaces the existing flash uploader and let you manage your whole  WP installation. You can also include the flash to your site and enable your users to upload/manage files based on user names, groups or roles. 'Sync Media Library' is a plugin which allows you to synchronize the Wordpress database with your upload folder. You can upload by WFU, FTP or whatever and import this files to the Media Library. 
Version: 3.4.2
Text Domain: WFU
Author: Michael Dempfle
Author URI: http://www.tinywebgallery.com
*/
// all parts are in an extra file in the inc folder.

// @ini_set("display_errors","1");

define('WP_CONTENT_FOLDERNAME', basename(WP_CONTENT_DIR));

include 'inc/wfu-flash.php';		
include 'inc/wfu-settings.php';
include 'inc/wfu-sync.php';

define('_VALID_TWG', '42');

include 'tfu/wfu_helper.php';

if (!class_exists("WFU")) {
    class WFU {
        var $adminOptionsName = "WFUAdminOptions";

        var $wfu_flash;
        var $wfu_settings;
        var $wfu_sync;
        var $nonce;

        var $unique_sizes_filter='';

        function __construct() { //constructor
            $wfu_flash = new WFUFlash();
            $wfu_settings = new WFUSettings();
            $wfu_sync = new WFUSync();
        }

        function init() {
            $this->getAdminOptions();
        }
        //Returns an array of admin options
        function getAdminOptions() {
        
            $arr = array();
                      
            $arr[] = get_option('large_size_h');
            $arr[] = get_option('large_size_w');
            $arr[] = get_option('medium_size_h');
            $arr[] = get_option('medium_size_w');
            $arr[] = get_option('thumbnail_size_h');
            $arr[] = get_option('thumbnail_size_w');
            $arr[] = "85x85"; 
            $arr[] = "280x125";
            
            $unique_sizes=array_unique($arr);
            
            $this->unique_sizes_filter = '';
            foreach($unique_sizes as $size) {
               if ($this->unique_sizes_filter != '') {
                 $this->unique_sizes_filter .= ',';
               }
               $this->unique_sizes_filter .= '*'.$size.'*.*';
            }
           
            
            $wfuAdminOptions = array(
                'wp_path' => '',
                'maxfilesize' => '',
                'resize_show' => 'true',
                'resize_data' => '100000,1024',
                'resize_label' => 'Original,1024',
                'resize_default' => '0',
                'allowed_file_extensions' => 'all',
                'forbidden_file_extensions' => 'php',
                'enable_folder_browsing' => 'true',
                'enable_folder_handling' => 'true',
                'enable_file_rename' => 'false',
                'show_size' => 'true',
                'normalize' => 'true', // don#t change this - wordpress cannot handle unnormalized files !!!
                'file_chmod' => '',
                'dir_chmod' => '',
                'language_dropdown' => 'de,en,es',
                'use_image_magic' => 'false',
                'image_magic_path' => 'convert',
                'upload_notification_email' => '',
                'upload_notification_email_from' => '',
                'upload_notification_email_subject' => 'Files where uploaded by the WP Flash Uploader',
                'upload_notification_email_text' => 'The following files where uploaded by %s: %s',
                'enable_file_download' => 'true',
                'preview_textfile_extensions' => 'log,php',
                'edit_textfile_extensions' => 'txt,css,html',
                'exclude_directories' => 'svn',
                'enable_folder_move' => 'true',
                'enable_file_copymove' => 'true',
                'swf_text' => '',
                'show_wfu_media' => 'true',
                'show_sync_media' => 'true', 
                'show_wfu_tab' => 'true',
                'show_sync_tab' => 'true', 
                'hide_donate' => 'false',
                'hide_htaccess' => 'false',
                'detect_resized' => 'true', 
                'file_filter' => $this->unique_sizes_filter,
                'flash_size' => '650', // default in the backend - can be owerwritten by the frontend
                'securitykey' => sha1(session_id()),
                'frontend_upload_folder' => '',
                // new 2.14
                'master_profile' => 'false',
                'master_profile_type' => 'master_profile_type_username',  
                // new 2.15
                'sync_extensions' => '',
                'scheduler' => 'none',
                // new 2.16
                'frontend_javascript' => '',
                // new 2.17
                 'sync_time' => '',
                 'synch_max_files' => 'auto',
                 'sync_warning_message' => 'true',
                 // new 3.3
                 'remove_invalid' => 'false',
                 'enable_auto_sync' => 'true'                        
            );

            $wfuOptions = get_option($this->adminOptionsName);
            if (!empty($wfuOptions)) {
              foreach ($wfuOptions as $key => $option) {
                $wfuAdminOptions[$key] = $option;
              }
            }
            update_option($this->adminOptionsName, $wfuAdminOptions);
            return $wfuAdminOptions;
        }

        function activate(){
            global $wp_version;
            if( ! version_compare( $wp_version, '2.7-alpha', '>=') ) {
                
                $message = __('<h1>Wordpress Flash Uploader</h1><p> Sorry, This plugin requires WordPress 2.7+</p>.', 'wfu');
                if( function_exists('deactivate_plugins') ) {
                   deactivate_plugins(__FILE__);    
                } else {
                   $message .= __('<p><strong>Please deactivate this plugin.</strong></p>', 'wfu');
                }
                wp_die($message);
            }      
        }
        
        function deactivate(){ 
           if ( wp_next_scheduled('wfu_task_hook') ) {
               wp_clear_scheduled_hook('wfu_task_hook');
           }
        }

        /* CSS für den Admin-Bereich von WFU */
        function addAdminHeaderCode($hook) {
             if( $hook != 'settings_page_wordpress-flash-uploader' && $hook != 'toplevel_page_wordpress-flash-uploader') 
         		    return;
            echo '<link type="text/css" rel="stylesheet" href="' . plugins_url() .'/wordpress-flash-uploader/css/wfu.css" />' . "\n";
            $wfuOptions = $this->getAdminOptions();
        }

        function printWFU($istab = false) {
            WFUFlash::printWFU($this->getAdminOptions(), $istab);
        }

        function printSync($istab = false, $check_nonce = true) {
            WFUSync::printSync($this->getAdminOptions(), $istab, $check_nonce);
        }


        function validateInput($key, $old_value, $new_value) {
            if (function_exists('sanitize_text_field')) {
                $new_value = stripslashes(sanitize_text_field($new_value));
            } else {
                $new_value = stripslashes($new_value);
            }
            
            // additional restrictions for image magick
            if ($key == 'image_magic_path') {
              $invalid = array(" ", ";", "'", "\"", "|", "<", ">", "-");
              $test_value = str_replace($invalid, "_",  $new_value);
              if ($new_value != $test_value) {
                    return false;
                }
            } 

            $old_value = trim(strtolower($old_value));
            $new_value = trim(strtolower($new_value));

            // booleans
            $possible_values = array( 'true', 'false', 'button', 'button1' );
            $black_list = array('<', '>', './', '://','cookie','popup','open(', 'alert','refresh', 'varchar', 'onmouse', 'javascript');
            if ( in_array( $old_value, $possible_values ) ) { // we have a defined value - only possilbe vlaues are allowed
                return in_array( $new_value, $possible_values ); // new value has to be in array
            }
            // if we have still a possible value it's treated as bad input
            if ( in_array( $new_value, $possible_values ) ) {
                return false; // new value has to be in array
            }
            // currently a blacklist is used for validation - it's not very strict but we are already in the backend and not everyone should have access here anyway.
            foreach ($black_list as $value) {
                if (strpos($new_value, $value) !== false) {
                    return false;
                }
            }
            return true;
        }

        //Prints out the admin page
        function printAdminPage() {
            $wfuOptions = $this->getAdminOptions();

             // now we check all possible actions if the correct nonce is set.
            if (isset($_POST['update_WFUSettings']) || isset($_POST['register_WFU'])  || isset($_GET['unregister_WFU']) ) {
                $nonce=$_POST['wfunonce'];
                if (! wp_verify_nonce($nonce, 'wfu-nonce') ) die('Security check failed!');
            } 
            // nounce is set porperly - we continue... 
 
            // we save all settings
            if (isset($_POST['update_WFUSettings'])) {             
                $failure = false;
                // simple fields
                foreach ($wfuOptions as $key => $option) {
                    if (isset($_POST[$key])) { // if is set
                        // we validate
                        $ok = $this->validateInput($key, $option, $_POST[$key]);
                        if ($ok){
                            if ($key == 'file_filter' && $_POST[$key] == 'auto') {
                                $wfuOptions[$key] = $this->unique_sizes_filter;
                            } else {
                                $wfuOptions[$key] = $_POST[$key];
                            }
                        } else {
                            $failure = true;
                        }
                    }
                }
                
                

                // fields that need special treatment
                update_option($this->adminOptionsName, $wfuOptions);

                if ($failure) {
                    echo '<div class="error linepad"><p><strong>';
                    echo _e("Settings did not validate. Only valid entries where saved.", "WFU");
                } else {
                    echo '<div class="updated linepad"><p><strong>';
                    echo _e("Settings Updated.", "WFU");
                }
                echo '</strong></p></div>';
            } else 	if (isset($_POST['register_WFU'])) {
                $isvalid = $this->validateInput('', '', $_POST['l']) && $this->validateInput('', '', $_POST['s']) && $this->validateInput('', '', $_POST['d']);
                $l = $_POST['l'];
                $d = $_POST['d'];
                $s = $_POST['s'];
                if ($isvalid && strlen($s) == 67) {
                    $filename = dirname(__FILE__) . "/tfu/twg.lic.php";
                    $file = fopen($filename, 'w');
                    fputs($file, "<?php\n");
                    fputs($file, "\$l=\"".$l."\";\n");
                    fputs($file, "\$d=\"".$d."\";\n");
                    fputs($file, "\$s=\"".$s."\";\n");
                    fputs($file, "?>");
                    fclose($file);

                    if (!file_exists($filename)) {
                        echo '<div class="error linepad"><p><strong>';
                        echo _e("The license file could not be created. Please create the file manually like described in the registration e-mail.", "WFU");
                    } else {
                        echo '<div class="updated linepad"><p><strong>';
                        echo _e("You license file was created successful. Please to to the flash and check if the registration works properly.", "WFU");
                    }
                } else {
                    echo '<div class="error linepad"><p><strong>';
                    echo _e("The license data is not valid. Please enter the data exaclty like in the registration e-mail. If you think your input is right please create the license file manually like described in the registration e-mail.", "WFU");
                }
                echo '</strong></p></div>';

            } else 	if (isset($_POST['unregister_WFU'])) {
                echo '<div class="updated linepad"><p><strong>';
                unlink (dirname(__FILE__) . "/tfu/twg.lic.php");
                echo _e("Registration file was deleted.", "WFU");
                echo '</strong></p></div>';
            }

            // the new nonce tocken!
            $nonce= wp_create_nonce ('wfu-nonce'); 
            echo '<div id="wfu" class="wrap"><form method="post" action="'. $_SERVER["REQUEST_URI"] . '">';
            echo '<input type="hidden" name="wfunonce" value="'.$nonce.'">';
            WFUSettings::printSyncSettings($wfuOptions);
            WFUSettings::printWordpressOptions($wfuOptions);
            WFUSettings::printFrontendOptions($wfuOptions); 
            WFUSettings::printOptions($wfuOptions);
            WFUSettings::printAdvancedOptions();
            WFUSettings::printRegisteredSettings($wfuOptions);
            // Next version - basic checks are already made on the upload page.
            // WFUSettings::printSystemCheck();
            WFUSettings::printServerInfo();
            WFUSettings::printRegistration($wfuOptions);
            WFUSettings::printLicense();
            WFUSettings::printNextVersion();

            echo '
<p>&nbsp;</p>
<center><div class="howto">WFU - WP Flash Uploader - Copyright (c) 2004-2012 TinyWebGallery.</div></center>
</form>
</div>';
        }//End function printAdminPage()

        //Add a tab to the media uploader:
        function tabs($tabs) {
            if( current_user_can( 'upload_files' ) ) {
                $wfuOptions = $this->getAdminOptions();
                if ($wfuOptions['show_wfu_tab'] == "true") {
                    $tabs['wfu'] = __('WP Flash Uploader');
                }
                $tabs['wfu'] = __('WP Flash Uploader'); 
                if ($wfuOptions['show_sync_tab'] == "true") {
                    $tabs['sync'] = __('Sync');
                }
            }
            return $tabs;
        }

        //Handle the actual page:
        function tab_wfu_handler(){
            if( ! current_user_can( 'upload_files' ) )
            return;
            //Set the body ID
            $GLOBALS['body_id'] = 'media-upload';
            //Do an IFrame header
            iframe_header( __('WP Flash Uploader', 'wfu') );
            //Add the Media buttons
            media_upload_header();
            //Do the content
            $this->printWFU(true);
            //Do a footer
            iframe_footer();
        }

        //Handle the actual page:
        function tab_sync_handler(){
            if( ! current_user_can( 'upload_files' ) )
            return;
            //Set the body ID
            $GLOBALS['body_id'] = 'media-upload';
            //Do an IFrame header
            iframe_header( __('Synch', 'synchwfu') );
            //Add the Media buttons
            media_upload_header();
            //Do the content
            $this->printSync(true);
            //Do a footer
            iframe_footer();
        }

        function add_tab_head_files() {
            //Enqueue support files.
            if ( 'media_upload_wfu' == current_filter()  ||  'media_upload_sync' == current_filter())
            wp_enqueue_style('media');
        }

        function aktt_plugin_action_links($links, $file) {
            $plugin_file = basename(__FILE__);
            $file = basename($file);
            if ($file == $plugin_file) {
                $settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', 'wfu').'</a>';
                array_unshift($links, $settings_link);
            }
            return $links;
        }
        
         // [wfu]
         function wfu_func($atts) {
	        extract(shortcode_atts(array(
	   	      'securitykey' => 'xxx',
		        'width' => '650',
		        'configid' => '',
            'master_profile' => '',
            'master_profile_type' => '',
            'frontend_upload_folder' => '' 
	        ), $atts));	          
	             // could already be started by another plugin.
               if(session_id() == '') {
                 @session_start();
               }
                
               $_SESSION["IS_ADMIN"] = "true";
               $_SESSION["WPINC"] = WPINC;
               $_SESSION["WP_PLUGIN_FOLDERNAME"] = WP_CONTENT_FOLDERNAME . '/' . basename(WP_PLUGIN_DIR);
               $devOptions = $this->getAdminOptions();
               
               if ($devOptions['securitykey'] == $securitykey) {
                  
                  // set the settings of the shortcode options.
                  if (!empty($master_profile)) {
                    $devOptions['master_profile'] = $master_profile;
                  }
                  if (!empty($master_profile_type)) {
                    $devOptions['master_profile_type'] = $master_profile_type;
                  }
                  if (!empty($frontend_upload_folder)) {
                    $devOptions['frontend_upload_folder'] = $frontend_upload_folder;
                  }
                   if (is_numeric ($width)) {
                   $devOptions['flash_size'] = $width;
                 }
                  
                  $current_user = wp_get_current_user();
                  $logged_id = (0 != $current_user->ID );              
                  $showflash = $logged_id || $devOptions['master_profile'] =='false' || ($devOptions['master_profile'] =='true' && $devOptions['master_profile_type'] == 'master_profile_type_ip');
                 if ($showflash) {

                 if ($configid != '' && is_numeric ($configid)) {
                    $_SESSION["WFU_SHORTCODE_CONFIG"] = $configid; 
                 } else if (isset($_SESSION["WFU_SHORTCODE_CONFIG"])) {
                    unset($_SESSION["WFU_SHORTCODE_CONFIG"]);
                 }
                 WFUFlash::storeSettingsToSession($devOptions); 
                 unset($_SESSION["IS_ADMIN"]);
	               $_SESSION["IS_FRONTEND"] = "true";
                 $_SESSION["WPINC"] = WPINC;
	               $_SESSION["WP_PLUGIN_FOLDERNAME"] = WP_CONTENT_FOLDERNAME . '/' . basename(WP_PLUGIN_DIR);
                 if ($logged_id) {
                   $_SESSION["WFU_USER_LOGIN"] = $current_user->user_login;
                   $_SESSION["WFU_USER_ROLE"] = array_shift($current_user->roles);
                   $_SESSION["WFU_USER_EMAIL"] = $current_user->user_email;
                 } else {
                   unset($_SESSION["WFU_USER_LOGIN"]);
                   unset($_SESSION["WFU_USER_ROLE"]);
                   unset($_SESSION["WFU_USER_EMAIL"]);
                }
	              $dir_chmod=($devOptions['dir_chmod'] == '') ? 0 : octdec($devOptions['dir_chmod']);
	            
              if ($devOptions['frontend_upload_folder'] == '') {
	              WFUFlash::setUploadFolder('', $dir_chmod);
	            } else {
	               $pathprefix = '../../../../';
                   if ($devOptions['master_profile'] =='true') {
                     // get type
                     if ($devOptions['master_profile_type'] == 'master_profile_type_username') {
                       $subdir = $current_user->user_login; 
                     } else if ($devOptions['master_profile_type'] == 'master_profile_type_display') {  
                       $subdir = $current_user->display_name;
                     } else if ($devOptions['master_profile_type'] == 'master_profile_type_id_name') {  
                       $first = get_user_meta( $current_user->ID, 'first_name', true );  
                       $last = get_user_meta( $current_user->ID, 'last_name' , true );   
                       $subdir = sprintf('%05d', $current_user->ID);
                       if (!empty($last)) {
                           $subdir .= '_' . $last;
                       } 
                       if (!empty($first)) {
                           $subdir .= '_' . $first;
                       }
                      
                     } else if ($devOptions['master_profile_type'] == 'master_profile_type_ip') {  
                       $subdir = $_SERVER['REMOTE_ADDR'];
                     } else {
                       $subdir = $current_user->ID;
                     }
                     // check folder, create if not exists
                     $userdir = $pathprefix . $devOptions['frontend_upload_folder'] . '/' . $subdir;
                     $work_userdir = $devOptions['frontend_upload_folder'] . '/' . $subdir;
                     if (!file_exists($work_userdir)) {                       
                        WFUFlash::mkdir_recursive($work_userdir, $dir_chmod);
                     }
                     $_SESSION["TFU_FOLDER"] = $userdir;
                   } else {
                     $_SESSION["TFU_FOLDER"] =  $pathprefix . $devOptions['frontend_upload_folder'];
                   }                           
                 }           
                   $js = '<script type="text/javascript">function uploadFinished(loc) {}; function deleteFile(loc) {} </script>';
   
                   // Fix 2.12.1 - relative path was not good because of permurls !
                   $siteurl = get_option('siteurl') . '/';    
                   return $js . WFUFlash::printFlash($devOptions , '/' , 'frontend', $siteurl) ;
                 } else {
                    return '<div style="padding:10px; margin:10px; border: 1px solid #555555; background-color: #f8f8f8; text-align:center; width:330px;">Please login. The flash is configured that a user has to be logged in to use it.</div>';
                 
                 }
               } else {
                 return '<div style="padding:10px; margin:10px; border: 1px solid #555555; background-color: #f8f8f8; text-align:center; width:330px;">A wrong security key is used - please read the documentation how to use the flash in the frontend.</div>';
               }
             }
        
         // add custom time 30 m to cron
         function filter_cron_schedules( $param ) {
             return array( 
              'every_min' => array(
                'interval' => 60, // seconds
                'display'  => __( 'Every minute' )
                ) ,
               'every_5_min' => array(
                'interval' => 300, // seconds
                'display'  => __( 'Every 5 minutes' )
                ),    
               'every_10_min' => array(
                'interval' => 600, // seconds
                'display'  => __( 'Every 10 minutes' )
                ), 
                 'every_30_min' => array(
                'interval' => 1800, // seconds
                'display'  => __( 'Every 30 minutes' )
                )
              );
         }
       
    
      /**
       * Called by the cron job !
       */ 
      function wfu_task_function() {
           require_once(ABSPATH . 'wp-admin/includes/image.php');
           $_POST['synchronize_media_library'] = 'true';
           $this->printSync(true, false);
      }
      
      function Initialize() {  
            // could already be started by another plugin.
            @ob_start();
            @session_start();
            @ob_end_clean();
      }

               
    } } //End Class WFU

if (class_exists("WFU")) {
    $dl_pluginSeries = new WFU();
}

//Initialize the admin panel
if (!function_exists("WFU_ap")) {
    function WFU_ap() {
        global $dl_pluginSeries;
        if (!isset($dl_pluginSeries)) {
            return;
        }
        if (function_exists('add_options_page')) {
            add_options_page('WP Flash Uplader', 'WP Flash Uploader', 'manage_options', basename(__FILE__), array(&$dl_pluginSeries, 'printAdminPage'));
        }
        $wfuOptions = $dl_pluginSeries->getAdminOptions();
        if (function_exists('add_media_page')&& $wfuOptions['show_wfu_media'] == "true") {
            add_media_page('WP Flash Uploader', 'WP Flash Uploader', 'upload_files', basename(__FILE__), array(&$dl_pluginSeries, 'printWFU'));
        }
        if (function_exists('add_media_page')&& $wfuOptions['show_sync_media'] == "true") {
            add_media_page('Sync Media Library', 'Sync Media Library', 'upload_files', basename(__FILE__) . '?printSync=true', array(&$dl_pluginSeries, 'printSync'));
        }
    }
}


//Actions and Filters	
if (isset($dl_pluginSeries)) {
    register_activation_hook(__FILE__, array(&$dl_pluginSeries, 'activate'));
    register_deactivation_hook (__FILE__, array(&$dl_pluginSeries, 'deactivate'));
    //Actions
    add_action('admin_menu', 'WFU_ap');
    add_action('wordpress-flash-uploader/wordpress-flash-uploader.php',  array(&$dl_pluginSeries, 'init'));
    add_action('admin_enqueue_scripts', array(&$dl_pluginSeries, 'addAdminHeaderCode'),99);

    add_action('media_upload_wfu', array(&$dl_pluginSeries, 'add_tab_head_files') );
    add_action('media_upload_sync', array(&$dl_pluginSeries, 'add_tab_head_files') );

    add_filter('media_upload_tabs', array(&$dl_pluginSeries, 'tabs'));
    add_action('media_upload_sync', array(&$dl_pluginSeries, 'tab_sync_handler'));
    add_action('media_upload_wfu', array(&$dl_pluginSeries, 'tab_wfu_handler'));
    //Filters
    add_filter('plugin_action_links', array(&$dl_pluginSeries, 'aktt_plugin_action_links'),10,2);

    add_shortcode('wfu', array(&$dl_pluginSeries,'wfu_func'));

    add_filter( 'cron_schedules', array( &$dl_pluginSeries, 'filter_cron_schedules' ) );
    add_action('wfu_task_hook', array( &$dl_pluginSeries, 'wfu_task_function' ) );
    add_action('init',  array( &$dl_pluginSeries, 'Initialize' ) );
    
    $wfuOptions = $dl_pluginSeries->getAdminOptions();
    if ( $wfuOptions['scheduler'] != 'none' && !wp_next_scheduled('wfu_task_hook') ) {
       wp_schedule_event( time(),  $wfuOptions['scheduler'], 'wfu_task_hook' ); // hourly, daily and twicedaily
    } else if  ($wfuOptions['scheduler'] == 'none') {
        wp_clear_scheduled_hook('wfu_task_hook');
    }
}

?>