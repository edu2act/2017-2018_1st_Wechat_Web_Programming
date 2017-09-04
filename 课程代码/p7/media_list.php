<?php

require ('../../vendor/autoload.php');

$wxm = new weixin\wxMedia;

walk_arr_echo(json_decode($wxm->getMediaList('image'),true));

walk_arr_echo(json_decode($wxm->getMediaList('thumb'),true));
