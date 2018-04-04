#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php

if ($argc !== 5) {
    echo 'badparam';
    exit();
}

$account = (string)$argv[1];
$pwd = (string)$argv[2];
$hostname = (string)$argv[3];
$ip = (string)$argv[4];

$API_TOKEN = $account;

// check the hostname contains '.'
if (strpos($hostname, '.') === false) {
    echo "badparam";
    exit();
}

// only for IPv4 format
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo "badparam";
    exit();
}

$hostname = explode('.', $hostname);
$arrayCount = count($hostname);
if ($arrayCount > 2) {
    $subDomain = implode('.', array_slice($hostname, 0, $arrayCount-2));
    $domain = implode('.', array_slice($hostname, $arrayCount-2, 2));
} else {
    $subDomain = '@';
    $domain = implode('.', $hostname);
}

$post = array(
    'api_key'=>$account,
    'api_action'=>'domain.list'
);
$req = curl_init();

function linode_request($req, $api, $method = 'GET', $request = [])
{
    global $API_TOKEN;

    $BASE_URL = 'https://api.linode.com/v4';
    $HEADERS = [ 'Authorization: Bearer '.$API_TOKEN, 'Content-type: application/json' ];

    curl_setopt($req, CURLOPT_URL, $BASE_URL.$api);
    curl_setopt($req, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($req, CURLOPT_HTTPHEADER, $HEADERS);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
    if (count($request) > 0) {
        curl_setopt($req, CURLOPT_POST, true);
        curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($request));
    } else {
        curl_setopt($req, CURLOPT_POST, false);
    }
    $res = curl_exec($req);
    $http_status = curl_getinfo($req, CURLINFO_HTTP_CODE);

    $error = null;
    switch($http_status) {
    case 200: break;
    case 401: $error = 'badauth'; break;
    default : $error = '911'; break;
        }
    if($error !== null) {
        echo $error;
        curl_close($req);
        exit(-1);
    }
    $json = json_decode($res, true);
    return $json;
}

$json = linode_request($req, '/domains', 'GET');
if ($json['results'] < 1) {
    echo 'nohost';
    curl_close($req);
    exit();
}

$domainId = null;
foreach($json['data'] as $row) {
    if ($row['domain'] == $domain)
        $domainId = $row['id'];
}
if ($domainId === null) {
    echo 'nohost';
    curl_close($req);
    exit();
}

$json = linode_request($req, '/domains/'.$domainId.'/records', 'GET');
if ($json['results'] < 1) {
    echo 'nohost';
    curl_close($req);
    exit();
}

$recordId = null;
foreach($json['data'] as $row) {
    if ($row['type'] == 'A' && $row['name'] == $subDomain) {
        $recordId = $row['id'];
        $recordTarget = $row['target'];
    }
}
if ($recordId === null) {
    echo 'nohost';
    curl_close($req);
    exit();
}
if ($recordTarget == $ip) {
    echo 'nochg';
    curl_close($req);
    exit();
}

$json = linode_request($req, '/domains/'.$domainId.'/records/'.$recordId, 'PUT', [ 'name' => $subDomain, 'target' => $ip ]);
curl_close($req);
echo 'good';
