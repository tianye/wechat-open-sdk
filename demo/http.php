<?php
//接受第三方平台信息

include '../vendor/autoload.php';

use OpenOauth\Core\Config;
use OpenOauth\Core\Core;
use OpenOauth\Core\DB;
use OpenOauth\Core\Http\Http;
use OpenOauth\Decryption;
use OpenOauth\Core\Tools;
use OpenOauth\NotifyProcessing;

$redis_driver = new \OpenOauth\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:OpenOauth:');

Core::init();

$response_data = Http::_post('https://www.baidu.com', ['test']);

var_dump($response_data);

$response_data = Http::_get('https://www.baidu.com');

var_dump($response_data);