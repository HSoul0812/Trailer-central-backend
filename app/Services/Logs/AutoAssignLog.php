<?php

namespace App\Services\Logs;

use Monolog\Logger;

/**
 * Auto Assign Log
 */
class AutoAssignLog
{
    /**
    * This class will create a custom Monolog instance.
    *
    * @param array $config
    * @return MonologLogger
    */
    public function __invoke(array $config)
    {
        // Initialize Logger
        $logger = new Logger('autoassign');
        $logger->pushHandler(new LogHandler());
        $logger->pushProcessor(new LogProcessor());

        // Return Scrape Replies Logger
        return $logger;
    }
}