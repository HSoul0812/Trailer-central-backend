<?php


namespace App\Logging;


use Illuminate\Log\Logger;
use Monolog\Handler\RotatingFileHandler;

// The file is at app/Logging/DailyLogWithUsername.php
//
// This class will be 'tapped' in the daily channel
//
// Laravel will create log file in the format 'laravel-{username}-Y-m-d.log'
//
// This is very useful in Apache or Nginx setup since most of the time there will
// be conflict between user that try to create content to the log file
// 
// For example:
// - Cron job user try to write log to the log file
// - www user (web) try to write error log to the log file
final class DailyLogWithUsername
{
    public function __invoke(Logger $logger)
    {
        try {
            $name = posix_getpwuid(posix_geteuid())['name'];
        } catch (\Exception $e) {
            $name = 'unknown';
        }

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof RotatingFileHandler) {
                $handler->setFilenameFormat("{filename}-$name-{date}", 'Y-m-d');
            }
        }
    }
}