<?php

//header("Access-Control-Allow-Origin: *");
include("_inc.configs.php");

$id = ""; $chunks = ""; $segment = "";
if(isset($_REQUEST['id'])){ $id = trim(strip_tags($_REQUEST['id'])); }
if(isset($_REQUEST['chunks'])){ $chunks = trim(strip_tags($_REQUEST['chunks'])); }
if(isset($_REQUEST['segment'])){ $segment = trim(strip_tags($_REQUEST['segment'])); }

if(stripos($_SERVER['REQUEST_URI'], ".php?") !== false){ $M3U_EXT = $KEY_EXT = $TS_EXT = ".php"; }else{ $KEY_EXT = ".key"; $M3U_EXT = ".m3u8"; $TS_EXT = ".ts"; }
$streamHeader = array("User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3");

//================================================================//

// Simplified security check is now handled within getChannels() if needed
if(empty($mac_details = app_macportaldetail("get", "", "", "", "", "", "")) || empty($mac_details['server_url'])){ http_response_code(403); exit(); }

if(!empty($id))
{
    $ctv_detail = getChannelDetail($id);
    if(empty($ctv_detail)){ http_response_code(404); exit(); }
    $streamURL = mac_getPlaybackLink($ctv_detail['id']);
    if(empty($streamURL)) {
        http_response_code(503); exit();
    }
    if(app_streamproxy('get') == "OFF")
    {
        header("Location: ".$streamURL);
        exit();
    }
    else
    {
        $fetch = getRequest($streamURL, $streamHeader);
        $return = $fetch['data'];
        $effective_url = $fetch['url'];
        if(stripos($return, "#EXTM3U") !== false)
        {
            $hine = "";
            $line = explode("\n", $return);
            foreach($line as $vine)
            {
                if(stripos($vine, 'URI="') !== false)
                {
                    $orgURL = extractURIPart($vine);
                    if($orgURL[0] == "/"){ $iBaseURL = getRootBase($effective_url); }else{ $iBaseURL = getRelativeBase($effective_url); }
                    if(stripos($iBaseURL, 'http://') !== false || stripos($iBaseURL, 'https://') !== false){ $iBaseURL = ""; }
                    $norgURL = "live".$M3U_EXT."?chunks=".ex_encdec('encrypt', $iBaseURL.$orgURL);
                    $hine .= str_replace($orgURL, $norgURL, $vine)."\n";
                }
                elseif(stripos($vine, 'URI="') === false && stripos($vine, '.ts') !== false)
                {
                    if($vine[0] == "/"){ $iBaseURL = getRootBase($effective_url); }else{ $iBaseURL = getRelativeBase($effective_url); }
                    if(stripos($vine, 'http://') !== false || stripos($vine, 'https://') !== false){ $iBaseURL = ""; }
                    $hine .= "live".$TS_EXT."?segment=".ex_encdec('encrypt', $iBaseURL.$vine)."\n";
                }
                elseif(stripos($vine, 'URI="') === false && stripos($vine, '.m3u8') !== false || stripos($vine, '/hls') !== false)
                {
                    if($vine[0] == "/"){ $iBaseURL = getRootBase($effective_url); }else{ $iBaseURL = getRelativeBase($effective_url); }
                    if(stripos($vine, 'http://') !== false || stripos($vine, 'https://') !== false){ $iBaseURL = ""; }
                    $hine .= "live".$M3U_EXT."?chunks=".ex_encdec('encrypt', $iBaseURL.$vine)."\n";
                }
                else
                {
                    $hine .= $vine."\n";
                }
            }
            header("Content-Type: application/vnd.apple.mpegurl");
            print(trim($hine));
            exit();
        }
        http_response_code(502);
        exit();
    }
}
elseif(!empty($chunks))
{
    $streamURL = ex_encdec('decrypt', $chunks);
    if(!filter_var($streamURL, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        exit();
    }
    $fetch = getRequest($streamURL, $streamHeader);
    $return = $fetch['data'];
    $effective_url = $fetch['url'];
    if(stripos($return, "#EXTM3U") !== false)
    {
        $hine = "";
        $line = explode("\n", $return);
        foreach($line as $vine)
        {
            if(stripos($vine, 'URI="') !== false)
            {
                $orgURL = extractURIPart($vine);
                if($orgURL[0] == "/"){ $iBaseURL = getRootBase($effective_url); }else{ $iBaseURL = getRelativeBase($effective_url); }
                if(stripos($iBaseURL, 'http://') !== false || stripos($iBaseURL, 'https://') !== false){ $iBaseURL = ""; }
                $norgURL = "live".$KEY_EXT."?chunks=".ex_encdec('encrypt', $iBaseURL.$orgURL);
                $hine .= str_replace($orgURL, $norgURL, $vine)."\n";
            }
            elseif(stripos($vine, 'URI="') === false && stripos($vine, '.ts') !== false)
            {
                if($vine[0] == "/"){ $iBaseURL = getRootBase($effective_url); }else{ $iBaseURL = getRelativeBase($effective_url); }
                if(stripos($vine, 'http://') !== false || stripos($vine, 'https://') !== false){ $iBaseURL = ""; }
                $hine .= "live".$TS_EXT."?segment=".ex_encdec('encrypt', $iBaseURL.$vine)."\n";
            }
            else
            {
                $hine .= $vine."\n";
            }
        }
        header("Content-Type: application/vnd.apple.mpegurl");
        print(trim($hine));
        exit();
    }
    http_response_code(404);
    exit();
}
elseif(!empty($segment))
{
    $streamURL = ex_encdec('decrypt', $segment);
    if(!filter_var($streamURL, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        exit();
    }
    header("Content-Type: video/m2ts");
    streamRequest($streamURL, $streamHeader);
    exit();
}
else
{
    http_response_code(400);
    exit();
}

?>