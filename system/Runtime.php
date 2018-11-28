<?php

namespace System;

/**
 *  运行时
 * @package System
 *
 */
class Runtime
{
    private $rootPath = null;

    private $dataDir = 'data';

    private $cacheDir = 'cache';

    public function __construct()
    {
    }

    /**
     * 设置BE框架的根路径
     *
     * @param string $rootPath BE框架的根路径，绝对路径
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * 获取BE框架的根路径
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->rootPath . '/' . $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->rootPath . '/' . $this->dataDir;
    }


    /**
     * @param string $dataDir
     */
    public function setDataDir($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * @return string
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }


    public function execute()
    {
        $httpService = new HttpServer();
        $httpService->start();
    }
}
