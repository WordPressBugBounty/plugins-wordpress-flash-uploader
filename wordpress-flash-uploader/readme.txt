=== Wordpress Flash Uploader ===
Contributors: mdempfle, Michael Dempfle
Tags: upload, admin, media, synchronize, flash, ftp, media library, sync, uploader, images, gallery, image upload, image preview
Requires at least: 2.7
Tested up to: 6.0.1
Stable tag: 3.4.3
Donate link: Please check the settings of Wordpress Flash Uploader

'Wordpress Flash Uploader' is a replacement of the internal flash uploader which let you also manage your whole Wordpress installation and synchronize your media library. 

== Description ==

'Wordpress Flash Uploader' is a flash uploader that replaces the existing flash uploader and let you manage your whole Wordpress installation. You can also include the flash to your site and enable your users to upload/manage files based on user names, groups or roles. 
The Wordpress Flash Uploader does contain 2 plugins: 'Wordpress Flash Uploader' and 'Sync Media Library'. 
'Sync Media Library' is a plugin which allows you to synchronize the Wordpress database with your 
upload folder. You can upload by WFU, FTP or whatever and import this files to the Media Library.

= Features =
* Support of all features of TFU: http://www.tinywebgallery.com/en/tfu/web_overview.php 
* Add the flash uploader to the site! You can define different profiles for users, groups and roles! So you can define exactly who can do/upload what and where on the server! See the frontend settings for details. 
* Manage your Wordpress installation with WFU.
* Synchronize the upload folder with the media library. 
* Synchronize the media library automatically using the wordpress cron 
* Define the extensions that should be synchronized.


= Motivation: =
Wordpress has a flash uploader which was not working on any of my servers. So I decided to write a 
wrapper for the TWG Flash Uploader which works on most servers so far. 
WordPress 2.5+ includes a new Media manager, However, it only knows about files which have been uploaded 
via the WordPress interface, not files which have been uploaded via other means (eg, FTP or WFU).
So I had to implement something that does the synchronisation.
The final result are actually two plugins in one.  The 'Wordpress Flash Uploader - WFU' and the 
'Sync Media Library'. WFU is the wrapper for the TWG Flash Uploader and with 'Sync Media Library' 
you can syncronize the upload folder with the Wordpress database. I implemented this as seperate 
menu items because maybe you want to upload your files with FTP and you can syncronize your 
files without using WFU. 

Have fun using WFU,
Michael

== Frequently Asked Questions ==

FAQ / Help: http://www.tinywebgallery.com/blog/wfu/wfu-faq/

== Website / Help / Forum ==

Website:    http://www.tinywebgallery.com/blog/wfu/
Forum:      http://www.tinywebgallery.com/en/forum.php

== Installation ==
Please make a backup of your images when you use the synch the first time!
On some systems Wordpress does remove the images once.
If you have this problem please contact me to fix this because it is not reproduceable on my systems.

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the plugin from plugins page.
You get 'WP Flash Uploader' and 'Sync Media Library' in the 'Media' menu and 'WP Flash Uploader' in the 'Settings' menu. 

Go to Settings->WP Flash Uploader and check the 'Limitations' part. Most of the users can upload and are 
only restricted by their servers. And most if the problems can be solved!

If you want to use the automatic sync please add   
define('ALTERNATE_WP_CRON', true);
in the wp-config.php 
to enable the cron job! 
Please note that this is not a real cron job. So if you set 5 minutes then it is syncronized at the next request that happens after 5 minutes waiting!

Please make a backup of your images when you use the synch the first time!
On some systems Wordpress does remove the images once.
If you have this problem please contact me to fix this because it is not reproduceable on my systems.

== Screenshots ==
1. The Wordpress Flash Uploader page where you can upload images
2. The Synch Media Library page where you can synchronize your upload folder with the Media Library
3. The Wordpress Flash Uploader included in the site directly with the shortapi/shortcode

== Donation ==

Please go to the settings page of Wordpress Flash Uploader. There you find a small donation section. Thank you for your support.

== Changelog ==
= 3.4.3 =
* Fix: Fixed loading of Wordpress when php 8.1 is used

