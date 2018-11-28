<?php
namespace System\Redis;

/**
 * Redis 类
 */
class Pool
{

    private static $instances = []; // 数据库连接


    public static function getInstance($redis) {

        if (isset(self::$instances[$redis]) && count(self::$instances[$redis]) > 0) {
            return array_shift(self::$instances[$redis]);
        }



        $config = Be::getConfig('Redis');
        if (!isset($config->$redis)) {
            throw new RuntimeException('Redis配置项（' . $redis . '）不存在！');
        }

        self::$cache['Redis'][$redis] = new \System\Redis\Driver($config->$redis);
        return self::$cache['Redis'][$redis];

    }



    /**
     * 连接数据库
     *
     * @throws RedisException
     */
    public function connect()
    {
        if ($this->instance === null) {

            if (!extension_loaded('Redis')) throw new RedisException('服务器未安装 Redis 扩展！');

            $config = $this->config;

            $instance = new Swoole\Coroutine\Redis();
            $fn = $config['persistent'] ? 'pconnect' : 'connect';
            if ($config['timeout'] > 0)
                $instance->$fn($config['host'], $config['port'], $config['timeout']);
            else
                $instance->$fn($config['host'], $config['port']);
            if ('' != $config['password']) $instance->auth($config['password']);
            if (0 != $config['db']) $instance->select($config['db']);

            $this->instance = $instance;
        }
    }

}
