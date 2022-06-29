<?php

namespace App\Console\Commands\User;

use App\Console\Commands\User\GenerateCrmUserJob;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerateCrmUsers extends Command {    
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "user:generate-crm-users";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates CRM Users on all Dealers missing them.';


    /**
     * Handle Generate CRM Users That Don't Have It
     */
    public function handle() 
    {
        // Get All Users
        $addCount = 0;
        $users = User::doesntHave('dealerUsers')->get();
        foreach ($users as $user) {
            // Dispatch Generate CRM User Job
            $job = new GenerateCrmUserJob($user);
            $this->dispatch($job->onQueue('crm-users'));
            $addCount++;
        }
        echo "Queued {$addCount} CRM users for dealers without crm users".PHP_EOL;
    }
}

