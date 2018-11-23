<?php
namespace System;

/**
 * 服务基类
 * 服务用于实于业务逻辑，提供服务，供控制器(controller)或其它服务(service)调用
 */
abstract class PublicService
{
    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return \System\CacheProxy | Mixed
     */
    public function withCache($expire = 600)
    {
        return new \System\CacheProxy($this, $expire);
    }

}
