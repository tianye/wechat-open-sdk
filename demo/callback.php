<?php
//接受服务号回调信息 用于把第三方开放平台测试版 转换为 正式版

include '../vendor/autoload.php';

use OpenOauth\core\Tools;

$record = new Tools();
//测试记录文本日志
//$record->dataRecodes('callback_server', $_SERVER, 'callback');
//$record->dataRecodes('callback_file_get_contents', file_get_contents('php://input'), 'callback');
//$record->dataRecodes('callback_get', $_GET, 'callback');
//$record->dataRecodes('callback_post', $_POST, 'callback');