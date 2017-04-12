<?php

namespace WechatOpen\Core;

use GuzzleHttp\Client;
use WechatOpen\Core\Exceptions\ConfigMistakeException;
use WechatOpen\Core\Http\Http;
use stdClass;

class Core
{
    /** @var  object */
    public           $configs;
    protected static $error;
    protected static $http;
    /** @var  DB */
    protected static $cacheDriver;

    private static $authorizer_access_token;

    const GET_COMPONENT_ACCESS_TOKEN  = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token'; //获取第三方token
    const GET_COMPONENT_PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode'; //获取第三方auth_code

    const GET_API_AUTHORIZER_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token'; //获取（刷新）授权公众号的接口调用凭据（令牌）
    const GET_API_QUERY_AUTH           = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth'; //使用授权码换取公众号的接口调用凭据和授权信息

    /**
     * 初始化 缓存 数据库 配置
     *
     * @param \WechatOpen\Core\DB|null $cacheDriver
     * @param \GuzzleHttp\Client|null $http
     */
    public static function init(DB $cacheDriver = null, Client $http = null)
    {
        if (!self::$http) {
            if (!$http) {
                $http = new Client();
            }
            self::$http = new Http($http);
        }

        self::$cacheDriver = $cacheDriver;
    }

    /**
     * Core constructor.
     *
     */
    public function __construct()
    {
        if (!self::$cacheDriver) {
            throw new ConfigMistakeException('未初始化init 缓存 数据库 配置');
        }

        if (!self::$http) {
            throw new ConfigMistakeException('未初始化HTTP 缓存 数据库 配置');
        }

        $configs = Config::$configs;

        if (!isset($configs['component_app_id']) || !isset($configs['component_app_secret']) || !isset($configs['component_app_token']) || !isset($configs['component_app_key'])) {
            throw new ConfigMistakeException();
        }

        if (!$this->configs) {
            $this->configs                       = new stdClass();
            $this->configs->component_app_id     = $configs['component_app_id'];
            $this->configs->component_app_secret = $configs['component_app_secret'];
            $this->configs->component_app_token  = $configs['component_app_token'];
            $this->configs->component_app_key    = $configs['component_app_key'];
        }
    }

    /**
     * 获取开放平台 ComponentAccessToken
     *
     * @return bool|mixed
     */
    public function getComponentAccessToken()
    {
        $component_access_token = self::$cacheDriver->_get('component_access_token:' . $this->configs->component_app_id);

        if (false == $component_access_token) {

            $request_data = [
                'component_appid'         => $this->configs->component_app_id,
                'component_appsecret'     => $this->configs->component_app_secret,
                'component_verify_ticket' => self::$cacheDriver->_get('component_verify_ticket:' . $this->configs->component_app_id),
            ];

            $response_data = Http::_post(self::GET_COMPONENT_ACCESS_TOKEN, $request_data);

            if (!$response_data || !is_array($response_data) || empty($response_data)) {
                $this->setError(Http::$error);

                return false;
            }

            self::$cacheDriver->_set('component_access_token:' . $this->configs->component_app_id, $response_data['component_access_token'], 5000);

            $component_access_token = $response_data['component_access_token'];
        }

        return $component_access_token;
    }

    /**
     * 获取第三方auth_code
     *
     * @return bool|mixed
     */
    public function getComponentPreAuthCode()
    {
        $component_access_token = $this->getComponentAccessToken();

        $query_data   = http_build_query(['component_access_token' => $component_access_token]);
        $request_data = [
            'component_appid' => $this->configs->component_app_id,
        ];

        $response_data = Http::_post(self::GET_COMPONENT_PRE_AUTH_CODE . '?' . $query_data, $request_data);
        if (!$response_data || !is_array($response_data) || empty($response_data)) {
            $this->setError(Http::$error);

            return false;
        }

        $component_pre_auth_code = $response_data['pre_auth_code'];

        if (false == $component_pre_auth_code) {
            $component_pre_auth_code = self::$cacheDriver->_get('component_pre_auth_code:' . $this->configs->component_app_id);
        } else {
            self::$cacheDriver->_set('component_pre_auth_code:' . $this->configs->component_app_id, $component_pre_auth_code, 5000);
        }

        return $component_pre_auth_code;
    }

