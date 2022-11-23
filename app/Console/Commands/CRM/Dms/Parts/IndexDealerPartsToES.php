<?php

namespace App\Console\Commands\CRM\Dms\Parts;

use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class IndexDealerPartsToES extends Command
{
    protected $signature = '
        crm:dms:parts:index-to-es
        {dealerIds : Dealer IDs seperated by commas.}
        {--chunkSize=500 : The size for each chunk.}
        {--delayChunkCount=10000 : The size of the chunk before we apply the wait time (delays).}
        {--delay=120 : Delay time in seconds after each delayChunkCount parts is being dispatched.}
    ';

    protected $description = 'Index all the parts under the dealer ids.';

    /** @var array<int, int> */
    private $dealerIds;

    private $chunkSize;

    private $delayChunkCount;

    private $delay;

    public function handle(): int
    {
        $this->prepareInputs();

        foreach ($this->dealerIds as $dealerId) {
            $dealer = User::find($dealerId);

            if ($dealer === null) {
                $this->error("Dealer ID $dealerId doesn't exist, skipping...");
                continue;
            }

            $this->indexParts($dealer);
        }

        $this->line('');
        $this->info('The command has finished!');

        return 0;
    }

    private function prepareInputs(): void
    {
        $this->dealerIds = collect(explode(',', $this->argument('dealerIds')))
            ->filter(function (string $dealerId) {
                return !empty($dealerId);
            })
            ->map(function (string $dealerId) {
                return (int) trim($dealerId);
            })
            ->unique()
            ->values();

        $this->chunkSize = $this->option('chunkSize');
        $this->delayChunkCount = $this->option('delayChunkCount');
        $this->delay = $this->option('delay');
    }

    private function indexParts(User $dealer): void
    {
        $this->alert("Start indexing parts for dealer ID $dealer->dealer_id!");

        $chunkRound = 0;
        $dispatchedCountTotal = 0;
        $dispatchedCountThisRound = 0;

        $partCount = $dealer->parts()->count();

        $dealer->parts()->chunkById($this->chunkSize, function (Collection $parts) use (&$chunkRound, &$dispatchedCountTotal, &$dispatchedCountThisRound, $partCount) {
            $chunkRound++;

            $this->info("Chunk round: $chunkRound, start processing...");

            $parts->searchable();
            $currentRoundPartsCount = $parts->count();

            $dispatchedCountTotal += $currentRoundPartsCount;
            $dispatchedCountThisRound += $currentRoundPartsCount;

            $this->info("Dispatched MakeSearchable jobs for $dispatchedCountThisRound from $partCount parts.");

            // Once we've dispatched the parts >= the chunk count
            // we will sleep for a certain seconds to allow Redis
            // to process all of the dispatched jobs before we dispatch
            // the new round of jobs (just so we don't flush Redis)
            if ($dispatchedCountThisRound >= $this->delayChunkCount) {
                $dispatchedCountThisRound = 0;

                $this->info("We've reached $dispatchedCountThisRound parts in this round! (delayChunkCount = $this->delayChunkCount), chunk round $chunkRound ends here.");
                $this->info("Sleep for $this->delay seconds before starting next chunk round...");
                sleep($this->delay);
            }
        });
    }
}
