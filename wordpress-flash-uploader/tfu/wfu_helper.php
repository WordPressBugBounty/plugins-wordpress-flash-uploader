<?php
/**
 * TWG Flash uploader 2.16.x
 *
 * Copyright (c) 2004-2012 TinyWebGallery
 * written by Michael Dempfle
 *
 *
 *        This file has all the helper functions.
 *        Normally you don't have to modify anything here.
 *        Only the timezone can be interesting for you: $timezone
 */
/**
 * * ensure this file is being included by a parent file
 */
defined('_VALID_TWG') or die('Direct Access to this location is not allowed.');
$session_double_fix = false; // this is only needed if you get errors because of corrupt sessions. If you turn this on a backup is made and checked if the first one is corrupt

/**
 * This stores all data in a session in a temporary folder as well if it does exist.
 * This is a workaround if a session is lost and empty in the tfu_upload.php and restored there!
 */
function store_temp_session()
{
    global $session_double_fix;
    clearstatcache();
    if (file_exists(dirname(__FILE__) . '/session_cache') && session_id() != "") { // we do your own small session handling
        $cachename = dirname(__FILE__) . '/session_cache/' . session_id();
        $ser_file = fopen($cachename, 'w');
        fwrite($ser_file, serialize($_SESSION));
        fclose($ser_file);
        if ($session_double_fix) {
            $ser_file = fopen($cachename . '2', 'w');
            fwrite($ser_file, serialize($_SESSION));
            fclose($ser_file);
        }
    }
}
?>