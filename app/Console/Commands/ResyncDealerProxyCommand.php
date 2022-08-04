<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Redis;

class ResyncDealerProxyCommand extends Command {
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "throwaway:test";
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $redis = Redis::connection('dealer-proxy');
        $websites = Website::get();
        
        foreach($websites as $website) {
            echo $redis->set($website->domain, 'true') . PHP_EOL;
            echo "inserted {$website->domain}" . PHP_EOL;
            
            $wwwDomain = 'www.'.$website->domain;
            
            echo $redis->set($wwwDomain, 'true') . PHP_EOL;
            echo "inserted {$wwwDomain}" . PHP_EOL;
        }
    }
    
}
