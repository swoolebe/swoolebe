<?php

namespace System;

/**
 * Response
 * @package System
 *
 * @method void setTitle(string $title) static 设置 title
 * @method void setMetaKeywords(string $metaKeywords)  static 设置 meta keywords
 * @method void setMetaDescription(string $metaDescription)  static 设置 meta description
 */
class Response
{
    private $data = array(); // 暂存数据


    /**
     * @var \Swoole\Http\Response
     */
    private $response = null;


    public function __construct(\Swoole\Http\Response $response)
    {
        $this->response = $response;
    }


    /**
     * 设置暂存数据
     * @param string $name 名称
     * @param mixed $value 值 (可以是数组或对象)
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 获取暂存数据
     *
     * @param string $name 名称
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->data[$name])) return $this->data[$name];
        return $default;
    }

    /**
     * 以 JSON 输出暂存数据
     */
    public function ajax()
    {
        header('Content-type: application/json');
        echo json_encode($this->data);
        exit();
    }

    /**
     * 成功
     *
     * @param string $message 消息
     */
    public function success($message)
    {
        $this->set('success', true);
        $this->set('message', $message);
        $this->ajax();
    }

    /**
     * 失败
     *
     * @param string $message 消息
     * @param int $code 错误码
     */
    public function error($message, $code = 1)
    {
        $this->set('success', false);
        $this->set('message', $message);
        $this->set('code', $code);
        $this->ajax();
    }

    /**
     * 显示模板
     *
     * @param string $template 模板名
     * @param string $theme 主题名
     */
    public function display($template = null, $theme = null)
    {
        ob_start();
        ob_clean();
        $templateInstance = Be::getTemplate($template, $theme);
        foreach ($this->data as $key => $val) {
            $templateInstance->$key = $val;
        }
        $templateInstance->display();
        $content = ob_get_contents();
        ob_end_clean();
        $this->response->end($content);
    }

    /**
     * 输出
     *
     * @param string $string 输出内空
     */
    public function write($string = '')
    {
        $this->response->write($string);
    }

    /**
     * 结束输出
     *
     * @param string $string 输出内空
     */
    public function end($string = '')
    {
        $this->response->end($string);
    }


    /**
     * 结束输出
     *
     * @param string $string 输出内空
     */
    public function show($string = null)
    {
        if ($string === null) {
            $this->response->end();
        } else {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div style="padding:10px;text-align:center;">' . $string . '</div></body></html>';
            $this->response->end($html);
        }
    }

    /*
     * 封装 setXxx 方法
     */
    public function __call($fn, $args)
    {
        if (substr($fn, 0, 3) == 'set' && count($args) == 1) {
            $this->data[lcfirst(substr($fn, 3))] = $args[0];
            return true;
        } else {
            return call_user_func_array(array($this->response, $fn), $args);
        }
    }

}

