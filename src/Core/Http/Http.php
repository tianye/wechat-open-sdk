<?php
namespace OpenOauth\Core\Http;

use GuzzleHttp\Client;

class Http
{
    /** @var  Client */
    public static $http;

    public static $error;

    public function __construct($http)
    {
        self::$http = $http;
    }

    public static function _post($apiUrl, $data)
    {
        try {
            if (version_compare(Client::VERSION, '6.0.0', 'ge')) {
                $response = self::$http->post($apiUrl, ['json' => $data]);
            } else {
                $response = self::$http->post($apiUrl, ['body' => $data]);
            }
        } catch (\Exception $e) {
            $response['err_code']               = '-1';
            $response['err_msg']                = 'CURL 请求失败';
            $response['body']['return_code']    = 'FAIL';
            $response['body']['return_msg']     = $e->getMessage();
            $response['body']['result_code']    = 'FAIL';
            $response['body']['err_code']       = 'CURL_ERROR';
            $response['body']['err_code_des']   = $e->getMessage();
            $response['body']['exception_code'] = $e->getCode();

            return $response;
        }

        $result           = [];
        $result['info']   = $response->getBody()->getContents();
        $result['header'] = $response->getHeaders();
        $result['status'] = $response->getStatusCode();

        return self::packData($result);
    }

    public static function _get($apiUrl)
    {
        try {
            if (version_compare(Client::VERSION, '6.0.0', 'ge')) {
                $response = self::$http->get($apiUrl);
            } else {
                $response = self::$http->get($apiUrl);
            }
        } catch (\Exception $e) {
            $response['err_code']               = '-1';
            $response['err_msg']                = 'CURL 请求失败';
            $response['body']['return_code']    = 'FAIL';
            $response['body']['return_msg']     = $e->getMessage();
            $response['body']['result_code']    = 'FAIL';
            $response['body']['err_code']       = 'CURL_ERROR';
            $response['body']['err_code_des']   = $e->getMessage();
            $response['body']['exception_code'] = $e->getCode();

            return $response;
        }

        $result           = [];
        $result['info']   = $response->getBody()->getContents();
        $result['header'] = $response->getHeaders();
        $result['status'] = $response->getStatusCode();

        return self::packData($result);
    }

    /**
     * 对接口返回的数据进行验证和组装.
     *
     * @author Tian
     *
     * @date   2015-12-08
     *
     * @param array $apiReturnData 由_post|| _get方法返回的数据.
     *
     * @return array|bool
     */
    private static function packData($apiReturnData)
    {
        $status     = $apiReturnData['status'];
        $returnData = $apiReturnData['info'];

        if ($status != 200 && empty($returnData)) {
            self::$error = '接口服务器连接失败.';

            return false;
        }

        $apiReturnData = json_decode($returnData, true);

        if ($status != 200 && !$apiReturnData) {
            self::$error = $returnData;

            return false;
        }

        if (isset($apiReturnData['errcode']) && $apiReturnData['errcode'] != 0) {
            $error = '错误码:' . $apiReturnData['errcode'] . ', 错误信息:' . $apiReturnData['errmsg'];

            self::$error = $error;

            return false;
        }

        return $apiReturnData;
    }
}