<?php

namespace App\Domains\Parts\Actions;

use App\Models\User\User;
use Illuminate\Support\Collection;

class IndexDealerPartsToESAction
{
    /**
     * How many models we want to have in one MakeSearchable job
     *
     * @var int
     */
    private $chunkSize = 500;

    /**
     * How many models processed before we pause for a few moment
     *
     * @var int
     */
    private $delayChunkThreshold = 10000;

    /**
     * How long we want to wait before processing the new round
     * by default it's 10 seconds because that's how long it takes
     * for production to process one chunk (500 parts)
     *
     * @var int
     */
    private $delay = 10;

    /**
     * A callable that will be called if the dealer has no parts
     *
     * @var callable<User>
     */
    private $onDealerHasNoParts;

    /**
     * A callable that will be called before start processing each round
     *
     * @var callable<int>
     */
    private $onStartProcessingRound;

    /**
     * A callable that will be called after the code dispatched the jobs
     *
     * @var callable<int, int>
     */
    private $onDispatchedJobs;

    /**A callable that will be called when the dispatched jobs number exceed
     * the $delayChunkThreshold value
     *
     * @var callable<int, int>
     */
    private $onDispatchedExceedingThreshold;

    public function __construct()
    {
        $this->onDealerHasNoParts = function(User $dealer) {
            // Logic can be implemented in the class caller
        };

        $this->onStartProcessingRound = function (int $round) {
            // Logic can be implemented in the class caller
        };

        $this->onDispatchedJobs = function (int $dispatchedTotal, int $totalParts) {
            // Logic can be implemented in the class caller
        };

        $this->onDispatchedExceedingThreshold = function (int $dispatchedThisRound, int $round) {
            // Logic can be implemented in the class caller
        };
    }

    /**
     * A main entry method for this action, call this to start indexing all the parts
     * under the given dealer model
     *
     * @param User $dealer
     * @return void
     */
    public function execute(User $dealer): void
    {
        $round = 1;
        $dispatchedTotal = 0;
        $dispatchedThisRound = 0;
        $currentlyProcessingRound = 0;

        $totalParts = $dealer->parts()->count();

        // No need to process if this dealer has no parts
        if ($totalParts === 0) {
            call_user_func($this->onDealerHasNoParts, $dealer);
            return;
        }

        $dealer->parts()->chunkById($this->chunkSize, function (Collection $parts) use (&$round, &$dispatchedTotal, &$dispatchedThisRound, &$currentlyProcessingRound, $totalParts) {
            if ($round !== $currentlyProcessingRound) {
                call_user_func($this->onStartProcessingRound, $round);
                $currentlyProcessingRound = $round;
            }

            $parts->searchable();
            $currentRoundPartsCount = $parts->count();

            $dispatchedTotal += $currentRoundPartsCount;
            $dispatchedThisRound += $currentRoundPartsCount;

            call_user_func($this->onDispatchedJobs, $dispatchedTotal, $totalParts);

            // Once we've dispatched the parts >= the chunk count
            // we will sleep for a certain seconds to allow Redis
            // to process all the dispatched jobs before we dispatch
            // the new round of jobs (just so we don't flush Redis)
            if ($dispatchedThisRound >= $this->delayChunkThreshold) {
                call_user_func($this->onDispatchedExceedingThreshold, $dispatchedThisRound, $round);

                sleep($this->delay);

                $dispatchedThisRound = 0;
                $round++;
            }
        });
    }

    /**
     * @param int $chunkSize
     * @return IndexDealerPartsToESAction
     */
    public function withChunkSize(int $chunkSize): IndexDealerPartsToESAction
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @param int $delayChunkThreshold
     * @return IndexDealerPartsToESAction
     */
    public function withDelayChunkThreshold(int $delayChunkThreshold): IndexDealerPartsToESAction
    {
        $this->delayChunkThreshold = $delayChunkThreshold;

        return $this;
    }

    /**
     * @param int $delay
     * @return IndexDealerPartsToESAction
     */
    public function withDelay(int $delay): IndexDealerPartsToESAction
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * @param callable|\Closure $onDealerHasNoParts
     * @return IndexDealerPartsToESAction
     */
    public function withOnDealerHasNoParts($onDealerHasNoParts): IndexDealerPartsToESAction
    {
        $this->onDealerHasNoParts = $onDealerHasNoParts;

        return $this;
    }

    /**
     * @param callable|\Closure $onStartProcessingRound
     * @return IndexDealerPartsToESAction
     */
    public function withOnStartProcessingRound($onStartProcessingRound): IndexDealerPartsToESAction
    {
        $this->onStartProcessingRound = $onStartProcessingRound;

        return $this;
    }

    /**
     * @param callable|\Closure $onDispatchedJobs
     * @return IndexDealerPartsToESAction
     */
    public function withOnDispatchedJobs($onDispatchedJobs): IndexDealerPartsToESAction
    {
        $this->onDispatchedJobs = $onDispatchedJobs;

        return $this;
    }

    /**
     * @param callable|\Closure $onDispatchedExceedingThreshold
     * @return IndexDealerPartsToESAction
     */
    public function withOnDispatchedExceedingThreshold($onDispatchedExceedingThreshold): IndexDealerPartsToESAction
    {
        $this->onDispatchedExceedingThreshold = $onDispatchedExceedingThreshold;

        return $this;
    }
}
