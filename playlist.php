<?php

include("_inc.configs.php");

// Use existing cache if available
$ctv_path = $APP_CONFIG['DATA_FOLDER']."/axCTV.enc";
$genres_path = $APP_CONFIG['DATA_FOLDER']."/axGenres.enc";

// Get fresh channels and genres
$livetv = mac_getallChannels(); // This will fetch fresh data
$genres = mac_getGenres();
$genre_filter = app_genre_filter("get");

if(isset($livetv[0]))
{
    $icdata = '#EXTM3U'."\n";
    $icdata .= '#PLAYLIST: '.$APP_CONFIG['APP_NAME']."\n\n";
    
    $channels_by_category = array();
    
    // Group channels by tv_genre_id
    foreach($livetv as $itv) {
        $genre_title = 'Uncategorized';
        $genre_id = '0';
        
        if(isset($itv['tv_genre_id']) && !empty($itv['tv_genre_id']) && $itv['tv_genre_id'] !== '0') {
            $genre_id = (string)$itv['tv_genre_id'];
            if(isset($genres[$genre_id])) {
                $genre_title = $genres[$genre_id];
            }
        }
        
        // Apply Genre Filter
        if(!empty($genre_filter)) {
            if(!in_array($genre_id, $genre_filter)) { continue; }
        }
        
        if(!isset($channels_by_category[$genre_title])) {
            $channels_by_category[$genre_title] = array();
        }
        $channels_by_category[$genre_title][] = $itv;
    }
    
    ksort($channels_by_category);
    
    foreach($channels_by_category as $cat_title => $channels) {
        $icdata .= "\n".'#EXTGRP:'.$cat_title."\n";
        
        usort($channels, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        
        foreach($channels as $itv) {
            $tvg_id = $itv['id'];
            if(strpos($itv['cmd'], 'localhost') !== false) {
                $tvg_id = str_replace('ffrt http://localhost/ch/', '', $itv['cmd']);
                $tvg_id = trim($tvg_id);
            }
            
            $tvg_logo = fixlogoissue($itv['logo']);
            $stream_url = $streamenvproto."://".$plhoth.str_replace(" ", "%20", str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']))."live.m3u8?id=".$itv['id'];
            
            $icdata .= '#EXTINF:-1 tvg-id="'.$tvg_id.'" tvg-name="'.$itv['title'].'" tvg-logo="'.$tvg_logo.'" group-title="'.$cat_title.'",'.$itv['title']."\n";
            $icdata .= $stream_url."\n";
        }
    }
    
    $znm = app_macportaldetail("get", "", "", "", "", "", "");
    $server_url = isset($znm['server_url']) ? $znm['server_url'] : '';
    $host = parse_url($server_url, PHP_URL_HOST);
    if(empty($host)) { $host = cleanString($APP_CONFIG['APP_NAME']); }
    
    $file = $host . ".m3u";
    
    if(isset($_GET['view']) && $_GET['view'] == 'browser') {
        header('Content-Type: text/plain');
        header('Content-Disposition: inline; filename="'.$file.'"');
    } else {
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header('Content-Type: application/vnd.apple.mpegurl');
    }
    
    exit(trim($icdata));
} else {
    http_response_code(503);
    echo "No channels found.";
    exit();
}
?>