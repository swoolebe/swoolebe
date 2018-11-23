<?php

namespace System\Log\Processor;

use Monolog\Logger;

class SystemProcessor
{

    private $level;
    private $config;

    // 忽略的方法调用
    private $skipFunctions = array(
        'call_user_func',
        'call_user_func_array',
    );

    // 忽略的类调用
    private $skipClasses = array(
        'Phpbe\\System\\Log'
    );


    /**
     * SystemProcessor constructor.
     * @param int $level 默认处理的最低日志级别，低于该级别不处理
     * @param Mixed $config 系统应用中的日志配置项
     */
    public function __construct($level = Logger::DEBUG, $config)
    {
        $this->level = $level;
        $this->config = $config;
    }


    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $config = $this->config;

        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = null;

        $hash = null;
        if (isset($record['file']) && isset($record['line'])) {
            $hash = md5(json_encode([
                'file' => $record['file'],
                'line' => $record['line'],
                'message' => $record['message']
            ]));

            if (isset($record['trace'])) {
                $trace = $record['trace'];
                unset($record['trace']);
            }

        } else {

            $trace = debug_backtrace((PHP_VERSION_ID < 50306) ? 2 : DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            array_shift($trace);

            $i = 0;

            while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
                if (isset($trace[$i]['class'])) {
                    foreach ($this->skipClasses as $part) {
                        if (strpos($trace[$i]['class'], $part) !== false) {
                            $i++;
                            continue 2;
                        }
                    }
                } elseif (in_array($trace[$i]['function'], $this->skipFunctions)) {
                    $i++;
                    continue;
                }

                break;
            }

            $record['extra']['file'] = isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : null;
            $record['extra']['line'] = isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : null;
            $record['extra']['class'] = isset($trace[$i]['class']) ? $trace[$i]['class'] : null;
            $record['extra']['function'] = isset($trace[$i]['function']) ? $trace[$i]['function'] : null;

            $hash = md5(json_encode([
                'file' => $record['extra']['file'],
                'line' => $record['extra']['line'],
                'message' => $record['message']
            ]));
        }

        $record['extra']['hash'] = $hash;

        if (isset($config->trace) && $config->trace) {
            $record['extra']['trace'] = $trace;
        }

        if (isset($config->get) && $config->get) {
            $record['extra']['get'] = &$_GET;
        }

        if (isset($config->post) && $config->post) {
            $record['extra']['post'] = &$_POST;
        }

        if (isset($config->request) && $config->request) {
            $record['extra']['request'] = &$_REQUEST;
        }

        if (isset($config->cookie) && $config->cookie) {
            $record['extra']['cookie'] = &$_COOKIE;
        }

        if (isset($config->session) && $config->session) {
            $record['extra']['session'] = &$_SESSION;
        }

        if (isset($config->server) && $config->server) {
            $record['extra']['server'] = &$_SERVER;
        }

        if (isset($config->memery) && $config->memery) {
            $bytes = memory_get_usage();
            $record['extra']['memory_usage'] = $this->formatBytes($bytes);

            $bytes = memory_get_peak_usage();
            $record['extra']['memory_peak_usage'] = $this->formatBytes($bytes);
        }

        return $record;
    }

    /**
     * 格式化内存占数数字
     *
     * @param int $bytes 整型内存占用量
     * @return string 含单位的容量字符
     */
    protected function formatBytes($bytes)
    {
        $bytes = (int)$bytes;

        if ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . ' MB';
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }


    private function isTraceClassOrSkippedFunction(array $trace, $index)
    {
        if (!isset($trace[$index])) {
            return false;
        }

        return isset($trace[$index]['class']) || in_array($trace[$index]['function'], $this->skipFunctions);
    }
}