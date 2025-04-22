<?php
include "config.php";

$playlist_path = $directories["playlist"];

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$server = $_SERVER['HTTP_HOST'] ?? '';
$currentScript = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

function generateId($cmd) {
    $cmdParts = explode("/", $cmd);
    if ($cmdParts[2] === "localhost") {
        $cmd = str_ireplace('ffrt http://localhost/ch/', '', $cmd);
    } else if ($cmdParts[2] === "") {
        $cmd = str_ireplace('ffrt http:///ch/', '', $cmd);
    }
    return $cmd;
}

function getImageUrl($channel, $host) {    
    $imageExtensions = [".png", ".jpg"];
    $emptyReplacements = ['', ""];
    
    $logo = str_replace($imageExtensions, $emptyReplacements, $channel['logo']);
    if (is_numeric($logo)) {
        return 'http://' . $host . '/stalker_portal/misc/logos/320/' . $channel['logo'];
    } else {
        return "https://i.ibb.co/39Nz2wgJ/stalker.png";
    }
}

$playlist_file = "$playlist_path/$host.m3u";

if (file_exists($playlist_file)) {
    $playlistContent = file_get_contents($playlist_file);
    $playPath = str_replace("playlist.php", "", $currentScript);
    $playlistContent = preg_replace('/^(?!#).*\//m', "{$protocol}{$server}{$playPath}", $playlistContent);
    header('Content-Type: audio/x-mpegurl');
    header('Content-Disposition: inline; filename="playlist.m3u"');
    echo $playlistContent;  
} else {
    $Playlist_url = "http://$host/stalker_portal/server/load.php?type=itv&action=get_all_channels&JsHttpRequest=1-xml";

    $Playlist_HED = [
        "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
        "Authorization: Bearer " . generate_token(),
        "X-User-Agent: Model: MAG250; Link: WiFi",
        "Referer: http://$host/stalker_portal/c/",
        "Accept: */*",
        "Host: $host",
        "Connection: Keep-Alive",
        "Accept-Encoding: gzip",
    ];

    $playlist_result = Info($Playlist_url, $Playlist_HED);
    $playlist_result_data = $playlist_result["Info_arr"]["data"];
    $playlist_json_data = json_decode($playlist_result_data,true);
    $timestamp = date('l jS \of F Y h:i:s A');
    $tvCategories = group_title();

    if (!empty($playlist_json_data)) {    
        $playlistContent = "#EXTM3U\n#DATE:- $timestamp\n" . PHP_EOL;   
        foreach ($playlist_json_data["js"]["data"] as $channel) {        
            foreach ($tvCategories as $genreId => $categoryName) {              
                if ($channel['tv_genre_id'] == $genreId) { 
                    $cmd = $channel['cmd'];
                    $id = generateId($cmd);                                       
                    $playPath = str_replace("playlist.php", "play.php?id=" . $id , $currentScript);                                                  
                    $playlistContent .= '#EXTINF:-1 tvg-id="' . $id . '" tvg-logo="' . getImageUrl($channel, $host) . '" group-title="' . $categoryName . '",' . $channel['name'] . "\r\n";
                    $playlistContent .= "{$protocol}{$server}{$playPath}" . PHP_EOL . PHP_EOL;
                }
            }
        }
        
        header('Content-Type: audio/x-mpegurl');
        header('Content-Disposition: inline; filename="playlist.m3u"');
        echo $playlistContent;    
        file_put_contents("$playlist_path/$host.m3u", $playlistContent);
    } else {    
        echo 'Empty or invalid response from the server.';    
    }
}
?>