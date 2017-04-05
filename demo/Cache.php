<?php
//接受第三方平台信息

include '../vendor/autoload.php';

use WechatOpen\Core\Config;
use WechatOpen\Core\Core;
use WechatOpen\Core\DB;
use WechatOpen\Decryption;
use WechatOpen\Core\Tools;
use WechatOpen\NotifyProcessing;

$redis_driver = new \WechatOpen\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:WechatOpen:');

$db = new DB($redis_driver);


$db->_set('tianyan', 'weiqi');
var_dump($db->_get('tianyan'));

$db::_set('weiqi', 'tianyan');
var_dump($db::_get('weiqi'));