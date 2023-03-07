<?php

namespace App\Console\Commands\Website;

use App\Models\Website\Image\WebsiteImage;
use Illuminate\Console\Command;

class UpdateWebsiteImagesVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:update-images-visibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command updates the website image is_active based on its start and expiry dates';

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

        WebsiteImage::whereDate('starts_from', '<=', now())
            ->where('is_active', false)
            ->update(['is_active' => true]);
    }
}
