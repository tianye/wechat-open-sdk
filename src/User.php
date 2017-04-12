<?php
namespace WechatOpen;

use WechatOpen\Core\Core;
use WechatOpen\Core\Http\Http;

/**
 * 微信Auth相关接口.
 *
 * @author Tian.
 */
class User extends Core
{
    const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/user/info =OPENID&lang=zh_CN';

    /**
     * AuthApi constructor.
     *
     * @param $authorized_app_id
     */
    public function __construct($authorized_app_id = '')
    {
        parent::__construct();
    }

    /**
     * 开发者可通过OpenID来获取用户基本信息
     *
     * @param        $openid
     * @param string $lang
     *
     * @return array|bool|\Psr\Http\Message\ResponseInterface
     */
    public function getUser($openid, $lang = 'zh_CN')
    {

        $queryStr = [
            'openid'       => $openid,
            'lang'         => $lang,
            'access_token' => $this->getAuthorizerAccessToken(),
        ];

        $query_data = http_build_query($queryStr);

        $res = Http::_get(self::USER_INFO_URL . '?' . $query_data);

        return $res;
    }
}
