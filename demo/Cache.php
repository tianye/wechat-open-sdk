<?php
//接受第三方平台信息

include '../vendor/autoload.php';

use OpenOauth\Core\Config;
use OpenOauth\Core\Core;
use OpenOauth\Core\DB;
use OpenOauth\Decryption;
use OpenOauth\Core\Tools;
use OpenOauth\NotifyProcessing;

$redis_driver = new \OpenOauth\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:OpenOauth:');

$db = new DB($redis_driver);


$db->_set('tianyan', 'weiqi');
var_dump($db->_get('tianyan'));

$db::_set('weiqi', 'tianyan');
var_dump($db::_get('weiqi'));