= 3.4.2 =
* New: Support of php 7
* New: Included version 3.2.2 of TFU.
* New: Added support for Wordpress 4.8.1

= 3.4.1 =
* New: Included version 3.2.1 of TFU. 
* New: Added support for Wordpress 4.7.4

= 3.4 =
* New: execute_command in wfu-settings are directly included into the needed functions that it cannot be called anymore. Also escapeshellcmd is used to secure the call
* New: escapeshellcmd is used in execute_command for the flash uploader part as well to increase security. 
* New: path functions from wp are used now where possible
* New: upload folder usage was rewritten. Now wp functions are used
* New: $mail->SMTPSecure support

= 3.3.5 = 
* New: Removed phpmailer from this plugin and using the one that comes with Wordpress
* New: Added support for Wordpress 4.7.2

= 3.3.4 = 
* New: phpmailer updated to 5.2.22

= 3.3.3 = 
* New: Added support for Wordpress 4.7.1
 
= 3.3.2 = 
* New: Added support for Wordpress 4.5
* Fix: Replaced depricated get_currentuserinfo() with wp_get_current_user()

= 3.3.1 =
* New: New master profile id_displayname

= 3.3 =
* New: Full profile support in the frontend! All frontend options can be set now in the shortcode and each shortcode can have a different profile! 
* New: css is only loaded in the administration if needed
* New: Sync can be disabled - "Sync automatically after upload"
* New: Autodetection of thumbnails was improved.
* New: Remove invalid entries can be enabled. 
* Fix: All sizes are now detected
* Fix: Session is only started when not started already

= 3.2.1 =
* New: Tested with Wordpress 4.1

= 3.2 =
* New: TFU 3.2 is included
* Fix: Removal of invalid media entries are disabled right now as it seems it causes problems on some systems. Please still make a backup of you install before useing this plugin.
* Fix: Small layout fixes

= 3.1.3 =
* Fix: Invalid characters are removed from the image_magic_path to avoid that harmfull commands can be entered. 
 
= 3.1.2 =
* New: Support of ip using master profile mode

= 3.1.1 =
* Fix: Test data removed from the language file

= 3.1 =
* New: TFU 3.1 is included
* New: You can define how much images are processed at once. After that a automatic reload is done. This way you get around any timeout limits of php
* New: Optimization and caching of the detection of the files to upload. Is now ~ 25 (!) times faster than before
* New: Renamed files are now checked in the upload detection.
* New: Image types Homepage, Slideshow, Sidebar are added to the detection.
* New: A message that you should backup your upload folder when you use WFU the first time was added. 
* Fix: Import of gifs has been checked. 
* Fix: Frontend message that the language folder could not be found fixed (by using TFU 3.1)

= 2.16.5 =
* Fix: check if mb_strtolower is available. if not strtolower is used.
* fix: Hotfix for Wordpress >= 3.4: update_attached_file is now used.

= 2.16.4 =
* New: set_time_limit can now be configured in the administration. Please note that this only works with save mode off.

= 2.16.3 =
* New: Added set_time_limit(600); to the sync part. So if your server does allow this the time limit is set to 10 minutes instead using the default of 30.
 
= 2.16.2 =
* Fix: An error message was shown when the plugin contexture-page-security was not available. This plugin enables to use groups in WP and this groups can be used in WFU to create profiles!
* Fix: Styles adopted for WP 3.4.1

= 2.16.1 =
* Fix: Blank screens in combination with some cache plugins fixed. I included the tfu_helper file where I was using only one function from. Including only this one function on an extra file solved the problem on 2 test installs.

= 2.16 =
* Updated to TFU 2.16.
* Support for a global custom configuration file called tfu_config_wfu.php. Please store your global custom settings here. This file is not overwritten when you update WFU.
* Support for custom configuration files for groups. So now there is support for users, groups and roles. Please see the advanced section in the settings of WFU.
* Javascript can be added to the site - use wordpress-flash-uploader.js in the wfu plugin folder.
* Custom configurations files are now listed in the advanced section. 
* The height of the flash is set in an extra div so that the page does not jump after the flash is loaded.
* Help updated - it describes now the usage of additional Javascript and the custom configs better.
* Fix: The session cache workaround does now also work in Wordpress now. Using it caused that only the first settings where used (e.g. switching to the wordpress view was not working)   
* Fix: Fixed that flash was not shown when color settings where added in the free text field.
* Fix: In the front end the language flags are loaded.
* Fix: In the front end the big progress bars are loaded

