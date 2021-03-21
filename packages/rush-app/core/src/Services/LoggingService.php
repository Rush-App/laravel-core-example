<?php

namespace RushApp\Core\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingService
{
    /**
     * @param string $message
     * @param $level
     */
    public static function auth(mixed $message, int $level): void
    {
        $logger = self::getLogger(
            __FUNCTION__,
            config('boilerplate.log_paths.auth'),
            $level
        );

        $logger->log($level, $message);
    }

    public static function debug(mixed $message): void
    {
        self::logCore($message, Logger::DEBUG);
    }

    public static function info(mixed $message): void
    {
        self::logCore($message, Logger::INFO);
    }

    public static function notice(mixed $message): void
    {
        self::logCore($message, Logger::NOTICE);
    }

    public static function warning(mixed $message): void
    {
        self::logCore($message, Logger::WARNING);
    }

    public static function error(mixed $message): void
    {
        self::logCore($message, Logger::ERROR);
    }

    public static function critical(mixed $message): void
    {
        self::logCore($message, Logger::CRITICAL);
    }

    public static function alert(mixed $message): void
    {
        self::logCore($message, Logger::ALERT);
    }

    public static function emergency(mixed $message): void
    {
        self::logCore($message, Logger::EMERGENCY);
    }

    protected static function logCore(mixed $message, int $level)
    {
        $levelName = strtolower(Logger::getLevelName($level));

        $logger = self::getLogger(
            $levelName,
            config('boilerplate.log_paths.core'),
            $level
        );

        $logger->$levelName($message);
    }

    /**
     * @param string $loggerName
     * @param string $path
     * @param int $level
     * @return Logger
     */
    protected static function getLogger(string $loggerName, string $path, int $level): Logger
    {
        $log = new Logger($loggerName);
        $log->pushHandler(new StreamHandler(storage_path($path), $level));

        return $log;
    }
}


