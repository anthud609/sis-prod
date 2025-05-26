<?php

namespace App\Core;

class Logger
{
    private static string $logFile = '';
    
    public static function init(): void
    {
        self::$logFile = __DIR__ . '/../../app.log';
    }
    
    public static function debug(string $message): void
    {
        self::log('DEBUG', $message);
    }
    
    public static function info(string $message): void
    {
        self::log('INFO', $message);
    }
    
    public static function error(string $message): void
    {
        self::log('ERROR', $message);
    }
    
    private static function log(string $level, string $message): void
    {
        if (empty(self::$logFile)) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}