= 2.15.2 =
* Added some debug outputs to be able to find problems during synchronize.

= 2.15.1 =
* Updated TFU 2.15. Language files where updated and Serbian language file was added.

= 2.15 =
* New configuration options for the 'Sync media library'. You can define which file extensions should be syncronized.
* You can enable automatically sync which is executed as cron job in Wordpress. I have added several cron job times as well. You have to set "define('ALTERNATE_WP_CRON', true);" in wp-config.php to enable the cron jobs.
* Running upload detection: The file size is read twice during the process. If the filesize changes then the file is still uploading and not synchronized!
* Updated the flash to TFU 2.15

= 2.14.5 =
* The $width variable was used to early and therefore not setting a flash variable. Now the notice is gone and the flash variable set correct.

= 2.14.4 =
* Added @ to avoid notices when unserializable is called. 

= 2.14.3 =
* Individual profiles for users and roles can be created. So now you have the full flexibility of TFU. Please read the advanced section for details.
* Updated the flash to TFU 2.14.3

= 2.14.2 =
* Updated the flash to TFU 2.14.2

= 2.14.1 =
* Updated the flash to TFU 2.14.1

= 2.14 =
* Updated the flash to TFU 2.14
* Added Master mode support - This means that each user gets his own directory automatically.
* Added chmod for creating directories.

= 2.13.1 =
* Updated the flash to TFU 2.13
* ini_set removed because so server do not allow this.
 
= 2.13 =
* Updated the flash to TFU 2.13
* Enhanced security because wp_nonce is now used everywhere
* Direct call of the tfu_login.php does not show any servers info anymore to avoid server info disclosure.
* Config variables are sent encrypted now. Avoids config info disclosure.

= 2.12.2 =
* Fix for permalinks. Enabling this was not showing the flash in the frontend because relative dirs where used. Now the absolute path is used.

= 2.12.1 =
* The flash can now be included into the site by [wfu securitykey=<see settings>] - See the settings page for details and the new screenshot.
* Updated the flash to TFU 2.12.1

= 2.12 =
 * Updated the flash to TFU 2.12
 * Now tested up to Wordpress 3.0.3!
 * Tabs in the media library popup are now available again.
 * Sync is now triggered by the uploader automatically in the free version!
 * Delete of files in the flash does now sync the library as well.  
 * Enhanced file filter for registered users to hide generated thumbnails, middle and large images.  
   
= Update for Wordpress 3.0 =
 * Now compatible up to Wordpress 3.0!
 * The tabs in the image popup are not working yet. The wordpress team has changed something I have to find out how to do this now!   

= 2.11 =
 * Now compatible up to Wordpress 2.9.2!
 * Updated the flash to TFU 2.11
 * Changed the name from 'WP Flash Uploader' to 'Wordpress Flash Uploader' because the old one could not be found on wordpress.org anymore and they did not fix it. 

= 2.10.7 = 
 * Now compatible up to Wordpress 2.9.1!
 * Updated the flash to TFU 2.10.7
 * Wrong message was shown after importing only one image.
 * Improved the normalizeFileNames function
 * The Uploader and the Sync option can now be activated independently in the menu and the media manager tabs
 * The 'Sync' has now a detection of already crunched images of an original. This can be turned off in the settings because the detection is very basic.   
     
= 2.9.1 =
 * No further problems found. First official release of WFU

= 2.9.1 RC 2 =
 * Updated the Flash to TFU 2.9.1.1
 
= 2.9.1 RC 1 =
 * Initial Release - The version does start with 2.9.1 because it is based on TFU 2.9.1
 * This version contains the first basic wrapper for Wordpress. Much more is possible with the TWG Flash Uploader. 
 * No problems found in Beta 1 - Therefore the version is released as RC 1 now.
 *     
Please don't hesitate to post your requirements