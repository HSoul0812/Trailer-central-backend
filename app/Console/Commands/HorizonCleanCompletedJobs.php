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

    /** @var RedisClient */
    private $client;


    public function handle()
    {
        /** @var RedisClient $client */
        $client = Redis::connection('horizon')->client();

        $cursor = null;

        $deletedJobs = 0;

        $bufferOfJobs = [];

        while (false !== ($keys = $client->scan($cursor, 'horizon:[0-9]*', 10))) {

            foreach ($keys as $key) {
                $parts = explode(':', $key);

                if (count($parts) === 2) {
                    $jobId = (int) $parts[1];

                    $status = $client->hGet((int) $jobId, 'status');

                    if ($status === self::COMPLETED) {
                        $bufferOfJobs[] = $jobId;

                        $deletedJobs++;
                    }

                    if (count($bufferOfJobs) === 50) {
                        $this->unlink($bufferOfJobs);
                    }
                }
            }
        }

        $this->unlink($bufferOfJobs);

        $this->line(sprintf('Deleted jobs: <comment>%d</comment>', $deletedJobs));
    }

    private function unlink(array &$jobs): void
    {
        if (count($jobs) > 0) {
            $this->client->del(...$jobs);

            $jobs = [];
        }
    }

    public function __construct()
    {
        parent::__construct();

        $this->client = Redis::connection('horizon')->client();
    }
}
