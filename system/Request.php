<?php

namespace System;

/**
 * Request
 */
class Request
{

    /**
     * @var \Swoole\Http\Request
     */
    private $request = null;


    public function __construct(\Swoole\Http\Request $request)
    {
        $this->request = $request;
    }


    public function method()
    {
        return $this->request->server['REQUEST_METHOD'];
    }

    public function isGet()
    {
        return 'GET' == $this->request->server['REQUEST_METHOD'] ? true : false;
    }

    public function isPost()
    {
        return 'POST' == $this->request->server['REQUEST_METHOD'] ? true : false;
    }


    /**
     * 获取请求者的 IP 地址
     *
     * @param bool $detectProxy 是否检测代理服务器
     * @return string
     */
    public function ip($detectProxy = true)
    {
        if ($detectProxy) {
            if (isset( $this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $pos = strpos( $this->request->server['HTTP_X_FORWARDED_FOR'], ',');

                $ip = null;
                if (false !== $pos) {
                    $ip = substr( $this->request->server['HTTP_X_FORWARDED_FOR'], 0, $pos);
                } else {
                    $ip =  $this->request->server["HTTP_X_FORWARDED_FOR"];
                }

                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return  $this->request->server['remote_addr'];
    }

    /**
     * 获取 $_GET 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function get($name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->get, $name, $default, $format);
    }

    /**
     * 获取 $_POST 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function post($name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->post, $name, $default, $format);
    }

    /**
     * 获取 $_REQUEST 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function request($name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->request, $name, $default, $format);
    }

    /**
     * 获取 $_SERVER 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function server($name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->server, $name, $default, $format);
    }

    /**
     * 获取 $_COOKIE 数据
     * @param string|null $name 参数量
     * @param string|null $default 默认值
     * @param string|\Closure $format 格式化
     * @return array|mixed|string
     */
    public function cookie($name = null, $default = null, $format = 'string')
    {
        return $this->_request($this->request->cookie, $name, $default, $format);
    }

    /**
     * 获取上传的文件
     * @param string|null $name 参数量
     * @return array|null
     */
    public function files($name = null)
    {
        if ($name === null) {
            return $this->request->files;
        }

        if (!isset($this->request->files[$name])) return null;

        return $this->request->files[$name];
    }

    private function _request($input, $name, $default, $format)
    {
        if ($name === null) {
            if ($format instanceof \Closure) {
                $input = $this->formatByClosure($input, $format);
            } else {
                $fnFormat = 'format' . ucfirst($format);
                $input = $this->$fnFormat($input);
            }

            return $input;
        }

        if (!isset($input[$name])) return $default;
        $value = $input[$name];

        if ($format instanceof \Closure) {
            return $this->formatByClosure($value, $format);
        } else {
            $fnFormat = 'format' . ucfirst($format);
            return $this->$fnFormat($value);
        }
    }

    private function formatInt($value)
    {
        return is_array($value) ? array_map(array($this, 'formatInt'), $value) : intval($value);
    }

    private function formatFloat($value)
    {
        return is_array($value) ? array_map(array($this, 'formatFloat'), $value) : floatval($value);
    }

    private function formatBool($value)
    {
        return is_array($value) ? array_map(array($this, 'formatBool'), $value) : boolval($value);
    }

    private function formatString($value)
    {
        return is_array($value) ? array_map(array($this, 'formatString'), $value) : htmlspecialchars($value);
    }

    // 过滤  脚本,样式，框架
    private function formatHtml($value)
    {
        if (is_array($value)) {
            return array_map(array($this, 'formatHtml'), $value);
        } else {
            $value = preg_replace("@<script(.*?)</script>@is", '', $value);
            $value = preg_replace("@<style(.*?)</style>@is", '', $value);
            $value = preg_replace("@<iframe(.*?)</iframe>@is", '', $value);

            return $value;
        }
    }

    /**
     * 格式化 IP
     * @param $value
     * @return array|string
     */
    private function formatIp($value)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = $this->formatIp($v);
            }
            return $returns;
        } else {
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            } else {
                return 'invalid';
            }
        }
    }

    private function format($value)
    {
        return $value;
    }

    private function formatNull($value)
    {
        return $value;
    }

    private function formatByClosure($value, \Closure $closure)
    {
        if (is_array($value)) {
            $returns = [];
            foreach ($value as $v) {
                $returns[] = $this->formatByClosure($v, $closure);
            }
            return $returns;
        } else {
            return $closure($value);
        }

    }

    /*
     * 封装 setXxx 方法
     */
    public function __call($fn, $args)
    {
        return call_user_func_array(array($this->request, $fn), $args);
    }
}

