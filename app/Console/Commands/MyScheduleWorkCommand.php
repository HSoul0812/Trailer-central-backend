<?php

/*
Replacement for https://github.com/laravel/framework/pull/34618/files
Can be deleted after migration to laravel 8
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Class MyScheduleWorkCommand
 */
class MyScheduleWorkCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schedule:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the schedule worker';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Schedule worker started successfully.');

        while (true) {
            if (Carbon::now()->second === 0) {
                $this->call('schedule:run');
            }

            sleep(1);
        }
    }
}
