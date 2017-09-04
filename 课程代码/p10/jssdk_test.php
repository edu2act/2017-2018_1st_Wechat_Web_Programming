<?php

require ('../../vendor/autoload.php');

$_view=[];

$js_ticket = weixin\wxJSTicket::getTicket();

if (empty($js_ticket)) {
    $_view['error_info'] = '获取js_ticket失败，请稍后再试，如果一直遇到此问题，请检查配置参数是否有错误。';
    goto error_end;
}

$sign = create_signature($js_ticket);
$_view['timestamp'] = $sign['sign_arr']['timestamp'];
$_view['noncestr'] = $sign['sign_arr']['noncestr'];
$_view['signature'] = $sign['signature'];
$_view['sign_str'] = $sign['sign_str'];

error_end:;
//如果有错误则加载错误信息模板
if ( isset($_view['error_info'])
    && !empty($_view['error_info']) ) {
    include('js_sdk_error.html');
    exit();
}

//加载模板
include ('js_sdk.html');