    /**
     * 获取（刷新）授权公众号的接口调用凭据（令牌)
     *
     * @param  string $authorizer_app_id        公众号app_id
     * @param  string $authorizer_refresh_token 刷新TOKEN的 authorizer_refresh_token
     *
     * @return array|bool $authorization_info
     */
    private function getApiAuthorizerToken($authorizer_app_id = '', $authorizer_refresh_token = '')
    {
        $query_data   = http_build_query(['component_access_token' => $this->getComponentAccessToken()]);
        $request_data = [
            'component_appid'          => $this->configs->component_app_id,
            'authorizer_appid'         => $authorizer_app_id,
            'authorizer_refresh_token' => $authorizer_refresh_token,
        ];

        $response_data = Http::_post(self::GET_API_AUTHORIZER_TOKEN_URL . '?' . $query_data, $request_data);

        if (!$response_data) {
            $this->setError(Http::$error);

            return false;
        }

        return $response_data;
    }

    /**
     * 使用授权码换取公众号的接口调用凭据和授权信息
     *
     * @param string $authorizer_app_id
     *
     * @return array|bool|\WechatOpen\Core\DBDriver\BaseDriver
     */
    public function getApiQueryAuth($authorizer_app_id = '')
    {
        $time = time();

        $authorization_info_key = 'authorized:' . $this->configs->component_app_id . ':' . $authorizer_app_id;
        $query_auth_key         = 'query_auth:' . $this->configs->component_app_id . ':' . $authorizer_app_id;

        $query_auth_info = self::$cacheDriver->_get($query_auth_key);
        //如果存在数据
        if (!empty($query_auth_info)) {

            //没超时 返回数据
            if ($query_auth_info['expired_time'] >= ($time + 1000) && $query_auth_info['authorization_state'] == 'authorized') {
                return $query_auth_info;
            } else {
                //如果超时了 获取新的 access_token 和 新的 刷新令牌 refresh_token
                $api_authorizer_token = $this->getApiAuthorizerToken($query_auth_info['authorization_info']['authorizer_appid'], $query_auth_info['authorization_info']['authorizer_refresh_token']);
                if (!empty($api_authorizer_token)) {
                    $query_auth_info['authorization_info']['authorizer_access_token']  = $api_authorizer_token['authorizer_access_token'];
                    $query_auth_info['authorization_info']['authorizer_refresh_token'] = $api_authorizer_token['authorizer_refresh_token'];
                    $query_auth_info['authorization_info']['expires_in']               = $api_authorizer_token['expires_in'];
                    $query_auth_info['expired_time']                                   = $time + $api_authorizer_token['expires_in'];
                    $query_auth_info['authorization_state']                            = 'authorized';

                    self::$cacheDriver->_set($query_auth_key, $query_auth_info, 0);

                    return $query_auth_info;
                }
            }
        }

        $authorization_info = self::$cacheDriver->_get($authorization_info_key);

        $query_data = http_build_query(['component_access_token' => $this->getComponentAccessToken()]);

        if ($authorization_info['AuthorizationCodeExpiredTime'] <= $time) {
            $this->setError('授权Code超时');

            return false;
        }
        $request_data = [
            'component_appid'    => $authorization_info['AppId'],
            'authorization_code' => $authorization_info['AuthorizationCode'],
        ];

        $response_data = Http::_post(self::GET_API_QUERY_AUTH . '?' . $query_data, $request_data);

        if (!$response_data) {
            $this->setError(Http::$error);

            return false;
        }

        $response_data['authorization_state'] = 'authorized';
        $response_data['expired_time']        = $time + $response_data['authorization_info']['expires_in'];
        self::$cacheDriver->_set($query_auth_key, $response_data, 0);

        return $response_data;
    }

    /**
     * 获取调用服务号的token
     *
     * @param string $authorized_app_id
     *
     * @return string
     *
     * @throws \WechatOpen\Core\Exception
     */
    public function getAuthorizerAccessToken($authorized_app_id = '')
    {
        if (!self::$authorizer_access_token) {
            $get_api_query_auth = $this->getApiQueryAuth($authorized_app_id);
            if (!$get_api_query_auth) {
                throw new Exception('获取调用服务号Token失败');
            }
            self::$authorizer_access_token = $get_api_query_auth['authorization_info']['authorizer_access_token'];
        }

        return self::$authorizer_access_token;
    }

    /**
     * @return mixed
     */
    public static function getError()
    {
        return self::$error;
    }

    /**
     * @param string $error
     */
    public static function setError($error = '')
    {
        self::$error = $error;
    }
}
