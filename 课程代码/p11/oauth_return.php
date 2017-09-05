<?php

require('vendor/autoload.php');

$appid='wx01935b533f3c3a98';
$secret='ea7060168afdd5469139c8db0e6b07f1';

$code = isset($_GET['code'])?$_GET['code']:'';
if(empty($code)){
  exit('Error: code is empty!');
}

$token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token'.
  '?appid=' . $appid .'&secret=' . $secret . '&code=' . $code . 
  '&grant_type=authorization_code';

//get access_token
$c = curl_init($token_url);
if(!$c){
  exit("failed to open url");
}
curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
curl_setopt($c,CURLOPT_SSL_VERIFYPEER,false);
$ret = curl_exec($c);
curl_close($c);

$json_token = json_decode($ret,true);

if(!isset($json_token['access_token'])){
  exit($ret);
}
//end to get access_token

$info_url = 'https://api.weixin.qq.com/sns/userinfo'.
  '?access_token='.$json_token['access_token'].'&openid='.$json_token['openid'].'&lang=zh_CN';

use anlutro\cURL\cURL;
$curl = new anlutro\cURL\cURL;
//初始化请求并设置连接选项
$request = $curl->newRequest('get', $info_url,[])
  ->setHeader('Accept-Charset', 'utf-8')
  ->setOption(CURLOPT_RETURNTRANSFER, true)
  ->setOption(CURLOPT_SSL_VERIFYPEER, false);
$response = $request->send();
$info = json_decode($response,true);

$view=[];

if(!isset($info['openid'])){
  $view['errorinfo']='获取用户信息失败';
  include('oauth_userinfo_error.html');
  exit;
}

$view['nickname'] = $info['nickname'];
$view['where'] = $info['country'].$info['province'].$info['city'];
$view['openid'] = $info['openid'];
if($info['headimgurl']){
  $view['headimgurl']='<img src="' . $info['headimgurl'] . '" style="width:50%;height:auto;">';
}

include('oauth_userinfo.html');
