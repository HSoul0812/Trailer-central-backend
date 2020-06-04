<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Showroom\Showroom;

/**
 * Description of ReplaceYoutubeEmbeds
 *
 * @author Eczek
 */
class ReplaceYoutubeEmbeds extends Command {
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "showroom:replace:youtube-embed";
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $showrooms = Showroom::where('video_embed_code', 'LIKE', '%youtube.com%')->get();
        $i = 0;
        foreach($showrooms as $showroom) {            
            $videoEmbedCode = str_replace('https://www.youtube.com/watch?v=', 'https://www.youtube.com/embed/', $showroom->video_embed_code);
            $showroom->video_embed_code = $videoEmbedCode;
            $showroom->save();
        }
    }
    
}
