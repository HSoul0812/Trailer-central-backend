<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class CloudWatchPusher
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $url = $handler->getUrl();
            if (is_null($url)) {
                break;
            }

            // Create your custom handler
            $cwHandler = new CloudWatchLoggerHandler();

            Log::channel('single')->info('We pushim');

            // Push it to monolog
            $logger->pushHandler(
                $cwHandler->getHandler(
                    $this->getStreamFileNameFromPath($url)
                )
            );

            Log::channel('single')->info('We pushe to ' . $this->getStreamFileNameFromPath($url));
        }
    }

    private function getStreamFileNameFromPath(string $path)
    {
        $explodedUrl = explode('storage/logs', $path);
        return $explodedUrl[1];
    }
}
