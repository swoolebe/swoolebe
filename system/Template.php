<?php

namespace System;

/**
 * 模板基类
 */
class Template
{
    public $title = ''; // 标题
    public $metaKeywords = ''; // meta keywords
    public $metaDescription = '';  // meta description

    /*
    网站主色调
    数组 10 个元素
    下标（index）：0, 1, 2, 3, 4, 5, 6, 7, 8, 9，
    主颜色: $this->colors[0], 模板主要颜色，
    其它颜色 依次减淡 10%, 即 ([index]*10)%

    可以仅有一个元素 即 $this->colors[0], 指定下标不存在时自动跟据主颜色按百分比换算。
    */
    public $colors = array('#333333');


    public function get($key, $default = null)
    {
        if (isset($this->$key)) return $this->$key;
        return $default;
    }

    public function getColor($index = 0)
    {
        if (isset($this->colors[$index])) return $this->colors[$index];
        return $this->colors[0];
    }

    /**
     * 输出函数
     */
    public function display()
    {
    }

    /**
     *
     * <head></head>头可加 js/css
     */
    protected function head()
    {
    }

    /**
     * 主体
     */
    protected function body()
    {
    }

    /**
     * 项部
     */
    protected function north()
    {
    }

    /**
     * 中间
     */
    protected function middle()
    {
    }

    /**
     * 中间 - 左
     */
    protected function west()
    {
    }

    /**
     * 中间 - 中
     */
    protected function center()
    {
    }

    /**
     * 中间 - 右
     */
    protected function east()
    {
    }

    /**
     * 底部
     */
    protected function south()
    {
    }


}
