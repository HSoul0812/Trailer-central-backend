<?php

namespace App\Services\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\BinQuantity;
use App\Repositories\Parts\CostHistoryRepositoryInterface;
use App\Repositories\Parts\CycleCountRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Parts\PartServiceInterface;
use Illuminate\Support\Facades\DB;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\Log;

/**
 *
 *
 * @author Eczek
 */
class PartService implements PartServiceInterface
{
    /**
     * @var App\Services\Parts\PartsServiceInterface
     */
    protected $partRepository;
    /**
     * @var App\Repositories\Parts\CycleCountRepositoryInterface
     */
    protected $cycleCountRepository;

    /**
     * @var App\Repositories\Parts\CostHistoryRepositoryInterface
     */
    protected $costHistoryRepository;

    public function __construct(
        PartRepositoryInterface $partRepository,
        CycleCountRepositoryInterface $cycleCountRepository,
        CostHistoryRepositoryInterface $costHistoryRepository
    )
    {
        $this->partRepository = $partRepository;
        $this->cycleCountRepository = $cycleCountRepository;
        $this->costHistoryRepository = $costHistoryRepository;
    }

    public function create($partData, $bins) : Part
    {
        $part = null;

        DB::transaction(function() use ($partData, $bins, &$part) {

            $part = $this->partRepository->create($partData);

            foreach($bins as $bin) {

                if (!isset($bin['old_quantity']) || !isset($bin['quantity'])) {
                    // log why this part was skipped
                    Log::warning("Quantities not specified, skipping", ['bin' => $bin]);
                    continue;
                }

                $this->cycleCountRepository->create([
                    'bin_id' => $bin['bin_id'],
                    'dealer_id' => $part->dealer_id,
                    'is_completed' => 1,
                    'is_balanced' => 0,
                    'parts' => [
                        [
                          'part_id' => $part->id,
                          'starting_qty' => $bin['old_quantity'],
                          'count_on_hand' => $bin['quantity']
                        ]
                    ]
                ]);

                $binQuantity = BinQuantity::where([
                    'part_id' => $part->id,
                    'bin_id' => $bin['bin_id'],
                ])->first();

                event(new PartQtyUpdated($part, $binQuantity, [
                    'quantity' => ($bin['quantity'] - $bin['old_quantity']),
                    'description' => 'Part created'
                ]));
            }

        });


        return $part;
    }

    public function update($partData, $bins) : Part
    {
        $part = null;
        $partBeforeUpdate = $this->partRepository->get($partData);

        DB::transaction(function() use ($partData, $bins, &$part, $partBeforeUpdate) {
            /** @var Part $part */
            $part = $this->partRepository->update($partData);

            foreach($bins as $bin) {

                if (!isset($bin['quantity'])) {
                    // log why this part was skipped
                    Log::warning("Quantities not specified, skipping", ['bin' => $bin]);
                    continue;
                }

                if (!isset($bin['old_quantity'])) {
                    $bin['old_quantity'] = 0;
                }

                $this->cycleCountRepository->create([
                    'bin_id' => $bin['bin_id'],
                    'dealer_id' => $part->dealer_id,
                    'is_completed' => 1,
                    'is_balanced' => 0,
                    'parts' => [
                        [
                          'part_id' => $part->id,
                          'starting_qty' => $bin['old_quantity'],
                          'count_on_hand' => $bin['quantity']
                        ]
                    ]
                ]);

                if ($bin['quantity'] != $bin['old_quantity']) {
                    // get the updated bin qty
                    $binQuantity = $part->bins->where('bin_id', $bin['bin_id'])->first();

                    event(new PartQtyUpdated($part, $binQuantity, [
                        'quantity' => ($bin['quantity'] - $bin['old_quantity']),
                        'description' => 'Part updated'
                    ]));
                }
            }

            // If a dealer_cost (an average cost of an part) is changed, create a new parts_cost_history record
            if (isset($partData['dealer_cost']) && $partBeforeUpdate->dealer_cost != $part->dealer_cost) {
                $this->costHistoryRepository->create([
                    'part_id' => $part->id,
                    'old_cost' => $partBeforeUpdate->dealer_cost,
                    'new_cost' => $part->dealer_cost
                ]);
            }
        });

        return $part;
    }

}
