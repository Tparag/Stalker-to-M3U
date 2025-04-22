<?php
error_reporting(0);
$directories = [
  "data" => "data",
  "filter" => "data/filter",
  "playlist" => "data/playlist"
];

foreach ($directories as $key => $dir_path) {
  if (!is_dir($dir_path)) {
      mkdir($dir_path, 0777, true);
  }
}

$tokenFile = $directories["data"] . "/token.txt";

date_default_timezone_set("Asia/Kolkata");

//Stalker Portal Data================================================================
$url = $mac = $sn = $device_id_1 = $device_id_2 = $sig = "";

$jsonFile = $directories["data"] . "/data.json";

if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $data = json_decode($jsonData, true);

    if ($data === null) {
        die("Error: JSON decoding failed. Check your JSON format.");
    }

    $url = $data["url"] ?? "";
    $mac = $data["mac"] ?? "";
    $sn = $data["serial_number"] ?? "";
    $device_id_1 = $data["device_id_1"] ?? "";
    $device_id_2 = $data["device_id_2"] ?? "";
    $sig = $data["signature"] ?? "";
}

$api = "263";
$host = parse_url($url)["host"];

//Handshake==========================================================================
function handshake()
{ 
  global $host;
  $Xurl = "http://$host/stalker_portal/server/load.php?type=stb&action=handshake&token=&JsHttpRequest=1-xml";
  $HED = [
    'User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
      'Connection: Keep-Alive',
      'Accept-Encoding: gzip',
      'X-User-Agent: Model: MAG250; Link: WiFi',
      "Referer: http://$host/stalker_portal/c/",
      "Host: $host",
      "Connection: Keep-Alive",
  ];
  $Info_Data = Info($Xurl,$HED);
  $Info_Status = $Info_Data["Info_arr"]["info"];
  $Info_Data =  $Info_Data["Info_arr"]["data"];
  $Info_Data_Json = json_decode($Info_Data,true);
  $Info_Encode = array(
    "Info_arr" => array(
        "token" => $Info_Data_Json["js"]["token"],
        "random" => $Info_Data_Json["js"]["random"],
        "Status Code" => $Info_Status
    )
  );
  return $Info_Encode;
}

//Generate Token======================================================================
function generate_token() 
{
  global $tokenFile, $host, $mac;
  $Info_Decode = handshake();
  $Bearer_token = $Info_Decode["Info_arr"]["token"];
  $Bearer_token = re_generate_token($Bearer_token);
  $Bearer_token = $Bearer_token["Info_arr"]["token"];
  get_profile($Bearer_token);
  file_put_contents($tokenFile, $Bearer_token);  
  return $Bearer_token;
}

//Re Generate Token===================================================================
function re_generate_token($Bearer_token)
{
  global $host;
  $Xurl = "http://$host/stalker_portal/server/load.php?type=stb&action=handshake&token=$Bearer_token&JsHttpRequest=1-xml";
  $HED = [
      'User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
      'Connection: Keep-Alive',
      'Accept-Encoding: gzip',
      'X-User-Agent: Model: MAG250; Link: WiFi',
      "Referer: http://$host/stalker_portal/c/",
      "Host: $host",
      "Connection: Keep-Alive",
  ];
  $Info_Data = Info($Xurl,$HED);
  $Info_Data =  $Info_Data["Info_arr"]["data"];
  $Info_Data_Json = json_decode($Info_Data,true);
  $Info_Encode = array(
    "Info_arr" => array(
    "token" => $Info_Data_Json["js"]["token"],
    "random" => $Info_Data_Json["js"]["random"],
    )
  );

  return $Info_Encode;
}

