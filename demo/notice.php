<?php
//接受第三方平台信息

include '../vendor/autoload.php';

use WechatOpen\Core\Config;
use WechatOpen\Core\Core;
use WechatOpen\Core\DB;
use WechatOpen\Decryption;
use WechatOpen\Core\Tools;
use WechatOpen\NotifyProcessing;

$config = new Config();
$config->init(['component_app_id' => '第三方平台appId', 'component_app_secret' => '第三方平台appSecret', 'component_app_token' => '第三方平台appToken', 'component_app_key' => '第三方平台appKey']);

$redis_driver = new \WechatOpen\Core\DBDriver\RedisDriver(['host' => '127.0.0.1', 'port' => '6379', 'database' => '2'], 'Cache:WechatOpen:');

$db = new DB($redis_driver);

Core::init($db);


$decryption = new Decryption();
$xml_array  = $decryption->decryptionNoticeXML();

//测试记录日志
//Tools::dataRecodes('xml_array', $xml_array, 'notice');

$notify_processing = new NotifyProcessing();
switch ($xml_array['InfoType']) {
    case 'component_verify_ticket':
        //每10分钟 接收一次微信推送过来 当前 第三方平台的 ticket 并且缓存
        $notify_processing->componentVerifyTicket($xml_array);
        exit('SUCCESS');
        break;
    case 'authorized':
        //服务号授权
        $notify_processing->Authorized($xml_array);
        exit('SUCCESS');
        break;
    case 'unauthorized':
        //服务号取消授权
        $notify_processing->UnAuthorized($xml_array);
        exit('SUCCESS');
        break;
    case 'updateauthorized':
        //服务号更新授权
        $notify_processing->UpdateAuthorized($xml_array);
        exit('SUCCESS');
        break;
}

exit('FAIL');
