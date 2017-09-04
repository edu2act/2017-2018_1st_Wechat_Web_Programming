<?php

require ('../../vendor/autoload.php');

//检查是否存在已保存的用户列表文件，并读取信息，不存在则返回错误信息
$user_file = 'user_list.save';
if ( file_exists($user_file) ) {
    $user_list = file_get_contents($user_file);
} else {
    exit('user list is empty');
}
//end

//如果解析JSON数据不是正确的列表格式，而是错误信息则返回错误信息
$user_list = json_decode($user_list,true);
if ( isset($user_list['errcode']) ) {
    exit($user_list['errmsg']);
}

$html = '';
foreach ($user_list['data']['openid'] as $u) {
    $html .= '<p><a href="get_user_info.php?openid=' . $u . '">' . $u . '</a></p>';
}

echo $html;
