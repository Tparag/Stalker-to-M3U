<?php
include("_inc.configs.php");

echo "<h2>axCTV.enc Structure</h2>";
$ctv_path = $APP_CONFIG['DATA_FOLDER']."/axCTV.enc";
if(file_exists($ctv_path)) {
    $data = file_get_contents($ctv_path);
    $channels = json_decode($data, true);
    echo "<pre>";
    echo "Total Channels: " . count($channels) . "\n\n";
    echo "First 5 Channels Structure:\n";
    print_r(array_slice($channels, 0, 5));
    echo "</pre>";
} else {
    echo "axCTV.enc not found!";
}

echo "<h2>axGenres.enc Structure</h2>";
$genres_path = $APP_CONFIG['DATA_FOLDER']."/axGenres.enc";
if(file_exists($genres_path)) {
    $data = file_get_contents($genres_path);
    $genres = json_decode($data, true);
    echo "<pre>";
    echo "Total Genres: " . count($genres) . "\n\n";
    print_r($genres);
    echo "</pre>";
} else {
    echo "axGenres.enc not found!";
}

// Also check what's coming from portal directly
echo "<h2>Direct API Response Sample</h2>";
if(!empty(mac_serverurl())) {
    $xvAPI = mac_serverurl()."?type=itv&action=get_all_channels&JsHttpRequest=1-xml";
    $xvHead = array("User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
                    "X-User-Agent: Model: MAG250; Link: WiFi",
                    "Referer: ".mac_macurl(),
                    "Cookie: mac=".mac_macid()."; stb_lang=en; timezone=GMT",
                    "Authorization: Bearer ".mac_handshake()['token']);
    
    $fetch = getRequest($xvAPI, $xvHead);
    $adata = @json_decode($fetch['data'], true);
    
    echo "<pre>";
    if(isset($adata['js']['data'][0])) {
        echo "First channel from portal:\n";
        print_r($adata['js']['data'][0]);
    } else {
        echo "No data from portal or invalid response\n";
        print_r($adata);
    }
    echo "</pre>";
}
?>