<?php

namespace App\Console\Commands\Parts;

use App\Models\Parts\Part;
use Illuminate\Console\Command;

/**
 * Class EsUpdateParts
 * @package App\Console\Commands\Parts
 */
class UpdatePartsInElasticsearch extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "parts:update-in-elasticsearch";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): bool
    {
        $partsQuery = Part::query();
        $count = 0;

        $partsQuery->chunk(500, function ($parts) use ($count) {
            /** @var Part $part */
            foreach ($parts as $part) {
                $part->searchable();
                $count++;
            }

            $this->info('Updated: ' . $count);
        });

        return true;
    }
}
