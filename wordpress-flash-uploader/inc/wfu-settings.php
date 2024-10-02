<?php
/**
 *   Wordpress Flash uploader 3.2.x
 *   This file contains all the methods for the settings screen from the WFU class
 *  
 *  Copyright (c) 2004-2017 TinyWebGallery
 *  Author: Michael Dempfle
 *  Author URI: http://www.tinywebgallery.com    
 *  
 */
if (!class_exists("WFUSettings")) {
    class WFUSettings {
        static function printWordpressOptions($devOptions) {
            echo '
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Settings</h2>
<div class="wfu_reg nounderline">
<h3>Settings</h3>
<a href="#wor">Wordpress Options</a> | 
<a href="#front">Frontend Options</a> | 
<a href="#bas">Basic Options</a> | 
<a href="#adv">Advanced Options/ profiles</a> | 
<a href="#reg">Registered Options</a>
<h3>Info</h3>
<a href="#lim">Limits</a> | <a href="#don">Donation / Registration</a> | 
<a href="#lic">License</a> | <a href="#com">Coming next</a>
<h3>Help</h3>
<a target="_blank" href="http://www.tinywebgallery.com/blog/wfu">Website</a> | <a target="_blank"  href="http://www.tinywebgallery.com/blog/wfu-faq">FAQ/Help</a> | <a target="_blank" href="http://www.tinywebgallery.com/en/forum.php">Forum</a>              
<br>&nbsp;
<div style="height:1px;width:100%;background-color:#aaa;"></div>
</div>
<a name="wor"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Wordpress Options</h2>
<strong>Please note</strong>: Only users that can manage settings have the option
 on the \'WP Flash Uploader\' page to manage the Wordpress install. They will get 2 buttons where they can switch between the main directory of Wordpress and the upload folder.
<table class="form-table">';
            WFUSettings::printTrueFalse($devOptions, 'Show \'WP Flash Uploader\' in Media menu',  'show_wfu_media', '');
            WFUSettings::printTrueFalse($devOptions, 'Show \'WP Flash Uploader\'  in Media tabs',  'show_wfu_tab', 'If you add a new post you can insert/select new media files. You can include WFU to the tabs there and upload your images to the media library and include it then directly. In this view the flash is shown which is preconfigured to the current image folder. You cannot manage your webspace there like when you select the menu entry.');
            
            WFUSettings::printTrueFalse($devOptions, 'Show \'Sync Media Library\' in Media menu',  'show_sync_media', '');
            WFUSettings::printTrueFalse($devOptions, 'Show \'Sync Media Library\' in Media tabs',  'show_sync_tab', 'If you add a new post you can insert/select new media files. You can include the \'Sync\' to the tabs there and include it then directly.');
            WFUSettings::printTrueFalse($devOptions, 'Hide .htaccess create option',  'hide_htaccess', 'On the WP Flash Uploader page the option to create and delete a .htaccess file is shown. once the flash is working you can hide this option.');
          
            echo '</table>';
            echo '<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
        }
        static function printFrontendOptions($devOptions) {
            echo '
<a name="front"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Front end Options</h2>
You can use the flash in the frontend by adding the following shorttag to your article or page: <p><b>[wfu securitykey="'.$devOptions['securitykey'].'"]</b></p>The security key is mandatory. There are the following optional parameters which overwrite the defaults of this section: 

<ul class="shortcode-frontend">
<li><strong>configid</strong> - You can define an optional config file. If you specify configid="1" than the config file tfu_config_shortcode_1.php is used. Create a file tfu_config_shortcode_1.php in the tfu folder of the plugin and put you individual settings there. Please see the advanced options for user, group, role based config files.</li>
<li><strong>width</strong> - Has a default of 650 px. If you want to specify the width simply add e.g. width="500".</li>
<li><strong>frontend_upload_folder</strong> - You can specify an upload folder. Please read the description below. Shortcode attribute: frontend_upload_folder=""</li>
<li><strong>master_profile</strong> - You can specify if master profiles are used. Please read the description below.  Shortcode attribute: master_profile="true" or master_profile="false".</li>
<li><strong>master_profile_type</strong> - You can specify the master profile type. Please read the description below. Shortcode attribute: master_profile_type="". Valid settings are: master_profile_type_username, master_profile_type_display, master_profile_type_id, master_profile_type_id_name, master_profile_type_ip.</li>
</ul>

Please note that the uploaded images are NOT synchronized with the media library. If the files are uploaded to the default upload folder of Wordpress this can be done at Media -> Synchronize Media Library in the administration of Wordpress.
<p>
For the front end you can specify <strong>custom configurations</strong> for individual <strong>users</strong>, <strong>groups</strong> or <strong>roles</strong>. Please read the <a class="wfu_reg nounderline" href="#adv">advanced options/ profile section</a> for details.  
</p>
<table class="form-table">';
            WFUSettings::printTextInput($devOptions, 'Security key',  'securitykey', 'This is security key which has to be used in the shorttag. This is mandatory because otherwise anyone who can create an article can use the flash. The default security key was randomly generated during installation. Please change the key if you like.');
            WFUSettings::printTextInput($devOptions, 'Upload folder',  'frontend_upload_folder', 'This is the optional upload folder for the frontend. If no folder is specified the current image upload directory is choosen. If you like a different directory simply add the folder relative to the main Wordpress installation. This makes is e.g. easy to use the uploader for a image gallery and let users without administrator access upload images too.');
            WFUSettings::printTrueFalse($devOptions, 'Master profile',  'master_profile', 'When the master profile is enabled a directory is created for each user.  The master profile is only used when you enter a \'Upload folder\' above. Make sure that you use the uploader on a page where a user is logged in. If this is not the case an error message is shown to avoid unrestricted access. Only IP can be used without a valid login! Please test if directories can be created by php with the correct rights. If not please set the permissions for new directories below in the basic options.');
            WFUSettings::printLoginId($devOptions, 'Master profile mode',  'master_profile_type', 'Selects the \'Username\', the \'Display name\', the \'Id\', the \'Id\' the \'Last name\' and the \'First name\' seperated by _,  or the \'IP\' as directory name of the sub directory of the \'Upload folder\'.'); 
          
            echo '</table>';
            echo '<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
        }
        static function printOptions($devOptions) {
           
            echo '
<a name="bas"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Basic Options</h2>
<table class="form-table">';
            WFUSettings::printTextInput($devOptions, 'Maximum file size',  'maxfilesize', 'The maximum upload file size in KB. If you leave this empty then JFU is using auto detection for the maximum settings of this server. Setting a higher value than your server limit does NOT increase the server limit. The current maximum is: <strong>'.WFUSettings::getMaximumUploadSize().' KB</stong>');
            WFUSettings::printTrueFalse($devOptions, 'Show resize',  'resize_show', 'Enables/disables the resize on the server side and shows the resize dropdown.');
            WFUSettings::printTextInput($devOptions, 'Resize values',  'resize_data', 'The sizes for the resize dropdown. Each number specifies the largest dimension an image would be resized too. 10000 means no resize. Separate the numbers by \',\'.');
            WFUSettings::printTextInput($devOptions, 'Resize label',  'resize_label', 'The labels for the resize dropdown. Each resize value needs one label. Separate the labels by \',\'.');
            WFUSettings::printTextInput($devOptions, 'Resize default',  'resize_default', 'The preselected entry in the dropdown (1st = 0).');
            WFUSettings::printTextInput($devOptions, 'Allowed file extensions',  'allowed_file_extensions', 'List of allowed files extensions. Separate them by \',\'. \'all\' allows all types. If this field is empty then the upload grid is removed and the server only view is enabled.');
            WFUSettings::printTextInput($devOptions, 'Forbidden file extensions',  'forbidden_file_extensions', 'Forbidden file extensions! - Only useful if you use \'all\' and you want to skip some extensions! Separate them by \',\'.');
            WFUSettings::printTrueFalse($devOptions, 'Enable folder browsing',  'enable_folder_browsing', '');
            WFUSettings::printTrueFalse($devOptions, 'Enable folder handling',  'enable_folder_handling', 'Enables the creation, delete and rename of folders.');
            WFUSettings::printTrueFalse($devOptions, 'Enable file rename ',  'enable_file_rename', 'BE carefull if you enable this because renamed files are like new files for the media library after the synchronize!');
            WFUSettings::printTrueFalse($devOptions, 'Show size',  'show_size', 'Enable the display of the file size on the server side.');
            // don't change this - right now wordpress cannot handle unnormalized files!!!
            // WFUSettings::printTrueFalse($devOptions, 'Normalize',  'normalize', 'Enable to normalize folder and filenames. Convert all names to lowercase and special characters are removed e.g. !"#$%&\'()*+,-- []\^_` are replaced with an _. öäü with oe,au,ue.');
            WFUSettings::printTextInput($devOptions, 'Chmod new files',  'file_chmod', 'If you leave this empty the server defaults are used. Otherwise you can specify the permissions for new files. E.g. 0777,0755,0644 ...');
            WFUSettings::printTextInput($devOptions, 'Chmod new directories',  'dir_chmod', 'If you leave this empty the server defaults are used. Otherwise you can specify the permissions for new directories. E.g. 0777,0755,0644 ...');
            WFUSettings::printTextInput($devOptions, 'Language selector',  'language_dropdown', 'Enables/disables a dropdown for the language selection. You have to specify the languages separated by \',\' (e.g. en,de,es). They are displayed in the given order! You can specify the default language in the "Additional settings" free text field. Available languages: ' . WFUSettings::getAvailableTFULanguages());
            WFUSettings::printTrueFalse($devOptions, 'Use image magick',  'use_image_magic', 'Enable image magick for the resize of the upload. Image magick uses less memory then gd lib and it does copy exif information!<br>' . WFUSettings::check_image_magic($devOptions['image_magic_path']));
            WFUSettings::printTextInput($devOptions, 'Image magick command',  'image_magic_path', 'The image magick command used to convert the images. \'convert\' is the default command of image magick. If the command is not in the path you have to specify the full path. WFU uses the command line version and not any php library. Please note that many characters like spaces,|,; are replaced by _ because of security issues. If you are not able to add the path here please set it in the my_tfu_config.php directly.');
            WFUSettings::printTextInput($devOptions, 'To e-mail address',  'upload_notification_email', ' 	The e-mail the notification is sent to. If you leave this filed empty email notification is turned off. Please fill the from field too! The php e-mail functions are used! If no email is sent please check the e-mail settings of your php installation!');
            WFUSettings::printTextInput($devOptions, 'From e-mail address',  'upload_notification_email_from', 'The sender e-mail of the notification. You have to specify the from and to email address!');
            WFUSettings::printTextInput($devOptions, 'Notification subject',  'upload_notification_email_subject', 'The subject of the notification e-mail');
            WFUSettings::printTextInput($devOptions, 'Notification text',  'upload_notification_email_text', 'The text of the notification e-mail. There are 2 parameters available. The 1st %s is the username. The 2nd %s is the list of uploaded files. If you only want the file names use %2s.');
            echo '</table>';
            echo '<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
        }
       static function printAdvancedOptions() {
          global $current_user;
        
            echo '
<a name="adv"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Advanced Options/ profiles</h2>
<p>
In the current version the most important settings are mapped in the administration panel. The WP Flash Uploader uses the TWG Flash Uploader which has much more features that can be directly configured here. If you want to configure WFU in more detail please create a file called tfu_config_wfu.php and copy your changes to this file. On the web page of WFU a tutorial is provided how this can be easily done by everyone -> <a target="_blank" class="nounderline" href="http://www.tinywebgallery.com/blog/wfu/advanced-features/">go there</a>
</p>
<p>
<strong>You also can define individual configurations for a user, a group or a user role</strong>. This is done by a custom config file in the "wp-content/plugins/wordpress-flash-uploader/tfu" folder. Create file with the name tfu_config_&lt;user login&gt;.php for a user, tfu_config_&lt;group&gt;.php for a group or tfu_config_&lt;user role&gt;.php for a role. In this file you can overwrite any configuration that is possible in TFU. The file has to be a valid php file starting with &lt;?php and end with ?&gt;. There you can set all parameters available in tfu_config.php. If a config file for the user and a group is available the one for the user is used! If a config file for the group and a role is available the one for the group is used!<br>The default roles you have to use in Wordpress are administrator, editor, author, contributor, subscriber. So a filename for the admin user would be tfu_config_admin.php and for the role administrator tfu_config_administrator.php.
</p>
<p>
You can also insert custom Javascript you need for using the Javascript callbacks of TFU (See the howtos of TFU). Create a file called wordpress-flash-uploader.js in the main folder of the plugin (where wordpress-flash-uploader.php is located) and put your Javascript in there. A simple example for the callback after an upload is:
</p>
&nbsp;&nbsp;function uploadFinished(loc) {<br/>
&nbsp;&nbsp;&nbsp;&nbsp;alert("Done");<br/>
&nbsp;&nbsp;}<br/>
</p>
<h3>Existing custom config files</h3>
<p>
The following custom configuration files do currently exist. Please note that you can edit this files with the plugin editor of Wordpress -> Plugins -> Editor -> Select "Wordpress Flash uploader" in the upper right dropdown box:
</p>
';
$roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
$groups = WFUFlash::get_user_groups($current_user->ID);

$config_files = array();
if (file_exists(dirname(__FILE__) . "/../wordpress-flash-uploader.js")) {
$config_files[] = '- wordpress-flash-uploader.js - Javascript file that is included in the frontend where you use the wordpress short code.';
}
 
   foreach (glob(dirname(__FILE__) .'/../tfu/tfu_config_*.php') as $filename) {
    
    $base = basename($filename, '.php');
    $type = substr($base, 11);  
    $text = '';
    if ($type == 'wfu') {
       $text  = "The <strong>global</strong> custom configuration file that overrides <strong>all</strong> settings from <strong>tfu_config.php</strong> and <strong>my_tfu_config.php</strong>";
    } else  { 
        if (in_array($type, $roles)) {
           $type_str  = "role ";
        } else if ($groups !== null && in_array($type, $groups)) {
           $type_str  = "group ";
        } else if (stripos($type, "shortcode_") !== false) {
            $type_str  = "shortcode that sets the attribute configid=";
            $type = substr($type, 10);  
        } else {
            $type_str  = "user ";
        }
        $text =  'Configuration file for the '.$type_str.'"'.$type.'".';
    }  
    $config_files[] = '- tfu/' . $base . '.php - ' . $text; 
}

echo "<hr height=1>";
if (count($config_files) == 0) {
     echo "<ul><li>No custom configuration files found.</li></ul>";
} else {
  foreach ($config_files as $file) {
    echo $file . '</br>';
  }
}
echo "<hr height=1>";
}

       static function printNextVersion() {
            echo '
<a name="com"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Coming next</h2>
<div class="wfu_reg">
<p>
This version of WFU is the first release where I have implemented all main features I think which are important for Wordpress. The next few months will show which part of WFU I will extend next. You can look at <a class="nounderline" target="_blank" href="  http://jfu.tinywebgallery.com">JFU (Joomla Flash Uploader)</a> where profiles and even a user management is already implemented. Maybe some of this features are very useful for WFU too - just let me know in the <a target="_blank" class="nounderline" href="http://www.tinywebgallery.com/en/forum.php">forum</a>.</p><p>This are the next features I have already on my roadmap:
<ul>
<li>Adding more TFU configuration options to WFU</li>
<li>Support of the description mode of TFU - Captions can then be entered directly during upload</li>
<li>Internationalzation of WFU</li>
</ul>
</p>
</div>
';
        }
       static function printLicense() {
            echo '
<a name="lic"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - License</h2>
<div class="wfu_reg">
<p>
Please note that 2 licenses affect this software bundle. The WP Flash Uploader is a bridge between Wordpress and the TWG Flash Uploader. Therefore all parts that belong to the Wordpress integration (everything except the tfu folder) are distributed under the GNU GENERAL PUBLIC LICENSE version 3. 
TWG Flash Uploader is an external application that does not use any Wordpress code and is separate enough from Wordpress that it is a separate work under copyright law and is distributed under the TWG Flash Upload Freeware License Agreement. Please see license.txt in the tfu folder of the plugin for details.
</p>
<p>
Definition of a bridge:<br>
A bridge links e.g. Wordpress to an external application (the TWG Flash Uploader) so that they can exchange data and cooperate. On the e.g. Wordpress side of the bridge, the bridge is treated just like a plugin; it must comply with the GPL. If the external application is separate enough that it is a separate work under copyright law, it may be licensed under whatever license the holder of its copyright sees fit. 
 </p>
</div>
';
        }
        static function printRegisteredSettings($devOptions) {
            echo '
<a name="reg"></a>
<div id="icon-options-general" class="icon_jfu"></div>
<h2>WP Flash Uploader - Registered Options</h2>
<p>
This are the registered settings which are enabled if the registration data was entered. Please note that not all possible settings are available here. Check the description at "Advanced settings" for more detail.
</p>
<h3>Standard license</h3>
<!--
<p>
If you have a standard license then WFU does automatically enable the Javascript events. The upload and delete event is used to synchronize the media library automatically. You don\'t have to do this manually by the \'Sync Media Library\' button of WFU.
</p>
-->
<table class="form-table">
<!-- enable_file_download -->
<tr valign="top">
<th scope="row">Enable download</th>
<td>';
            echo '<input name="enable_file_download" type="radio"  value="true" ';
            if ($devOptions['enable_file_download'] == "true") { echo 'checked="checked"'; }
            echo ' /> Yes&nbsp;&nbsp;<input name="enable_file_download" type="radio" value="false" ';
            if ($devOptions['enable_file_download'] == "false") {echo 'checked="checked"'; }
            echo '/> No&nbsp;&nbsp;<input name="enable_file_download" type="radio"  value="button1" ';
            if ($devOptions['enable_file_download'] == "button1") {echo 'checked="checked"'; }
            echo '/> As upper button&nbsp;&nbsp;<input name="enable_file_download" type="radio" value="button" ';
            if ($devOptions['enable_file_download'] == "button") {echo 'checked="checked"'; }
            echo ' /> As lower button&nbsp;&nbsp;';
            echo '<br>
<em>Enables/disables the download of files. \'Yes\' shows the download option in the menu, \'No\' disables the download, \'As upper button\' shows the download button instead of the delete button and the delete button moves into the menu, \'As lower button\' shows the download button instead of the menu button - But only of all menu items are disabled (like folder functions, rename, move ...)</em>
</td>
</tr>
';
            WFUSettings::printTextInput($devOptions, 'Preview textfile extensions',  'preview_textfile_extensions', 'This are the extensions that are previewed in the flash as text files. You can restrict is to single files as well by using the full name. e.g. foldername.txt. * is supported as wildcard!.');
            WFUSettings::printTextInput($devOptions, 'Edit textfile extensions',  'edit_textfile_extensions', 'This are the extensions that can be edited in the flash. You can restrict is to single files as well by using the full name. e.g. foldername.txt. * is supported as wildcard!');
            WFUSettings::printTextInput($devOptions, 'Exclude files and directores',  'exclude_directories', 'You can enter directories and files that are hidden in WFU. Separate them by ,');
            if (function_exists('fnmatch')) {
              WFUSettings::printTextInput($devOptions, 'File filter',  'file_filter', 'You can enter a pattern for files that are hidden in WFU. The intension is to hide resized versions of a file. The default filter reads the values for thumbs, medium and large images. If you know your images you can enter a better filter! Separate them by ,. Entering "auto" does restore the original filter. Using *-*x*.* does work fine if you don\'t have files with - and a x after that in the filename.');
            } else {
              echo '<tr><td colspan="2">fnmatch is not available on this system. Therefore the enhanced file filter to hide thumbnails, middle and large images cannot be enabled. Please update to php >= 5.3 if you have a windows server.</td></tr>';
            }
            echo '
</table>
<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
            echo '
<h3>Professional license</h3>
<p>
If you have a professional license then the following features are available. Additional features are currently not directly used in this version of WFU. They can be enabled through the advanced configuration or by extending the WFU plugin.
</p>
<table class="form-table">
<!-- $enable_folder_move, $enable_file_copymove -->
<tr valign="top">
';
            WFUSettings::printTrueFalse($devOptions, 'Enable to copy and move files',  'enable_folder_move', '');
            WFUSettings::printTrueFalse($devOptions, 'Enable to move folders',  'enable_file_copymove', '');
            echo '
<tr valign="top">
<th scope="row">Additional settings</th>
<td><fieldset>
<textarea rows="3" name="swf_text" cols="50" id="swf_text">'.$devOptions['swf_text'].'</textarea><br>
<em>Additional parameters of the flash. You can add the default language here: use e.g. lang=de for German. This works without registration.<br>You can change the color of the flash here when you have a professional license or above. Please go to the help for a list of possible settings! If you e.g. want to change the text color and the background color you have to add: c_text=FF00FF&c_bg=00FF00</em></td>
</tr>
</table>
<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
        }
       static function printServerInfo()
        {
            $limit = WFUSettings::return_kbytes(ini_get('memory_limit'));
            echo '
    <a name="lim"></a>
    <div id="icon-options-general" class="icon_jfu"><br></div>
    <h2>WP Flash Uploader - Limits</h2>
    ';
            echo 'Some info\'s about your server. This limits are not TFU limits. You have to change this in the php.ini.';
            echo '<div class="install" style="margin-left:50px">';
            echo '<table><tr><td>';
            echo '<tr><td width="400">TFU version:</td><td width="250">2.16&nbsp;';
            // simply output the license type by checking the strings in the license. No real check like in the flash is done here.
            if (file_exists(dirname(__FILE__) . "/../tfu/twg.lic.php")) {
                include  dirname(__FILE__) . "/../tfu/twg.lic.php";
                if (isset($l)) {
                    if ($l == $d) {
                        echo " (Enterprise Edition License)";
                    } else if (strpos($d, "TWG_PROFESSIONAL") !== false) {
                        echo " (Professional Edition License)";
                    } else if (strpos($d, "TWG_SOURCE") !== false) {
                        echo " (Source code Edition License)";
                    } else {
                        echo " (Standart Edition License)";
                    }
                } else {
                    echo " (No valid License)";
                }
            } else {
                echo " (Freeware Edition)";
            }
            echo  '</td></tr>';
            echo '<tr><td width="400">Server name:</td><td width="250">' . WFUSettings::get_server_name() . '</td></tr>';
            echo '<tr><td>PHP upload limit (in KB): </td><td>' . WFUSettings::getMaximumUploadSize() . '</td></tr>';
            echo '<tr><td>PHP memory limit (in KB):&nbsp;&nbsp;&nbsp;</td><td>' . $limit . '</td></tr>';
            echo '<tr><td>Safe mode:</td><td>';
            echo (ini_get('safe_mode') == 1) ? 'ON<br>You maybe have some limitations creating folders or uploading<br>if the permissions are not set properly.<br>Please check the TWG FAQ 30 if you want to know more about<br>safe mode and the problems that comes with this setting.' : 'OFF';
            echo '</td></tr><tr><td>GD lib:</td><td>';
            echo (!function_exists('imagecreatetruecolor')) ? '<font color="red">GDlib is not installed properly.<br>TFU Preview does not work!</font>' : 'Available';
            echo '</td></tr>';
            echo '<tr><td>Max resize resolution (GDlib):</td><td>';
            if (!$limit) {
                echo '<font color="green">No limit</font>';
            } else {
                $xy = $limit * 1024 / 6.6;
                $x = floor(sqrt ($xy / 0.75));
                $y = floor(sqrt($xy / 1.33));
                if ($x > 4000) {
                    echo '<font color="green">~ ' . $x . ' x ' . $y . '</font>';
                } else if ($x > 2000) {
                    echo '<font color="orange">~ ' . $x . ' x ' . $y . '</font>';
                } else {
                    echo '<font color="red">~ ' . $x . ' x ' . $y . '</font>';
                }
            }
            echo '</td></tr>';
            echo '<tr><td><br>The times below have to be longer than the maximum<br>upload duration! Otherwise the upload will fail.<br>&nbsp;</td><td>&nbsp;</td></tr>';
            echo '<tr><td>PHP maximum execution time: </td><td>' . ini_get('max_execution_time') . ' s</td></tr>';
            echo '<tr><td>PHP maximum input time: </td><td>' . ini_get('max_input_time') . ' s</td></tr>';
            echo '<tr><td>PHP default socket timeout: </td><td>' . ini_get('default_socket_timeout') . ' s</td></tr>';
            echo '</table>';
            echo '</div>';
        }
        static function printRegistration($devOptions) {
            echo '
<a name="don"></a>
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - Donation / Registration</h2>';
            if (!file_exists(dirname(__FILE__) . "/../tfu/twg.lic.php")) {
                echo '
<div class="wfu_reg">
<h3>Donate</h3>
<table><tr><td>You like the plugins? Support the development with a small donation </td><td>&nbsp;&nbsp;&nbsp;<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40mdempfle%2ede&item_name=WP%20Flash%20Uploader&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=EN&bn=PP%2dDonationsBF&charset=UTF%2d8"><img src="../wp-content/plugins/wordpress-flash-uploader/img/btn_donate_LG.gif"></A></td></tr></table>
</p>
<table class="form-table">
';
                WFUSettings::printTrueFalse($devOptions, 'Hide the donate button',  'hide_donate', 'I don\'t want to bother you with the donate logo inside the plugins itself. Feel free to turn it off.');
                echo '</table>
&nbsp;&nbsp;<input type="submit" class="button-primary" name="update_WFUSettings" value="';
                echo _e('Update Setting', 'WFU');
                echo '" />';
                echo '
<h3>Registration</h3>
<p>WFU is the wrapper of the TWG Flash Uploader. For most users the limitations of the freeware version of the TWG Flash Uploader should not be a problem. But if, you can register the flash for a small fee. The registration does also include TinyWebGallery, TWG Flash Uploader and Joomla Flash Uploader!</p>
There are 2 versions of WFU available:<br><ul>
  <li>Freeware: Has almost everything you need. The main restriction is the 3 MB limit of the upload queue. Please go to <a href="http://www.tinywebgallery.com/en/tfu/web_overview.php">www.tinywebgallery.com</a> for a full list of all the features.</li><li>Registered: See below for the extras you get.</li></ul>
      <div class="install" style="width: 600px; margin-left: 50px;">If you register WFU you get the following extra features: <br><ul>  <li>Unlimited version of the TWG Flash Uploader. No 3 MB limit anymore! </li> <li style="margin-top: 3px;">Registration of TinyWebGallery, TWG Flash Uploader, Joomla Flash Uploader</li> <li style="margin-top: 3px;">Download of files </li>  <li style="margin-top: 3px;">View and Edit of text files </li>  <li style="margin-top: 3px;">Javascript events</li>  <li style="margin-top: 3px;">Titel and some text labels can be changed by configuration. *</li>  <li style="margin-top: 3px;">Limit the number of files that can be uploaded to a directory of the server *</li>  <li style="margin-top: 3px;"><strong>Professional license and above:</strong>  <ul>  <li style="margin-top: 3px;">The colors of the flash can be configured.</li>  <li style="margin-top: 3px;">Copy/move files and move folders</li>  <li style="margin-top: 3px;">Description mode.<!-- A caption can be entered directly for each file.--> *</li>  <li style="margin-top: 3px;">Completely anonymous flash: The ? can be turned off</li><li style="margin-top: 3px;">Reading of html form fields.</li><li style="margin-top: 3px;">Big progress bar. Go <a href="http://www.tinywebgallery.com/en/tfu/web_progressbar.php" target="_blank">here</a> for additional progress bars.</li> </ul>  </li>  </ul></div>
      <div class="howto">* This features are currently not directly used in this version of WFU. They can be enabled through the advanced configuration or by extending the WFU plugin. Depending on the feature requests I will add direct support of them in one of the next updates.</div>
';
                echo '
<p>      
The registration is free (powered by trialpay) or only <b>15 €/domain</b> and can be done on www.tinywebgallery.com by clicking <a href="http://www.tinywebgallery.com/en/register_tfu.php"><b>here</b></a>.<br>The registration of the TWG Flash Uploader, JFU and WFU is the same. The registration is also valid for TWG and the standalone version of TFU!<p>To register please store the content of the 3 lines provided in the registration email in the text boxes below and press the register button. If everything worked fine you get a different message here and in the options of the registered version are enabled - and of course the 3MB limit is gone.</p>
<div class="install" style="width: 650px; margin-left: 50px;">
&lt;?php
<table><tbody><tr><td style="text-align:left;">
$l&nbsp;=" <input name="l" size="65" type="text"> ";</td></tr><tr><td style="text-align:left;">
$d=" <input name="d" size="65" type="text"> ";</td></tr><tr><td style="text-align:left;">
$s=" <input name="s" size="65" type="text"> ";</td></tr></tbody></table>
?&gt;
<div class="submit" style="padding:0px;padding-left:60px;">
<input type="submit" class="button-primary" name="register_WFU" value="';
                echo _e('Register', 'WFU');
                echo '" /></div>
</div>
</div>
';
            } else {
                echo '
<p>This is a registered copy of WP Flash Uploader. Please go to Meadia -> WP Flash Uploader to check if the registration was successful. You will get an error message if it is not the case. If you don\'t get an error message you can click on the ? to see the status. If the ? is not there anymore the registration was successful too.</p><p>If you want to change or enter a different license data please click on the button below.</p>
<div class="submit" style="padding:0px;padding-left:60px;">
<input type="submit" class="button-primary" name="unregister_WFU" value="';
                echo _e('Delete Registration file', 'WFU');
                echo '" /><br> <br></div>';
            }
        }
/**
 *  will come in the next version!
 */
       static function printSystemCheck() {
            echo '
<div id="icon-options-general" class="icon_jfu"><br></div>
<h2>WP Flash Uploader - System Check</h2>
<p>
Below you find the results of some test WFU is performing if you can upload properly and the solutions/workarounds if something is not like it should be.
</p>';
            echo "Upload directory: <br>";
            echo "Upload directory exists: <br>";
            echo "Upload directory writeable: <br>";
            echo "Sub directories in the upload directory can be created <br>";
        }
       static function return_kbytes($val)
        {
            $val = trim($val);
            if (strlen($val) == 0) {
                return 0;
            }
            
            $last = strtolower($val[strlen($val)-1]);
            switch ($last) {
                case 'g':
                    $val *= 1024 * 1024;
                    break;
                case 'm':
                    $val *= 1024;
                    break;
            }
            return $val;
        }
       static function get_server_name() {
            if(isset($_SERVER['HTTP_HOST'])) {
                $domain = $_SERVER['HTTP_HOST'];
            } else if(isset($_SERVER['SERVER_NAME'])) {
                $domain = $_SERVER['SERVER_NAME'];
            } else {
                $domain = '';
            }
            $port = strpos($domain, ':');
            if ( $port !== false ) $domain = substr($domain, 0, $port);
            return $domain;
        }
       static function getMaximumUploadSize()
        {
            $upload_max = WFUSettings::return_kbytes(ini_get('upload_max_filesize'));
            $post_max = WFUSettings::return_kbytes(ini_get('post_max_size'));
            return $upload_max < $post_max ? $upload_max : $post_max;
        }
       static function printTrueFalse($options, $label,  $id, $description) {
            echo '
<tr valign="top">
<th scope="row">'.$label.'</th>
<td>
';
            echo '<input type="radio" id="'.$id.'" name="'.$id.'" value="true" ';
            if ($options[$id] == "true") { echo 'checked="checked"'; }
            echo ' /> Yes&nbsp;&nbsp;<input type="radio" id="'.$id.'" name="'.$id.'" value="false" ';
            if ($options[$id] == "false") {echo 'checked="checked"'; }
            echo '/> No<br>
<em>'.$description.'</em></td>
</tr>
';
        }
        
        
        
        
        static function printSyncSettings($devOptions) {
            echo '
<a name="reg"></a>
<div id="icon-options-general" class="icon_jfu"></div>
<h2>Sync media library options</h2>
<p>
You can automatically sync the media library in a given interval. It is also possible to define the file extensions that should be synched
</p>
<table class="form-table">
<!-- enable_file_download -->';
            WFUSettings::printTrueFalse($devOptions, 'Sync automatically after upload',  'enable_auto_sync', 'If you upload files with the WP FLash uploader you can enable/disable that the files are automatically added to the media library. Depending on your setup the syncronisation might not work as you expect. Please disable the automatic sync then and use a different plugin for the synchronisation.');

            WFUSettings::printTrueFalse($devOptions, 'Show backup warning',  'sync_warning_message', 'The sync process does synchronize your upload folder with the media library. It does create thumbnails that do not exist yet and fix invalid database entries. Every server is different and every wordpress version as well. The sync has been tested carefully with the most common settings. But your settings are maybe different! Some make a backup when you use the synch the first time! I recommend to make a backup of your database and your upload folder!');
echo '
<tr valign="top">
<th scope="row">Sync automatically by cronjob</th>
<td>';
            echo '<select name="scheduler">';
            WFUSettings::printOptionLine('none', 'No automatic sync', $devOptions);            
            WFUSettings::printOptionLine('every_min', 'Every minute', $devOptions);
            WFUSettings::printOptionLine('every_5_min', 'Every 5 minutes', $devOptions);
            WFUSettings::printOptionLine('every_10_min', 'Every 10 minutes', $devOptions);
            WFUSettings::printOptionLine('every_30_min', 'Every 30 minutes', $devOptions);
            WFUSettings::printOptionLine('hourly', 'Every hour', $devOptions);
            WFUSettings::printOptionLine('daily', 'Once a day', $devOptions);
            echo '<select>';
            echo '<br>
<em>Please set the time interval the upload folder should be checked and syncronized with the media library. You have to set <b>"define(\'ALTERNATE_WP_CRON\', true);"</b> in the wp-config.php to enable the cron job! Please note that this is not a realy cron job. So if you set 5 minutes then it is syncronized at the next request that happens after 5 minutes waiting!</em>
</td>
</tr>
';
            WFUSettings::printTextInput($devOptions, 'Sync extensions',  'sync_extensions', 'You can define the extensions that should be synchronized. If you leave the field empty an import of all files is tried! Please separate the extensions with "," .');
            WFUSettings::printTrueFalse($devOptions, 'Try to detect resized files',  'detect_resized', 'Resized files should normally not imported again. The plugin tries to detect this files and does not offer them on the \'Sync\' menu entry if you set this to true. If you set it to false all files are synchronized.');
            if ($devOptions['sync_time'] != '0' && $devOptions['sync_time'] != '') {
              $time = intval($devOptions['sync_time']);
              // Both settings should do the same! Only works with safemode off!
              @set_time_limit($time);
              @ini_set('max_execution_time', $time);
            }
            WFUSettings::printTextInput($devOptions, 'PHP time limit',  'sync_time', 'This sets the maximum execution time of the script. See <a target="_blank" href="http://php.net/manual/en/function.set-time-limit.php">http://php.net/manual/en/function.set-time-limit.php</a>. On most systems this is set to a default of 30 seconds. For big syncs this is not enough and you can increase this time. But not all servers do allow this. By leaving this field empty or by entering 0 nothing is done and the default of the server is used.<br />This only works with <strong>safe mode off</strong>. The current time returned from the system with the settings above is <strong>' .  ini_get('max_execution_time') . "s</strong>. If the time is NOT equals your setting than the time cannot be set. Please turn off safe mode." );
            WFUSettings::printTextInput($devOptions, 'Maximum files to sync in one request',  'synch_max_files', 'You can define how many images are synched in one request. If you are not able to increase the PHP time limit then syncs will fail after this time. If you enter a number here then after this number an automatic refresh of this command will happen and the next set of files will be processed. You can also enter "auto" here what is the default. The synch will measure the time each file will take to process and refresh before the limit is reached. auto is not 100% save because it does only count real time. Php execution time is the time the script gets really on the cpu. So auto does reload most likely more often than needed. Increase the php limit or use a number if auto does not work');            
            WFUSettings::printTrueFalse($devOptions, 'Show remove invalid media entries button',  'remove_invalid', 'You can also show a button that does remove invalid database entries. Please make a backup of your database before you enable this feature as some users report problems on some Wpordpress versions.');
           
           
           
            echo '
</table>
<div class="submit">
<input type="submit" class="button-primary" name="update_WFUSettings" value="';
            echo _e('Update Settings', 'WFU');
            echo '" /></div>';
        }

function printLoginId($options, $label,  $id, $description) {
            echo '
<tr valign="top">
<th scope="row">'.$label.'</th>
<td>
';
            echo '<input type="radio" id="'.$id.'" name="'.$id.'" value="master_profile_type_username" ';
            if ($options[$id] == "master_profile_type_username") { echo 'checked="checked"'; }
            echo ' /> Username&nbsp;&nbsp;<input type="radio" id="'.$id.'" name="'.$id.'" value="master_profile_type_display" ';
            if ($options[$id] == "master_profile_type_display") {echo 'checked="checked"'; }
            echo '/> Display name&nbsp;<input type="radio" id="'.$id.'" name="'.$id.'" value="master_profile_type_id" ';
            if ($options[$id] == "master_profile_type_id") {echo 'checked="checked"'; }
            echo '/> Id&nbsp;<input type="radio" id="'.$id.'" name="'.$id.'" value="master_profile_type_id_name" ';
            if ($options[$id] == "master_profile_type_id_name") {echo 'checked="checked"'; }
            echo '/> Id + last name + first name&nbsp;<input type="radio" id="'.$id.'" name="'.$id.'" value="master_profile_type_ip" ';
            if ($options[$id] == "master_profile_type_ip") {echo 'checked="checked"'; }
            echo '/> IP<br>
<em>'.$description.'</em></td>
</tr>
';
        }
       static function printTextInput($options, $label,  $id, $description) {
            echo '
<tr valign="top">
<th scope="row">'.$label.'</th>
<td>
<input name="'.$id.'" type="text" size="50" id="'.$id.'" value="'.$options[$id].'"  /><br>
<em>'.$description.'</em></td>
</tr>
';
        }
/*
*   Static right now - will be dynamic later on.
*/
       static function getAvailableTFULanguages() {
            return 'de,en,es,br,cn,ct,da,fr,it,jp,nl,no,pl,pt,se,sk,tw';
        }
       static function check_image_magic($image_magic_path) {
            $inputimage = dirname(__FILE__) . "/../tfu/lang/de.gif";
            // now we check if we can do the test in the upload directoy           
            $wp_upload_dir_array = wp_upload_dir();
            $folder = $wp_upload_dir_array['path'];
            if (!is_writeable($folder)) {
                return '<span id="im_test"><img src="' . plugins_url() . '/wordpress-flash-uploader/img/maybe.jpg"> Image magick test cannot be performed because the folder "'.$folder.'" is not writeable. You can enable the setting and try.</span>';
            }
            $outputcachetest = $folder . "/_image_magick_test.jpg";
            $fh=fopen($outputcachetest,'w');
            fclose($fh);
            $command = $image_magic_path. " \"" .  realpath($inputimage) . "\" -quality 80 -resize 120x81  \"" . realpath($outputcachetest) . "\"";
            $command_esc = escapeshellcmd($command);
            ob_start();
            if (substr(@php_uname(), 0, 7) == "Windows"){
                // Make a new instance of the COM object
                $WshShell = new COM("WScript.Shell");
                // Make the command window but dont show it.
                $oExec = $WshShell->Run("cmd /C " . $command_esc, 0, true);
            } else {
                exec($command_esc . " > /dev/null");
            }
            ob_end_clean();
  
            if (file_exists($outputcachetest) &&  filesize($outputcachetest) > 0) {
                $ok = true;
                @unlink($outputcachetest);
                return '<span id="im_test"><img src="'.admin_url().'/images/yes.png"> Image magick support is available</span>';
            } else {
                @unlink($outputcachetest);
                return '<span id="im_test"><img src="'.admin_url().'/images/no.png"> Image magick is not available. Please check the next setting.</span>';
            }
        }
          
        static function printOptionLine($value, $text, $devOptions) {
           $selected = '';
           if ($devOptions['scheduler'] == $value) {
              $selected = ' selected="selected" ';
           } 
           echo '<option '.$selected.' value="'.$value.'">&nbsp;' .__($text). '&nbsp;</option>';
         }  
    }
}
?>