<?php

require ('../../vendor/autoload.php');

//next_openid不填
$list_api = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=';
$list_api .= weixin\wxToken::getToken();


$curl = new weixin\wxCURL;
$response = $curl->get($list_api);
file_put_contents('user_list.save', $response);
echo $response;
