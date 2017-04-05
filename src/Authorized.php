<?php

namespace WechatOpen;

use WechatOpen\Core\Core;
use WechatOpen\Core\Http\Http;

class Authorized extends Core
{
    const GET_API_QUERY_AUTH           = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth'; //使用授权码换取公众号的接口调用凭据和授权信息
    const GET_API_AUTHORIZER_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token'; //获取（刷新）授权公众号的接口调用凭据（令牌）

    /**
     * @param $redirect_path
     */
    public function getAuthHTML($redirect_path)
    {
        $component_app_id = $this->configs->component_app_id;
        $pre_auth_code    = $this->getComponentPreAuthCode();
        $redirect_uri     = $this->current() . $redirect_path;

        $editorSrc = <<<HTML
         <script language="JavaScript" type="text/javascript">
           window.location.href="https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_app_id&pre_auth_code=$pre_auth_code&redirect_uri=$redirect_uri";
    </script>
HTML;
        exit($editorSrc);
    }

    public function current()
    {
        $protocol = (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] === 443) ? 'https://' : 'http://';

        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }

        return $protocol . $host . '/';
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

        $query_auth_info = parent::$cacheDriver->_get($query_auth_key);
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

                    parent::$cacheDriver->_set($query_auth_key, $query_auth_info, 0);

                    return $query_auth_info;
                }
            }
        }

        $authorization_info = parent::$cacheDriver->_get($authorization_info_key);

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
        parent::$cacheDriver->_set($query_auth_key, $response_data, 0);

        return $response_data;
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
     * 获取授权服务号 AccessToken
     *
     * @param string $authorizer_app_id
     *
     * @return bool
     */
    public function getAuthorizerAccessToken($authorizer_app_id = '')
    {
        $query_auth_info = $this->getApiQueryAuth($authorizer_app_id);

        if (!empty($query_auth_info)) {

            if ($query_auth_info['authorization_state'] == 'authorized') {
                return $query_auth_info['authorization_info']['authorizer_access_token'];
            } else {
                $this->setError('已经取消授权的服务号:' . $query_auth_info['authorization_info']['authorizer_appid']);

                return false;
            }
        }

        return false;
    }
}