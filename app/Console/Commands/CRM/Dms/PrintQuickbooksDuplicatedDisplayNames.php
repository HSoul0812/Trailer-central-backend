<?php

namespace App\Console\Commands\CRM\Dms;

use App\Domains\QuickBooks\Actions\GetQuickBooksDuplicatedDisplayNamesAction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PrintQuickbooksDuplicatedDisplayNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:quickbooks:print-duplicated-display-names {dealer_id : The dealer id that we want to run this command on.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the duplicated customers, vendors, and employees display name.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(GetQuickBooksDuplicatedDisplayNamesAction $getQuickBooksDuplicatedDisplayNames)
    {
        try {
            $stats = $getQuickBooksDuplicatedDisplayNames->execute($this->argument('dealer_id'));
        } catch (ModelNotFoundException $exception) {
            $this->error($exception->getMessage());
            return 1;
        }

        $this->printStats($stats);

        return 0;
    }

    private function printStats(Collection $stats): void
    {
        $this->info("Found {$stats->count()} duplicated display names!");

        if ($stats->isEmpty()) {
            $this->info("Hooray!");
            return;
        }

        foreach ($stats as $index => $stat) {
            $segments = collect([]);

            $no = $index + 1;

            $segments->push("$no. {$stat['display_name']}: {$stat['duplicated_count']}");

            foreach (['customers', 'employees', 'vendors'] as $modelType) {
                $typeTitle = ucfirst($modelType);

                if ($stat[$modelType]->isNotEmpty()) {
                    $segments->push("$typeTitle IDs: " . $stat[$modelType]->implode(', '));
                }
            }

            $this->info($segments->implode(' | '));
        }
    }
}