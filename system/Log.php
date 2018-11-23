<?php

namespace System;

use Monolog\Logger;
use System\Log\Handler\SystemHandler;
use System\Log\Processor\SystemProcessor;

/**
 * 日志类
 */
class Log
{

    private static $logger = null;


    /**
     *
     * @return Logger
     */
    private static function getLogger()
    {
        if (self::$logger === null) {

            $config = Be::getConfig('System', 'Log');

            $level = Logger::DEBUG;
            if (isset($config->level)) {
                switch ($config->level) {
                    case 'debug':
                        $level = Logger::DEBUG;
                        break;
                    case 'info':
                        $level = Logger::INFO;
                        break;
                    case 'notice':
                        $level = Logger::NOTICE;
                        break;
                    case 'warning':
                        $level = Logger::WARNING;
                        break;
                    case 'error':
                        $level = Logger::ERROR;
                        break;
                    case 'critical':
                        $level = Logger::CRITICAL;
                        break;
                    case 'alert':
                        $level = Logger::ALERT;
                        break;
                    case 'emergency':
                        $level = Logger::EMERGENCY;
                        break;
                }
            }

            $logger = new Logger('Be');

            $handler = new SystemHandler($level);
            $logger->pushHandler($handler);

            $processor = new SystemProcessor($level, $config);
            $logger->pushProcessor($processor);

            self::$logger = $logger;
        }

        return self::$logger;
    }


    public static function debug($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::DEBUG, $message, $context);
    }

    public static function info($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::INFO, $message, $context);
    }

    public static function notice($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::NOTICE, $message, $context);
    }

    public static function warning($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::WARNING, $message, $context);
    }

    public static function error($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::ERROR, $message, $context);
    }

    public static function critical($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::CRITICAL, $message, $context);
    }

    public static function alert($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::ALERT, $message, $context);
    }

    public static function emergency($message, array $context = array())
    {
        return self::getLogger()->addRecord(Logger::EMERGENCY, $message, $context);
    }

}
