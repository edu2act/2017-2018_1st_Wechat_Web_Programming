<?php

require ('../../vendor/autoload.php');

$curl = new weixin\wxCURL;
$token = weixin\wxToken::getToken();
$upload_url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=' . $token . '&type=image';

$file = new CURLFile('1234.jpg');
$response = $curl->rawPost('post',$upload_url,['media'=>$file]);

echo $response;
