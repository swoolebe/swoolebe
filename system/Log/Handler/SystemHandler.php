<?php

namespace System\Log\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use System\Be;

class SystemHandler extends AbstractProcessingHandler
{


    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
    }


    // 日志存储实现
    protected function write(array $record)
    {
        $t = time();

        $year = date('Y', $t);
        $month = date('m', $t);
        $day = date('d', $t);

        $dir = Be::getRuntime()->getDataPath() . '/System/Log/' .  $year . '/' . $month . '/' . $day . '/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $logFileName = null;
        if (isset($record['extra']['hash'])) {
            $logFileName = $record['extra']['hash'];
        } else {
            $logFileName = md5(json_encode($record)); // 相同错误只存储一次
        }

        $logFilePath = $dir . $logFileName;

        if (!file_exists($logFilePath)) {
            $record['record_time'] = $t;
            file_put_contents($logFilePath, json_encode($record));
        }

        $indexFilePath = $dir . 'index';
        $f = fopen($indexFilePath, 'ab+');
        if ($f) {
            fwrite($f, pack('H*', $logFileName));
            fwrite($f, pack('L', $t));
            fclose($f);
        }
    }

}