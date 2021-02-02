<?php

namespace App\Services\Logs;

use Monolog\Logger;

/**
 * Scrape Replies Log
 */
class ScrapeRepliesLog
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
        $logger = new Logger('scrapereplies');
        $logger->pushHandler(new LogHandler());
        $logger->pushProcessor(new LogProcessor());

        // Return Scrape Replies Logger
        return $logger;
    }
}