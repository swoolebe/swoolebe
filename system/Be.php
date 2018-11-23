<?php

namespace System;

/**
 *  BE系统资源工厂
 * @package System
 *
 */
abstract class Be
{

    private static $cache = []; // 缓存资源实例

    private static $version = '1.0.0'; // 系统版本号

    /**
     * @var Runtime
     */
    private static $runtime = null; // 系统运行时


    /**
     * 获取数据库对象
     *
     * @param string $db 数据库名
     * @return \System\Db\Driver
     * @throws RuntimeException
     */
    public static function getDb($db = 'master')
    {
        $key = 'Db:' . $db;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = Be::getConfig('Db');
        if (!isset($config->$db)) {
            throw new \RuntimeException('数据库配置项（' . $db . '）不存在！');
        }

        $config = $config->$db;

        $class = 'Phpbe\\System\\Db\\Driver\\' . $config['driver'] . 'Impl';
        if (!class_exists($class)) throw new \RuntimeException('数据库配置项（' . $db . '）指定的数据库驱动' . $config['driver'] . '不支持！');

        self::$cache[$key] = new $class($config);
        return self::$cache[$key];
    }

    /**
     * 获取Redis对象
     *
     * @param string $redis Redis名
     * @return \System\Redis\Driver
     * @throws RuntimeException
     */
    public static function getRedis($redis = 'master')
    {
        $key = 'Redis:' . $redis;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = Be::getConfig('Redis');
        if (!isset($config->redis)) {
            throw new RuntimeException('Redis配置项（' . $redis . '）不存在！');
        }

        self::$cache[$key] = new \System\Redis\Driver($config->$redis);
        return self::$cache[$key];
    }

    /**
     * 获取MongoDB对象
     *
     * @param string $mongoDB MongoDB名
     * @return \System\Redis\Driver
     * @throws RuntimeException
     */
    public static function getMongoDB($mongoDB = 'master')
    {
        $key = 'MongoDB:' . $mongoDB;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $config = Be::getConfig('MongoDB');
        if (!isset($config->mongoDB)) {
            throw new RuntimeException('MongoDB配置项（' . $mongoDB . '）不存在！');
        }

        self::$cache[$key] = new \System\MongoDB\Driver($config->$mongoDB);
        return self::$cache[$key];
    }

    /**
     * 获取指定的库
     *
     * @param string $lib 库名，可指定命名空间，调用第三方库
     * @return Lib | mixed
     * @throws RuntimeException
     */
    public static function getLib($lib)
    {
        $class = null;
        if (strpos($lib, '\\') === false) {
            $class = 'Phpbe\\Lib\\' . $lib . '\\' . $lib;
        } else {
            $class = $lib;
        }

        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new RuntimeException('库 ' . $class . ' 不存在！');

        self::$cache[$class] = new $class();
        return self::$cache[$class];
    }

    /**
     * 获取指定的配置文件
     *
     * @param string $config 配置文件名
     * @return mixed
     * @throws RuntimeException
     */
    public static function getConfig($config)
    {
        $key = 'Config:' . $config;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Data\\Runtime\\Config\\' . $config;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        $class = 'Config\\' . $config;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        throw new RuntimeException('配置文件 ' . $config . ' 不存在！');
    }

    /**
     * 获取指定的一个服务
     *
     * @param string $service 服务名
     * @return Service | mixed
     * @throws RuntimeException
     */
    public static function getService($service)
    {
        $key = 'Service:' . $service;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'Service\\' . $service;

        if (!class_exists($class)) throw new RuntimeException('服务 ' . $service . ' 不存在！');

        $instance = new $class();
        if ($instance instanceof PublicService) {
            self::$cache[$class] = $instance;
        }

        return $instance;
    }

    /**
     * 获取指定的一个数据库行记灵对象
     *
     * @param string $tupple 数据库行记灵对象名
     * @return \System\Db\Tuple | mixed
     * @throws RuntimeException
     */
    public static function getTuple($tupple)
    {
        $class = 'Tuple\\' . $tupple;
        if (class_exists($class)) return (new $class());

        $class = 'Cache\\Runtime\\Tuple\\' . $tupple;
        //if (class_exists($class)) return (new $class());

        $path = self::$runtime->getCachePath() . '/Runtime/App/Tuple/' . $tupple . '.php';
        if (!file_exists($path)) {
            $service = Be::getService('Db');
            $service->updateRow($tupple);
            include_once $path;
        }

        if (!class_exists($class)) {
            throw new RuntimeException('行记灵对象 ' . $tupple . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个数据库表对象
     *
     * @param string $table 表名
     * @return \System\Db\Table
     * @throws RuntimeException
     */
    public static function getTable($table)
    {
        $class = 'Table\\' . $table;
        if (class_exists($class)) return (new $class());

        $class = 'Cache\\Runtime\\Table\\' . $table;
        //if (class_exists($class)) return (new $class());

        $path = self::$runtime->getCachePath() . '/Runtime/App/Table/' . $table . '.php';
        if (!file_exists($path)) {
            $service = Be::getService('Db');
            $service->updateTable($table);
            include_once $path;
        }

        if (!class_exists($class)) {
            throw new RuntimeException('表对象 ' . $table . ' 不存在！');
        }

        return (new $class());
    }

    /**
     * 获取指定的一个数据库表配置
     *
     * @param string $table 表名
     * @return \System\Db\TableConfig
     * @throws RuntimeException
     */
    public static function getTableConfig($table)
    {
        $key = 'TableConfig:' . $table;
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $class = 'TableConfig\\' . $table;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        $class = 'Data\\Runtime\\TableConfig\\' . $table;
        if (class_exists($class)) {
            self::$cache[$key] = new $class();;
            return self::$cache[$key];
        }

        return new \System\Db\TableConfig();
    }

    /**
     * 获取指定的控制器
     *
     * @param string $controller 控制器名
     * @return Controller | mixed
     * @throws RuntimeException
     */
    public static function getController($controller)
    {
        $class = 'Controller\\' . $controller;
        if (isset(self::$cache[$class])) return self::$cache[$class];

        if (!class_exists($class)) throw new RuntimeException('控制器 ' . $controller . ' 不存在！');

        $instance = new $class();
        if ($instance instanceof PublicController) {
            self::$cache[$class] = $instance;
        }

        return $instance;
    }

    /**
     * 清除工厂缓存数据
     *
     * @param string $key 指定缓存key，未指定时清除所有缓存数据
     */
    public static function cleanCache($key = null)
    {
        if ($key === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$key]);
        }
    }

    /**
     * 获取系统版本号
     *
     * @return string
     */
    public static function getVersion()
    {
        return self::$version;
    }


    /**
     * @return Runtime
     */
    public static function getRuntime()
    {
        if (self::$runtime == null) {
            self::$runtime = new Runtime();
        }
        return self::$runtime;
    }
}
