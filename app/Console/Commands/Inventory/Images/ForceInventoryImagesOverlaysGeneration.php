<?php

namespace App\Console\Commands\Inventory\Images;

use App\Jobs\Inventory\GenerateAllOverlayImagesByDealer;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ForceInventoryImagesOverlaysGeneration extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'inventory:force-image-overlays-regeneration {dealer_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will force image overlay regeneration';

    /**
     * @return void
     */
    public function handle()
    {

        /** @var int $dealerId */
        $dealerId = $this->argument('dealer_id');

        User::query()
            ->where('dealer_id', $dealerId)
            ->update(['overlay_updated_at' => now()]);

        $job = (new GenerateAllOverlayImagesByDealer($dealerId))->delay(2);

        $this->dispatch($job);
    }
}
