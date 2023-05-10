<?php

namespace App\Logging;

/**
 * class CloudWatchPusher
 *
 * @package App\Logging
 */
class CloudWatchPusher
{
    /**
     * @param $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $url = $handler->getUrl();
            if (is_null($url)) {
                break;
            }

            // Create your custom handlers here
            if (config('integrations.cloudwatch.enabled')) {
                $cwHandler = new CloudWatchLoggerHandler();

                // Push it to monolog
                $logger->pushHandler(
                    $cwHandler->getHandler(
                        $this->getStreamFileNameFromPath($url)
                    )
                );
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getStreamFileNameFromPath(string $path): string
    {
        $explodedUrl = explode('storage/logs', $path);
        return gethostname() . $explodedUrl[1];
    }
}
