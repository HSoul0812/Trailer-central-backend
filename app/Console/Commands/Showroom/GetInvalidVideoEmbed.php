<?php

namespace App\Console\Commands\Showroom;

use Illuminate\Console\Command;
use App\Models\User\User;
use App\Models\User\AuthToken;
use App\Models\Showroom\Showroom;

class GetInvalidVideoEmbed extends Command {
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "showroom:get-invalid-video-embed";
    
    public function handle() 
    {
        $showroomUnits = Showroom::where('video_embed_code', 'LIKE', '%<iframe width="100%" height="400px" src="<iframe width="100%" height="400px" %')->get();
        foreach($showroomUnits as $unit) {
            echo "{$unit->id} has a broken video embed code." . PHP_EOL;
        }
        
    }
}

