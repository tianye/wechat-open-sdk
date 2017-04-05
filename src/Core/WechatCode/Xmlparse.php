<?php
namespace OpenOauth\Core\WechatCode;

use OpenOauth\Core\WechatCode\ErrorCode;

/**
 * XMLParse class
 *
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 */
class XMLParse
{

    /**
     * 提取出xml数据包中的加密消息
     *
     * @param string $xmltext 待提取的xml字符串
     *
     * @return string 提取出的加密消息字符串
     */
    public function extract($xmltext)
    {
        try {
            $object     = simplexml_load_string($xmltext, 'SimpleXMLElement', LIBXML_NOCDATA);
            $encrypt    = $object->Encrypt;
            $tousername = $object->ToUserName;

            return [0, $encrypt, $tousername];
        } catch (Exception $e) {
            //print $e . "\n";
            return [ErrorCode::$ParseXmlError, null, null];
        }
    }

    /**
     * 生成xml消息
     *
     * @param string $encrypt   加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce     随机字符串
     */
    public function generate($encrypt, $signature, $timestamp, $nonce)
    {
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";

        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
}
