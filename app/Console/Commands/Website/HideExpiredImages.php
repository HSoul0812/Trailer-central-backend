<?php

namespace App\Console\Commands\Website;

use App\Models\Website\Image\WebsiteImage;
use Illuminate\Console\Command;

class HideExpiredImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:hide-expired-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command would hide expired website images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        WebsiteImage::whereDate('expires_at', '<=', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
