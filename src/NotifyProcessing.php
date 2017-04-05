<?php
namespace WechatOpen;

use WechatOpen\Core\Core;

class NotifyProcessing extends Core
{
    /**
     * @param $xml_array
     *
     * @return mixed
     */
    public function componentVerifyTicket($xml_array)
    {
        $key = 'component_verify_ticket:' . $xml_array['AppId'];
        self::$cacheDriver->_set($key, $xml_array['ComponentVerifyTicket'], 1200);
        $component_verify_ticket = $xml_array['ComponentVerifyTicket'];

        return $component_verify_ticket;
    }

    /**
     * Authorized
     *
     * @param $xml_array
     *
     * @return array|bool
     */
    public function Authorized(array $xml_array)
    {
        $key = 'authorized:' . $xml_array['AppId'] . ':' . $xml_array['AuthorizerAppid'];
        self::$cacheDriver->_set($key, $xml_array, 0);

        return $xml_array;
    }

    /**
     * UpdateAuthorized
     *
     * @param $xml_array
     *
     * @return array|bool
     */
    public function UpdateAuthorized(array $xml_array)
    {
        $key = 'authorized:' . $xml_array['AppId'] . ':' . $xml_array['AuthorizerAppid'];
        self::$cacheDriver->_set($key, $xml_array, 0);

        return $xml_array;
    }

    public function UnAuthorized($xml_array)
    {
        $key = 'query_auth:' . $this->configs->component_app_id . ':' . $xml_array['AuthorizerAppid'];

        $query_auth_info = self::$cacheDriver->_get($key);

        if (!empty($query_auth_info)) {
            $query_auth_info['authorization_state'] = 'unauthorized';

            self::$cacheDriver->_set($key, $query_auth_info, 0);
        }

        return $xml_array;
    }
}