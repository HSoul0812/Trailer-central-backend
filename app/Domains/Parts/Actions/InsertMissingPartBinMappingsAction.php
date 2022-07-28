<?php

namespace App\Domains\Parts\Actions;

use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsertMissingPartBinMappingsAction
{
    /** @var int */
    private $chunkSize = 5000;

    /** @var callable */
    private $onFoundMappingToInsert;

    public function __construct()
    {
        $this->onFoundMappingToInsert = function (int $partId, int $binId) {
            // Boilerplate closure, do nothing by default
        };
    }

    /**
     * The main method, use this to execute the action
     * In short, this action will find all the missing mapping
     * between part and bin and will create them with the default
     * quantity of 0
     *
     * @param int $dealerId
     * @return void
     * @throws ModelNotFoundException
     */
    public function execute(int $dealerId): void
    {
        $dealer = User::findOrFail($dealerId);

        $binIds = $this->getDealerBinIds($dealer);

        // Use DB facade instead of Eloquent for performance
        // some dealers might have 200k+ parts, Eloquent won't cut it
        DB::table(Part::getTableName())
            ->select(['id', 'dealer_id'])
            ->where('dealer_id', $dealer->dealer_id)
            ->chunkById($this->chunkSize, function (Collection $parts) use ($binIds) {
                $inserts = collect([]);

                $partIds = $parts->pluck('id');

                $partBins = $this->getPartBinsGrouped($partIds);

                /**
                 * Here we loop over each group (key is the part id)
                 * We will find out the missing mappings and then add it
                 * to the insert array, once the loop finished, we will
                 * supply the $inserts variable in the insert method
                 *
                 * @var int $partId
                 * @var Collection $bins
                 */
                foreach ($partBins as $partId => $bins) {
                    $partBinIds = $bins->pluck('bin_id');

                    $diffIds = $binIds->diff($partBinIds);

                    $insertPayloads = $this->getInsertPayloads($diffIds, $partId);

                    $inserts = $inserts->merge($insertPayloads);
                }

                DB::table(BinQuantity::getTableName())->insert($inserts->toArray());
            });
    }

    /**
     * Internal method, use by the action to get bin ids that belong
     * to the dealer
     *
     * @param User $dealer
     * @return Collection
     */
    private function getDealerBinIds(User $dealer): Collection
    {
        return $dealer->bins()->pluck('id');
    }

    /**
     * Internal method, use by the action to get the collection
     * that has the key as the part_id and values as the list of
     * part/bin mappings (BinQuantity model)
     *
     * @param Collection $partIds
     * @return Collection
     */
    private function getPartBinsGrouped(Collection $partIds): Collection
    {
        return DB::table(BinQuantity::getTableName())
            ->whereIn('part_id', $partIds)
            ->get(['id', 'part_id', 'bin_id'])
            ->groupBy('part_id');
    }

    /**
     * Internal method, use by the action to get the insert payload
     *
     * @param Collection $diffIds
     * @param int $partId
     * @return Collection
     */
    private function getInsertPayloads(Collection $diffIds, int $partId): Collection
    {
        return $diffIds->map(function (int $binId) use ($partId) {
            call_user_func($this->onFoundMappingToInsert, $binId, $partId);

            return [
                'part_id' => $partId,
                'bin_id' => $binId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->values();
    }

    /**
     * Assign a custom chunk size using this method
     *
     * @param int $chunkSize
     * @return $this
     */
    public function withChunkSize(int $chunkSize): InsertMissingPartBinMappingsAction
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Set the callable to run when the code found the mapping to insert
     * Method signature is (int $partId, int $binId) where $partId is the
     * value that will go to the part_id and $binId goes to bin_id column
     *
     * @param callable $callable
     * @return $this
     */
    public function withOnFoundMappingToInsert(callable $callable): InsertMissingPartBinMappingsAction
    {
        $this->onFoundMappingToInsert = $callable;

        return $this;
    }
}