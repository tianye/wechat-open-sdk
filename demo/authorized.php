<?php
//生成 授权页面

//http://applist-test-open.weflame.com/authorized.php

include '../vendor/autoload.php';

use WechatOpen\Authorized;
use WechatOpen\Core\Config;
use WechatOpen\Core\Core;
use WechatOpen\Core\DB;

$config = new Config();
$config->init(['component_app_id' => '第三方平台appId', 'component_app_secret' => '第三方平台appSecret', 'component_app_token' => '第三方平台appToken', 'component_app_key' => '第三方平台appKey']);

$redis_driver = new \WechatOpen\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:WechatOpen:');

$db = new DB($redis_driver);

Core::init($db);

$authorized = new Authorized();

$authorized->getAuthHTML('index.php');