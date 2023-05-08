<?php

namespace App\Logging;

use App\Exceptions\NotImplementedException;
use Monolog\Handler\HandlerInterface;
use Maxbanton\Cwh\Handler\CloudWatch;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

/**
 * class CloudWatchLoggerHandler
 *
 * @package App\Logging
 */
class CloudWatchLoggerHandler implements HandlerInterface
{
    /**
     * Create a custom Monolog instance.
     *
     * @param string $channel
     * @return CloudWatch
     */
    public function getHandler(string $channel): CloudWatch
    {
        $sdkParams = config('integrations.cloudwatch.sdk');
        $tags = config('integrations.cloudwatch.tags') ?? [ ];

        // Instantiate AWS SDK CloudWatch Logs Client
        $client = new CloudWatchLogsClient($sdkParams);

        // Log group name, will be created if none
        $groupName = config('integrations.cloudwatch.group_name') ?? config('app.name') . '-' . config('app.env');

        // Log stream name, will be created if none
        $streamName = $channel ?? config('integrations.cloudwatch.stream_name') ?? null;

        // Days to keep logs, 14 by default. Set to `null` to allow indefinite retention.
        $retentionDays = config('integrations.cloudwatch.retention') ?? null;

        // Instantiate handler (tags are optional)
        return new CloudWatch($client, $groupName, $streamName, $retentionDays, 10000, $tags);
    }

    public function isHandling(array $record): bool
    {
        return NotImplementedException::class;
    }

    public function handle(array $record): bool
    {
        return NotImplementedException::class;
    }

    public function handleBatch(array $records): void
    {
        //
    }

    public function close(): void
    {
        //
    }
}
