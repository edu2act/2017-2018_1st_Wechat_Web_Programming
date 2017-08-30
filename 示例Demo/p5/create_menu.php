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

$menu=[
    'button'=>[
        [
            'name'=>'开发',
            'sub_button'=>[
                [
                    'type'=>'view',
                    'url'=>'http://blog.jobbole.com/',
                    'name'=>'伯乐在线'
                ],
                [
                    'type'=>'view',
                    'url'=>'http://www.runoob.com/',
                    'name'=>'菜鸟教程'
                ],
            ]
        ],
    ]
];

//创建菜单API，使用上面后取得access_token
$create_menu_api = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $token['access_token'];

$data = json_encode($menu,JSON_UNESCAPED_UNICODE);
$request = $curl->newRawRequest('post',$create_menu_api,$data)
    ->setHeader('Accept-Charset','utf-8')
    ->setOptions([
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_POST=>true
    ]);
$response = $request->send();

exit($response);
