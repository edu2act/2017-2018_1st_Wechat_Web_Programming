<?php

require '../vendor/autoload.php';

use anlutro\cURL\cURL;

$curl = new anlutro\cURL\cURL;

$appid = 'wx01935b533f3c3a98';
$appsecret = 'ea7060168afdd5469139c8db0e6b07f1';
$token_api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

$request = $curl->newRequest('get',$token_api)
        ->setHeader('Accept-Charset','utf-8')
        ->setOptions([
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_SSL_VERIFYPEER=>false,
        ]);
$response = $request->send();

$token = json_decode($response,true);
if( !isset($token['access_token']) ) {
    exit('Error: token invalid');
}

$delete_menu_api = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $token['access_token'];

$curl = new anlutro\cURL\cURL;
$request = $curl->newRequest('get',$delete_menu_api)
        ->setHeader('Accept-Charset','utf-8')
        ->setOptions([
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_SSL_VERIFYPEER=>false,
        ]);
$response = $request->send();

exit($response);
