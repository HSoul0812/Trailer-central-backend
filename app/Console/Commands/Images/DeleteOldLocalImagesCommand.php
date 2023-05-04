<?php

namespace App\Console\Commands\Images;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldLocalImagesCommand extends Command
{
    use PrependsOutput;
    use PrependsTimestamp;

    public const DELETE_OLDER_THAN_MONTHS = 6;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:delete-old-local';

    /** @var string The console command description. */
    protected $description = 'Delete old local images.';

    public function __construct(private ImageServiceInterface $imageService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = Carbon::now()->subMonths(self::DELETE_OLDER_THAN_MONTHS)->diffInDays();

        $this->imageService->deleteOldLocalImages($days);

        $this->info("Images that are older than $days days have been deleted!");

        return 0;
    }
}
