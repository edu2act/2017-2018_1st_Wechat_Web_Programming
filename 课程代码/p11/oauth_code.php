<?php

$appid='wx01935b533f3c3a98';
$secret='ea7060168afdd5469139c8db0e6b07f1';
$token = 'wxtest2017';

$return_uri = 'http://www.hzwjia.com/shop/weixin/oauth_return.php';

$get_code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize';
$get_code_url .= '?appid='.$appid.'&redirect_uri='. urlencode($return_uri) . 
  '&response_type=code&scope=snsapi_userinfo&state=test#wechat_redirect';

header('location:'.$get_code_url);
