<?php

require ('../../vendor/autoload.php');

$openid = request_data('get','openid','');
if (empty($openid)) {
    exit('Error: openid is empty');
}

//如果已缓存则返回缓存结果
$info=[];
$user_file = './user_info/'.$openid;
if (file_exists($user_file)) {
    $info = file_get_contents($user_file);
}

if (empty($info)) {
    $info_api = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=';
    $info_api .= weixin\wxToken::getToken();
    $info_api .= '&openid='.$openid.'&lang=zh_CN';
    $curl = new weixin\wxCURL;
    $info = $curl->get($info_api);
    file_put_contents($user_file, $info);
}

echo $info;
