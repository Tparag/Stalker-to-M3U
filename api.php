<?php

include("_inc.configs.php");

$action = ""; if(isset($_REQUEST['action'])){ $action = trim($_REQUEST['action']); }

//=====================================//

if($action == "getChannels")
{
    if(empty(mac_serverurl())){ response("error", 503, "Application is not Configured", ""); }
    $live = array(); 
    $tv_list = getChannels();
    
    // Get genres for category mapping
    $genres = mac_getGenres();
    
    // Get genre filter
    $genre_filter = app_genre_filter("get");
    
    foreach($tv_list as $etv) {
        $category_title = 'Uncategorized';
        
        // Map category using tv_genre_id
        $genre_id = '0';
        if(isset($etv['tv_genre_id']) && !empty($etv['tv_genre_id']) && $etv['tv_genre_id'] !== '0') {
            $genre_id = (string)$etv['tv_genre_id'];
            if(isset($genres[$genre_id])) {
                $category_title = $genres[$genre_id];
            }
        }
        
        // Apply Genre Filter if configured
        if(!empty($genre_filter)) {
            if(!in_array($genre_id, $genre_filter)) {
                continue; // Skip this channel
            }
        }
        
        $live[] = array(
            "id" => $etv['id'], 
            "title" => $etv['title'], 
            "logo" => fixlogoissue($etv['logo']),
            "category_title" => $category_title,
            "tv_genre_id" => $genre_id
        );
    }
    $count_live = count($live);
    response('success', 200, $count_live.' TV Channels Found', array('count' => $count_live, 'list' => $live));
}
elseif($action == "getPlaybackDetails")
{
    if(empty(mac_serverurl())){ response("error", 503, "Application is not Configured", ""); }
}
elseif($action == "get_iptvplaylist")
{
    $livetv = getChannels();
    if(isset($livetv[0]))
    {
        // Get genre filter
        $genre_filter = app_genre_filter("get");
        
        $icdata = '#EXTM3U'."\n";
        $e = 0;
        foreach($livetv as $itv)
        {
            // Apply Genre Filter
            if(!empty($genre_filter)) {
                $genre_id = isset($itv['tv_genre_id']) ? (string)$itv['tv_genre_id'] : '0';
                if(!in_array($genre_id, $genre_filter)) { continue; }
            }
            
            $e++;
            $icdata .= '#EXTINF:-1 tvg-id="'.$e.'" tvg-name="'.$itv['title'].'" tvg-logo="'.fixlogoissue($itv['logo']).'" group-title="'.$APP_CONFIG['APP_NAME'].'",'.$itv['title']."\n";
            $icdata .= $streamenvproto."://".$plhoth.str_replace(" ", "%20", str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']))."live.php?id=".$itv['id']."\n";
        }
        $file = cleanString($APP_CONFIG['APP_NAME'])."_" . time() . "_".cleanString($APP_CONFIG['APP_NAME']).".m3u";
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header("Content-Type: application/vnd.apple.mpegurl");
        exit(trim($icdata));
    }
    http_response_code(503);
    exit();
}
elseif($action == "get_iptvplaylist_categorized")
{
    $livetv = getChannels();
    if(isset($livetv[0]))
    {
        $genres = mac_getGenres();
        $genre_filter = app_genre_filter("get");
        
        $icdata = '#EXTM3U'."\n";
        $icdata .= '#PLAYLIST: '.$APP_CONFIG['APP_NAME']."\n";
        
        $channels_by_category = array();
        foreach($livetv as $itv) {
            $cat_title = 'Uncategorized';
            $genre_id = '0';
            
            if(isset($itv['tv_genre_id']) && !empty($itv['tv_genre_id']) && $itv['tv_genre_id'] !== '0') {
                $genre_id = (string)$itv['tv_genre_id'];
                if(isset($genres[$genre_id])) {
                    $cat_title = $genres[$genre_id];
                }
            }
            
            // Apply Genre Filter
            if(!empty($genre_filter)) {
                if(!in_array($genre_id, $genre_filter)) { continue; }
            }
            
            if(!isset($channels_by_category[$cat_title])) {
                $channels_by_category[$cat_title] = array();
            }
            $channels_by_category[$cat_title][] = $itv;
        }
        
        foreach($channels_by_category as $cat_title => $channels) {
            $icdata .= "\n".'#EXTGRP:'.$cat_title."\n";
            
            foreach($channels as $itv) {
                $tvg_id = preg_replace('/[^a-zA-Z0-9]/', '', $itv['title']);
                $tvg_logo = fixlogoissue($itv['logo']);
                $stream_url = $streamenvproto."://".$plhoth.str_replace(" ", "%20", str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']))."live.php?id=".$itv['id'];
                
                $icdata .= '#EXTINF:-1 tvg-id="'.$tvg_id.'" tvg-name="'.$itv['title'].'" tvg-logo="'.$tvg_logo.'" group-title="'.$cat_title.'",'.$itv['title']."\n";
                $icdata .= $stream_url."\n";
            }
        }
        
        $file = cleanString($APP_CONFIG['APP_NAME'])."_categorized_" . time() . ".m3u";
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header("Content-Type: application/vnd.apple.mpegurl");
        exit(trim($icdata));
    }
    http_response_code(503);
    exit();
}
elseif($action == "get_genres")
{
    if(empty(mac_serverurl())){ response("error", 503, "Application is not Configured", ""); }
    
    $genres = mac_getGenres();
    
    response('success', 200, count($genres).' Genres Found', array('count' => count($genres), 'list' => $genres));
}
elseif($action == "get_categories")
{
    if(empty(mac_serverurl())){ response("error", 503, "Application is not Configured", ""); }
    
    $categories = mac_getCategories();
    
    response('success', 200, count($categories).' Categories Found', array('count' => count($categories), 'list' => $categories));
}
elseif($action == "force_update_channels")
{
    session_start();
    $_SESSION['yuvisession'] = true;
    
    if(empty(mac_serverurl())){ response("error", 503, "Stalker Portal details are not configured", ""); }
    
    $channels = mac_forceUpdateChannels();
    
    if(!empty($channels)) {
        response("success", 200, "Channels updated successfully. Total: ".count($channels), "");
    } else {
        response("error", 403, "Failed to update channels. Check error logs.", "");
    }
}
else
{
    session_start();
    if($action == "login")
    {
        $pin = "";
        if($_SERVER['REQUEST_METHOD'] !== "POST") { response("error", 405, "Method Not Supported", ""); }
        if(isset($_REQUEST['pin'])){ $pin = trim($_REQUEST['pin']); }
        
        if(empty($pin)) {
            response("error", 400, "Please Enter Access PIN To Login", "");
        }
        
        $irlPIN = app_accesspin("get", "");
        if(md5($pin) == md5($irlPIN)) {
            $_SESSION['yuvisession'] = true;
            response("success", 200, "Logged In Successfully", "");
        }
        response("error", 403, "Invalid Credentials", "");
    }
    elseif($action == "logout")
    {
        session_destroy();
        response("success", 200, "Logged Out Successfully", "");
    }

    // All other actions in this block require session
    if(!isset($_SESSION['yuvisession']) || $_SESSION['yuvisession'] !== true) {
        response("error", 401, "Unauthorized Access. Please login.", "");
    }

    if($action == "dashboard_data")
        {
            $expirydm = "-";
            $metaData = app_macportalmeta("get");
            if(isset($metaData['expiry']) && !empty($metaData['expiry'])) {
                $expirydm = date("d/m/Y", strtotime($metaData['expiry']));
            }
            
            // Get cached channel count without triggering a fetch
            $channelsCount = 0;
            $ctv_path = $APP_CONFIG['DATA_FOLDER']."/axCTV.enc";
            if(file_exists($ctv_path)) {
                $ctv_data = @json_decode(@file_get_contents($ctv_path), true);
                if(is_array($ctv_data)) {
                    $channelsCount = count($ctv_data);
                }
            }

            $xdetail = array("stalker_base" => app_macportaldetail("get", "", "", "", "", "", ""),
                             "stalker_data" => array("channels_count" => $channelsCount,
                                                     "expiry" => $expirydm),
                              "settings" => array("stream_proxy" => app_streamproxy('get'),
                                                "admin_button" => app_admin_button('get'),
                                                "logging_status" => app_logging('get'),
                                                "playback_cache" => app_playback_cache('get'),
                                                "genre_filter" => app_genre_filter("get")));
            response("success", 200, "Dashboard Data", $xdetail);
        }
        elseif($action == "toggle_playback_cache")
        {
            if(app_playback_cache("toggle")) {
                app_recordalogs("SUCCESS", "Playback Cache Status Toggled");
                response("success", 200, "Playback Cache Status Updated", "");
            }
            response("error", 500, "Failed to update Playback Cache Status", "");
        }
        elseif($action == "update_playback_expiry")
        {
            $expiry = 14400;
            if(isset($_POST['expiry'])) { $expiry = (int)$_POST['expiry']; }
            if(app_playback_cache("update_expiry", $expiry)) {
                app_recordalogs("SUCCESS", "Playback Cache Expiry Updated to ".$expiry."s");
                response("success", 200, "Playback Cache Expiry Saved", "");
            }
            response("error", 500, "Failed to save Playback Cache Expiry", "");
        }
        elseif($action == "save_genre_filter")
        {
            $filter = array();
            if(isset($_POST['filter']) && is_array($_POST['filter'])) {
                $filter = $_POST['filter'];
            }
            if(app_genre_filter("update", $filter)) {
                response("success", 200, "Genre Filter Saved Successfully", "");
            }
            response("error", 500, "Failed to save Genre Filter", "");
        }
        elseif($action == "change_access_pin")
        {
            $pin = "";
            if(isset($_POST['pin'])) {
                $pin = trim(strip_tags($_POST['pin']));
            }
            if(empty($pin)) {
                response("error", 400, "Please enter new Access PIN to Change", "");
            }
            if(!isValidAdminPIN($pin)) {
                response("error", 400, "Access PIN should be 4 numbers long", "");
            }
            if(app_accesspin("update", $pin)) {
                response("success", 200, "Access PIN Changed. Login Again.", "");
            }
            response("error", 500, "Failed to change Access PIN", "");
        }
        elseif($action == "save_mac_portal")
        {
            if($_SERVER['REQUEST_METHOD'] !== "POST") { response("error", 405, "Method Not Supported", ""); }
            $server_url = ""; $mac_id = ""; $serial = "";
            $device_id1 = ""; $device_id2 = ""; $signature = "";
            if(isset($_REQUEST['server_url'])){ $server_url = trim(strip_tags($_REQUEST['server_url'])); }
            if(isset($_REQUEST['mac_id'])){ $mac_id = trim(strip_tags($_REQUEST['mac_id'])); }
            if(isset($_REQUEST['serial'])){ $serial = trim(strip_tags($_REQUEST['serial'])); }
            if(isset($_REQUEST['device_id1'])){ $device_id1 = trim(strip_tags($_REQUEST['device_id1'])); }
            if(isset($_REQUEST['device_id2'])){ $device_id2 = trim(strip_tags($_REQUEST['device_id2'])); }
            if(isset($_REQUEST['signature'])){ $signature = trim(strip_tags($_REQUEST['signature'])); }
            if(empty($server_url)) {
                response("error", 400, "Please enter MAC Server URL", "");
            }
            if(empty($mac_id)) {
                response("error", 400, "Please enter MAC ID", "");
            }
            if (substr($server_url, -strlen('/c/')) !== '/c/') {
                response("error", 400, "MAC Server URL should end with /c/", "");
            }
            $savify = app_macportaldetail("update", $server_url, $mac_id, $serial, $device_id1, $device_id2, $signature);
            if($savify) {
                app_recordalogs("SUCCESS", "Stalker Portal Data Saved/Updated");
                response("success", 200, "Saved Successfully", "");
            }
            app_recordalogs("SUCCESS", "Failed To Save Stalker Portal Data");
            response("error", 500, "Failed To Save", "");
        }
        elseif($action == "update_mac_data")
        {
            if(empty(mac_serverurl())){ response("error", 503, "Stalker Portal details are not configured", ""); }
            $profile_data = mac_getprofile();
            if(empty($profile_data)) {
                response("error", 403, "Failed To Fetch Profile Details. Check Error Logs", "");
            }
            $channels = mac_getallChannels();
            if(empty($channels)) { $channels = mac_getallChannels(); }
            if(empty($channels)) {
                response("error", 403, "Failed To Fetch Channels List. Check Error Logs", "");
            }
            app_recordalogs("SUCCESS", "Stalker Portal Meta-Info Saved/Updated");
            response("success", 200, "Stalker Portal Details Updated Successfully", "");
        }
        elseif($action == "delete_mac_portal")
        {
            $scFile = scandir($APP_CONFIG['DATA_FOLDER']);
            if(isset($scFile[0])) {
                foreach($scFile as $vcfile) {
                    if($vcfile !== "axPIN.enc" && $vcfile !== "axADMBTN.enc") {
                        $bib = $APP_CONFIG['DATA_FOLDER']."/".$vcfile;
                        if(is_file($bib)) {
                            unlink($bib);
                        }
                    }
                }
            }
            response("success", 200, "Stalker Portal Deleted Successfully", "");
        }
        elseif($action == "toggle_stream_proxy")
        {
            $doChng = app_streamproxy('toggle');
            if(!$doChng) {
                 response("error", 500, "Failed To Toggle Stream Proxy Status", "");
            }
            $mesn = app_streamproxy("get");
            response("success", 200, "Stream Proxy Status Changed To ".$mesn, "");
        }
        elseif($action == "toggle_logging")
        {
            $doChng = app_logging('toggle');
            if(!$doChng) {
                 response("error", 500, "Failed To Toggle Logging Status", "");
            }
            $mesn = app_logging("get");
            response("success", 200, "Logging Status Changed To ".$mesn, "");
        }
        elseif($action == "clear_logs")
        {
            $path_actionlogs = $APP_CONFIG['DATA_FOLDER']."/axLogs.enc";
            if(file_exists($path_actionlogs)) {
                if(unlink($path_actionlogs)) {
                    response("success", 200, "Logs Cleared Successfully", "");
                } else {
                    response("error", 500, "Failed To Clear Logs", "");
                }
            }
            response("success", 200, "Logs Already Empty", "");
        }
        elseif($action == "toggle_admin_button")
        {
            $doChng = app_admin_button('toggle');
            if(!$doChng) {
                 response("error", 500, "Failed To Toggle Admin Button Visibility", "");
            }
            $mesn = app_admin_button("get");
            response("success", 200, "Admin Button Visibility Changed To ".$mesn, "");
        }
        else
        {
            response("error", 400, "Requested Module Does Not Exist", "");
        }
    }
?>