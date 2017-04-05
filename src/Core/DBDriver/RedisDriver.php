<?php

namespace WechatOpen\Core\DBDriver;

use Predis\Client;
use WechatOpen\Core\Exceptions\ConfigMistakeException;

/**
 * 文件缓存驱动.
 *
 */
class RedisDriver extends BaseDriver
{
    /** @var  \Predis\Client $redis */
    private static $redis;
    private        $pre;

    public function __construct(array $options, $pre = '')
    {
        parent::__construct($options, $pre);

        $this->pre = $pre;

        $options += ['host' => '127.0.0.1', 'port' => '6379', 'database' => '0', 'scheme' => 'tcp', 'auth' => null];

        if (!self::$redis) {
            ini_set('default_socket_timeout', 50);//socket连接超时时间;

            self::$redis = new Client([
                'scheme' => $options['scheme'],
                'host'   => $options['host'],
                'port'   => $options['port'],
            ]);

            if (!empty($options['auth'])) {
                self::$redis->auth($options['auth']);
            }

            self::$redis->select($options['database']);
        }

        if (!self::$redis) {
            throw new ConfigMistakeException('Redis初始化连接失败-database');
        }
    }

    /**
     * 根据缓存名获取缓存内容.
     *
     * @param string $name
     *
     * @return bool|mixed|string
     */
    public function _get($name)
    {
        $name = $this->createFileName($name);

        $data = self::$redis->get($name);

        return $data;
    }

    /**
     * 根据缓存名 设置缓存值和超时时间.
     *
     * @param string $name    缓存名
     * @param void   $value   缓存值
     * @param int    $expires 超时时间
     *
     * @return boolean;
     */
    public function _set($name, $value, $expires = 0)
    {
        $name = $this->createFileName($name);

        if (is_int($expires) && $expires && $expires > 0) {
            $result = self::$redis->setex($name, $expires, $value);
        } else {
            $result = self::$redis->set($name, $value);
        }

        return $result;
    }

    /**
     * 数据打包.
     *
     * @param void $data 缓存值
     *
     * @return string
     */
    private function packData($data, $expires)
    {
        $str            = [];
        $str['data']    = $data;
        $str['expires'] = $expires === 0 ? 0 : time() + $expires;
        $str            = serialize($str);

        return serialize($str);
    }

    /**
     * 数据解包.
     *
     * @param $data
     *
     * @return mixed
     */
    private function unpackData($data)
    {
        $arr = unserialize($data);

        if (time() > $arr['expires'] && $arr['expires'] !== 0) {

            return false;
        }

        return $arr['data'];
    }

    /**
     * 创建缓存文件名.
     *
     * @param string $name 缓存名
     *
     * @return string
     */
    private function createFileName($name)
    {
        return $this->pre . md5($name);
    }
}
