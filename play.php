<?php
include "config.php";

$Bearer_token = (file_exists($tokenFile) && filesize($tokenFile)) ? file_get_contents($tokenFile) : generate_token();

$config = [
    'stalkerUrl' => "http://$host/stalker_portal/",
    'macAddress' => $mac,
    'authorizationToken' => "Bearer $Bearer_token",
];

if (empty($_GET['id'])) {
    die("Error: Channel ID is missing.");
}

$channelId = $_GET['id'];

function fetchStreamUrl($config, $channelId) {
    $headers = getHeaders($config);
    $streamUrlEndpoint = "{$config['stalkerUrl']}server/load.php?type=itv&action=create_link&cmd=ffrt%20http://localhost/ch/{$channelId}&JsHttpRequest=1-xml";
    
    $data = executeCurl($streamUrlEndpoint, $headers);
    
    if (!isset($data['js']['cmd'])) {
        $config['authorizationToken'] = "Bearer " . generate_token();
        $headers = getHeaders($config);
        $data = executeCurl($streamUrlEndpoint, $headers);
    }
    
    return $data['js']['cmd'] ?? die("Failed to retrieve stream URL for channel ID: {$channelId}.");
}

function getHeaders($config) {
    return [
        "Cookie: timezone=GMT; stb_lang=en; mac={$config['macAddress']}",
        "Referer: {$config['stalkerUrl']}",
        "Accept: */*",
        "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
        "X-User-Agent: Model: MAG250; Link: WiFi",
        "Authorization: {$config['authorizationToken']}",
        "Host: " . parse_url($config['stalkerUrl'], PHP_URL_HOST),
        "Connection: Keep-Alive",
        "Accept-Encoding: gzip"
    ];
}

function executeCurl($url, $headers) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => 'gzip',
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);
    
    return json_decode($response, true);
}

$streamUrl = fetchStreamUrl($config, $channelId);
header("Location: $streamUrl");
exit;
