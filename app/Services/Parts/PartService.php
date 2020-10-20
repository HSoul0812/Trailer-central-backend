<?php

namespace App\Services\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\BinQuantity;
use App\Repositories\Parts\CycleCountRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Parts\PartServiceInterface;
use App\Exceptions\NotImplementedException;
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

    public function __construct(PartRepositoryInterface $partRepository, CycleCountRepositoryInterface $cycleCountRepository)
    {
        $this->partRepository = $partRepository;
        $this->cycleCountRepository = $cycleCountRepository;
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

        DB::transaction(function() use ($partData, $bins, &$part) {
            $part = $this->partRepository->update($partData);

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

                if ($bin['quantity'] != $bin['old_quantity']) {
                    $binQuantity = BinQuantity::where([
                        'part_id' => $part->id,
                        'bin_id' => $bin['bin_id'],
                    ])->first();

                    event(new PartQtyUpdated($part, $binQuantity, [
                        'quantity' => ($bin['quantity'] - $bin['old_quantity']),
                        'description' => 'Part updated'
                    ]));
                }
            }
        });

        return $part;
    }

}
