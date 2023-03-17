<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Redis as RedisClient;

/**
 * @see https://github.com/laravel/horizon/issues/715
 *
 * Somehow Horizon is not trimming completed jobs, thus memory is always up to 5GB on production.
 *
 * This command cleanup Redis memory
 */
class HorizonCleanCompletedJobs extends Command
{
    private const COMPLETED = 'completed';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:clean-completed-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all completed jobs';

    public function handle()
    {
        /** @var RedisClient $client */
        $client = Redis::connection('horizon')->client();

        $cursor = null;

        $deletedJobs = 0;

        while (false !== ($keys = $client->scan($cursor, 'horizon:[0-9]*', 50))) {

            foreach ($keys as $key) {
                $parts = explode(':', $key);

                if (count($parts) === 2) {
                    $jobId = (int) $parts[1];

                    $status = $client->hGet((int) $jobId, 'status');

                    if ($status === self::COMPLETED) {
                        $client->del($jobId);

                        $deletedJobs++;
                    }
                }
            }
        }

        $this->line(sprintf('Deleted jobs: <comment>%d</comment>', $deletedJobs));
    }
}
