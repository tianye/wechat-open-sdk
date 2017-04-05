<?php
namespace OpenOauth\Core;

use OpenOauth\Core\DBDriver\BaseDriver;
use OpenOauth\Core\DBDriver\DBInter;

/**
 * Class DB
 *
 * @method static \OpenOauth\Core\DBDriver\BaseDriver|\OpenOauth\Core\DBDriver\DBInter init(array $options, $dir = '');
 * @method static \OpenOauth\Core\DBDriver\BaseDriver|\OpenOauth\Core\DBDriver\DBInter _get($name);
 * @method static \OpenOauth\Core\DBDriver\BaseDriver|\OpenOauth\Core\DBDriver\DBInter _set($name, $value, $expires = null);
 *
 */
class DB
{
    public static $driver_container;

    public static $driver;

    /**
     * DB constructor.
     *
     * @param BaseDriver $class DB驱动
     */
    public function __construct(BaseDriver $class)
    {
        static::$driver = $class;
    }

    /**
     * @param string $method    静态调用方法
     * @param mixed  $arguments 参数
     *
     * @throws \Exception
     * @return array|bool
     */
    public static function __callStatic($method, $arguments)
    {
        if (!static::$driver instanceof DBInter) {
            throw new Exception('请先 初始化');
        }

        return call_user_func_array([static::$driver, $method], $arguments);
    }

    public function __call($method, $arguments)
    {
        if (!static::$driver instanceof DBInter) {
            throw new Exception('请先 初始化');
        }

        return call_user_func_array([static::$driver, $method], $arguments);
    }
}