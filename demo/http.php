<?php
//接受第三方平台信息

include '../vendor/autoload.php';

use WechatOpen\Core\Config;
use WechatOpen\Core\Core;
use WechatOpen\Core\DB;
use WechatOpen\Core\Http\Http;
use WechatOpen\Decryption;
use WechatOpen\Core\Tools;
use WechatOpen\NotifyProcessing;

$redis_driver = new \WechatOpen\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:WechatOpen:');

Core::init();

$response_data = Http::_post('https://www.baidu.com', ['test']);

var_dump($response_data);

$response_data = Http::_get('https://www.baidu.com');

var_dump($response_data);