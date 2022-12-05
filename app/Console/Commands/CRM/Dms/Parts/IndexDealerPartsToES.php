<?php

namespace App\Console\Commands\CRM\Dms\Parts;

use App\Console\Traits\PrependsOutput;
use App\Console\Traits\PrependsTimestamp;
use App\Domains\Parts\Actions\IndexDealerPartsToESAction;
use App\Models\User\User;
use Illuminate\Console\Command;

class IndexDealerPartsToES extends Command
{
    use PrependsOutput, PrependsTimestamp;

    protected $signature = '
        crm:dms:parts:index-to-es
        {dealerIds : Dealer IDs seperated by commas.}
        {--chunkSize=500 : The size for each chunk.}
        {--delayChunkThreshold=10000 : The size of the chunk before we apply the wait time (delays).}
        {--delay=10 : Delay time in seconds after each delayChunkCount parts is being dispatched.}
    ';

    protected $description = 'Index all the parts under the dealer ids.';

    public function handle(): int
    {
        $indexPartsAction = $this->getIndexPartsAction();

        $dealers = $this->getDealers();

        if (empty($dealers)) {
            $this->info("No valid dealers to process! terminating the script now, have a good day! 😃");
            return 1;
        }

        foreach ($dealers as $dealer) {
            $this->info("Start indexing parts for dealer ID $dealer->dealer_id!");

            $indexPartsAction->execute($dealer);

            $this->info("Finished indexing parts for dealer ID $dealer->dealer_id!" . PHP_EOL);
        }

        $this->info('The command has finished!');

        return 0;
    }

    /**
     * Get the Index Parts action to use in this command
     *
     * @return IndexDealerPartsToESAction
     */
    private function getIndexPartsAction(): IndexDealerPartsToESAction
    {
        $delayChunkThreshold = $this->option('delayChunkThreshold');
        $delay = $this->option('delay');

        return resolve(IndexDealerPartsToESAction::class)
            ->withChunkSize($this->option('chunkSize'))
            ->withDelayChunkThreshold($delayChunkThreshold)
            ->withDelay($delay)
            ->withOnDealerHasNoParts(function () {
                $this->line("Dealer has no parts in the database, skipping this one...");
            })
            ->withOnStartProcessingRound(function (int $round) {
                $this->line("Chunk round: $round, start processing...");
            })
            ->withOnDispatchedJobs(function (int $dispatchedTotal, int $totalParts) {
                $this->line("Dispatched MakeSearchable jobs for $dispatchedTotal from $totalParts parts.");
            })
            ->withOnDispatchedExceedingThreshold(function (int $dispatchedThisRound, int $round) use ($delayChunkThreshold, $delay) {
                $this->line("We've reached $dispatchedThisRound parts in this round! (delayChunkCount = $delayChunkThreshold), chunk round $round ends here.");
                $this->line("Pause for $delay seconds before processing the next chunk round...");
            });
    }

    /**
     * Get the dealers collection from the input
     *
     * @return array<int, User>
     */
    private function getDealers(): array
    {
        $this->info("Gathering dealers from the database...");

        $dealerIds = explode(',', $this->argument('dealerIds'));

        $dealers = [];

        foreach ($dealerIds as $dealerId) {
            $dealerId = trim($dealerId);
            $tempDealerId = $dealerId;

            $dealerId = filter_var($dealerId, FILTER_VALIDATE_INT);

            // Ignore the non integer dealer id
            if ($dealerId === false) {
                $this->error("Invalid dealer ID format: $tempDealerId, dealer ID must be an integer.");
                continue;
            }

            // No need to process the same dealer id again
            if (array_key_exists($dealerId, $dealers)) {
                $this->error("Duplicate dealer ID: $dealerId, will process only once.");
                continue;
            }

            $dealer = User::find($dealerId);

            if ($dealer === null) {
                $this->error("Dealer ID $dealerId doesn't exist in the database!");
                continue;
            }

            $dealers[$dealerId] = $dealer;
        }

        $dealerIds = implode(', ', array_keys($dealers));

        $this->info("Finished gathering dealers! The command will index parts for dealers in this order: $dealerIds." . PHP_EOL);

        return $dealers;
    }
}
