<?php

namespace RushApp\Core\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingService
{
    /**
     * @var array
     */
    protected static array $loggersList = [
        'authLogging' => [
            'name' => 'authLogging',
            'path' => 'logs/auth-process.log'
        ],
        'sendMessageLogging' => [
            'name' => 'sendMessageLogging',
            'path' => 'logs/send-message.log'
        ],
        'forFilesLogging' => [
            'name' => 'forFilesLogging',
            'path' => 'logs/for-files.log'
        ],
        'CRUD_errorsLogging' => [
            'name' => 'CRUD_errorsLogging',
            'path' => 'logs/crud-errors.log'
        ]
    ];

    /**
     * @param string $message
     * @param $level
     */
    public static function authLogging (string $message, int $level): void
    {
        self::addLoggerMessage(
            self::$loggersList['authLogging']['name'],
            self::$loggersList['authLogging']['path'],
            $level,
            $message
        );
    }

    /**
     * @param string $message
     * @param $level
     */
    public static function sendMessageLogging (string $message, int $level): void
    {
        self::addLoggerMessage(
            self::$loggersList['sendMessageLogging']['name'],
            self::$loggersList['sendMessageLogging']['path'],
            $level,
            $message
        );
    }

    /**
     * @param string $message
     * @param $level
     */
    public static function forFilesLogging (string $message, int $level): void
    {
        self::addLoggerMessage(
            self::$loggersList['forFilesLogging']['name'],
            self::$loggersList['forFilesLogging']['path'],
            $level,
            $message
        );
    }

    /**
     * @param string $message
     * @param $level
     */
    public static function CRUD_errorsLogging (string $message, int $level): void
    {
        self::addLoggerMessage(
            self::$loggersList['CRUD_errorsLogging']['name'],
            self::$loggersList['CRUD_errorsLogging']['path'],
            $level,
            $message
        );
    }

    /**
     * @param string $loggerName
     * @param string $path
     * @param $level
     * @param string $message
     */
    public static function addLoggerMessage(string $loggerName, string $path, $level, string $message): void {
        $log = new Logger($loggerName);
        $log->pushHandler(new StreamHandler(storage_path($path), $level));

        switch ($level) {
            case Logger::DEBUG:
                $log->debug($message);
                break;
            case Logger::INFO:
                $log->info($message);
                break;
            case Logger::NOTICE:
                $log->notice($message);
                break;
            case Logger::WARNING:
                $log->warning($message);
                break;
            case Logger::ERROR:
                $log->error($message);
                break;
            case Logger::CRITICAL:
                $log->critical($message);
                break;
            case Logger::ALERT:
                $log->alert($message);
                break;
            case Logger::EMERGENCY:
                $log->emergency($message);
                break;
        }
    }
}

