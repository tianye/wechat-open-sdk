<?php

namespace WechatOpen;

use WechatOpen\Core\Core;

class Authorized extends Core
{

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