<?php

require ('../../vendor/autoload.php');
require ('../../wxcall.php');

use anlutro\cURL\cURL;

$curl = new anlutro\cURL\cURL;

$options = [
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>true
];

/*
"title": TITLE,

"thumb_media_id": THUMB_MEDIA_ID,

"author": AUTHOR,

"digest": DIGEST,

"show_cover_pic": SHOW_COVER_PIC(0 / 1),

"content": CONTENT,

"content_source_url": CONTENT_SOURCE_URL
*/

$news=[
    'articles'=>[
        [
            'title'=>'First News',
            'thumb_media_id'=>'gCDI2tlSIZh0grmcUzzCt1GVvf_LjV3X2Tfco4rjlAU',
            'author'=>'BraveWang',
            'digest'=>'nothing',
            'show_cover_pic'=>1,
            'content'=>'The first news for test page.',
            'content_source_url'=>''
        ]
    ]
];

//转换成JSON格式字符串进行POST提交
$json_news = json_encode($news,JSON_UNESCAPED_UNICODE);
//获取Token并构造参数
$token = WxToken::getToken();
$news_url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=' . $token;

$request = $curl->newRawRequest('post',$news_url,$json_news)
    ->setOptions($options);
$response = $request->send();

echo $response;