//Get Profile========================================================================
function get_profile($Bearer_token)
{
  global $host,$mac,$sn,$device_id_1,$device_id_2,$sig,$api;
  $timestamp = time();
  $Info_Decode = handshake();
  $Info_Decode_Random = $Info_Decode["Info_arr"]["random"];
  $Xurl = "http://$host/stalker_portal/server/load.php?type=stb&action=get_profile&hd=1&ver=ImageDescription%3A+0.2.18-r14-pub-250%3B+ImageDate%3A+Fri+Jan+15+15%3A20%3A44+EET+2016%3B+PORTAL+version%3A+5.1.0%3B+API+Version%3A+JS+API+version%3A+328%3B+STB+API+version%3A+134%3B+Player+Engine+version%3A+0x566&num_banks=2&sn=$sn&stb_type=MAG250&image_version=218&video_out=hdmi&device_id=$device_id_1&device_id2=$device_id_2&signature=$sig&auth_second_step=1&hw_version=1.7-BD-00&not_valid_token=0&client_type=STB&hw_version_2=08e10744513ba2b4847402b6718c0eae&timestamp=$timestamp&api_signature=$api&metrics=%7B%22mac%22%3A%22$mac%22%2C%22sn%22%3A%22$sn%22%2C%22model%22%3A%22MAG250%22%2C%22type%22%3A%22STB%22%2C%22uid%22%3A%22%22%2C%22random%22%3A%22$Info_Decode_Random%22%7D&JsHttpRequest=1-xml";
  $HED = [
    'User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
    'Connection: Keep-Alive',
    'Accept-Encoding: gzip',
    'X-User-Agent: Model: MAG250; Link: WiFi',
    "Referer: http://$host/stalker_portal/c/",
    "Authorization: Bearer " . $Bearer_token,
    "Host: $host",
    "Connection: Keep-Alive",
  ];
  Info($Xurl,$HED);
}

//INFO================================================================================
function Info($Xurl,$HED)
{
  global $mac;
  $cURL_Info = curl_init();
  curl_setopt_array($cURL_Info, [
    CURLOPT_URL => $Xurl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => 'gzip',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_COOKIE => "mac=$mac; stb_lang=en; timezone=GMT",
    CURLOPT_HTTPHEADER => $HED,
  ]);
  $Info_Data = curl_exec($cURL_Info);
  curl_close($cURL_Info);
  $Info_Status = curl_getinfo($cURL_Info);
  $Info_Encode = array(
    "Info_arr" => array(
        "data" => $Info_Data,
        "info" => $Info_Status,
    )
  );
  return  $Info_Encode;
}

//Get Groups================================================================================
function group_title($all = false) {
  global $host;
  global $directories;

  $dir_path = $directories["filter"];

  if (!is_dir($dir_path)) {
      mkdir($dir_path, 0777, true);
  }
  $filter_file = "$dir_path/$host.json";

  if (file_exists($filter_file)) {
      $json_data = json_decode(file_get_contents($filter_file), true);
      if (!empty($json_data)) {
          unset($json_data["*"]);
          
          if ($all) {
              return array_column($json_data, 'title', 'id');
          }
          
          return array_column(array_filter($json_data, function ($item) {
              return $item['filter'] === true;
          }), 'title', 'id');
      }
  }

  $group_title_url = "http://$host/stalker_portal/server/load.php?type=itv&action=get_genres&JsHttpRequest=1-xml";
  $headers = [
      "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
      "Authorization: Bearer " . generate_token(),
      "X-User-Agent: Model: MAG250; Link: WiFi",
      "Referer: http://$host/stalker_portal/c/",
      "Accept: */*",
      "Host: $host",
      "Connection: Keep-Alive",
      "Accept-Encoding: gzip",
  ];

  $response = Info($group_title_url, $headers);
  if (empty($response["Info_arr"]["data"])) {
      return [];
  }

  $json_api_data = json_decode($response["Info_arr"]["data"], true);
  if (!isset($json_api_data["js"]) || !is_array($json_api_data["js"])) {
      return [];
  }

  $filtered_data = [];
  foreach ($json_api_data["js"] as $genre) {
      if ($genre['id'] === "*") {
          continue; // Skip the "*" entry
      }
      $filtered_data[$genre['id']] = [
          'id' => $genre['id'],
          'title' => $genre['title'],
          'filter' => true,
      ];
  }

  file_put_contents($filter_file, json_encode($filtered_data));

  return array_column($filtered_data, 'title', 'id');
}
?>