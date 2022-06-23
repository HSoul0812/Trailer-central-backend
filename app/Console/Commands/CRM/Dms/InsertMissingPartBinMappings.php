<?php

namespace App\Console\Commands\CRM\Dms;

use App\Domains\Parts\Actions\InsertMissingPartBinMappingsAction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InsertMissingPartBinMappings extends Command
{
    const CHUNK_SIZE = 5000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:insert-missing-part-bin-mappings {dealerId : Dealer ID to run this command on.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert the missing part -> bin mappings.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(InsertMissingPartBinMappingsAction $insertMissingPartBinMappings)
    {
        $start = now();
        
        $totalInsert = 0;
        
        try {
            $insertMissingPartBinMappings
                ->withOnFoundMappingToInsert(function (int $partId, int $binId) use (&$totalInsert) {
                    // Only print out the debug line if the user runs
                    // this command with -v or --verbose option
                    if ($this->option('verbose')) {
                        $this->info("Insert: part_id = $partId, bin_id = $binId");
                    }
                    
                    $totalInsert++;
                })
                ->execute($this->argument('dealerId'));
        } catch (ModelNotFoundException $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
        
        $runTime = now()->diffInRealMilliseconds($start) / 1000;
        
        $this->info("Command runtime: $runTime seconds.");
        $this->info("Peak memory usage: " . memory_get_peak_usage(true) / 1024 / 1024 . "MB.");
        $this->info("Total insert: $totalInsert records!");

        return 0;
    }
}
