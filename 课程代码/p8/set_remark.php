<?php

require('../../vendor/autoload.php');

$remark_api = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=';
$remark_api .= weixin\wxToken::getToken();

$data = [
    'openid'=>'o3SIswU0_9th7MZ1RJfm1AGz2Dlg',
    'remark'=>'1234'
];
$post_data = json_encode($data,JSON_UNESCAPED_UNICODE);
$curl = new weixin\wxCURL;
$response = $curl->rawPost($remark_api,$post_data);
echo $response;
