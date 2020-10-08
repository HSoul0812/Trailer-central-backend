<?php

namespace App\Repositories\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\DB;

use App\Models\Parts\BinQuantity;
use App\Models\Parts\CycleCount;
use App\Models\Parts\CycleCountHistory;
use App\Repositories\Parts\CycleCountRepositoryInterface;

/**
 * @author Marcel
 */
class CycleCountRepository implements CycleCountRepositoryInterface
{

    protected function addCycleCountHistories(CycleCount $cycleCount, array $parts)
    {
        foreach ($parts as $cycleCountPart) {
            CycleCountHistory::create([
                'cycle_count_id' => $cycleCount->id,
                'part_id' => $cycleCountPart['part_id'],
                'count_on_hand' => $cycleCountPart['count_on_hand'],
                'starting_qty' => $cycleCountPart['starting_qty'],
            ]);
            // Update part qty by bin if the cycle count is completed
            if ($cycleCount->is_completed) {
                $binQty = BinQuantity::where('part_id', $cycleCountPart['part_id'])
                    ->where('bin_id', $cycleCount->bin_id)
                    ->first();
                if ($binQty) {
                    $binQty->update(['qty' => $cycleCountPart['count_on_hand']]);
                } else {
                    // Create a new bin qty
                    $binQty = BinQuantity::create([
                        'part_id' => $cycleCountPart['part_id'],
                        'bin_id' => $cycleCount->bin_id,
                        'qty' => $cycleCountPart['count_on_hand']
                    ]);
                }

                $difference = $cycleCountPart['count_on_hand'] - $cycleCountPart['starting_qty'];
                event(new PartQtyUpdated(
                    Part::find($cycleCountPart['part_id']),
                    $binQty,
                    [
                        'quantity' => $difference,
                        'description' => 'Updated via cycle count',
                    ]
                ));
            }
        }
    }

    public function create($params)
    {
        DB::beginTransaction();

        try {
            $cycleCount = CycleCount::create($params);
            $this->addCycleCountHistories($cycleCount, $params['parts']);

             DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }

        return $cycleCount;
    }

    public function delete($params) {
        $cycleCount = CycleCount::findOrFail($params['id']);
        $cycleCount->parts()->delete();
        return $cycleCount->delete();
    }

    public function get($params) {
        return CycleCount::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        if (isset($params['dealer_id'])) {
            $query = CycleCount::whereIn('dealer_id', $params['dealer_id']);
        } else {
            $query = CycleCount::where('id', '>', 0);
        }

        if (isset($params['bin_id'])) {
            $query = $query->whereIn('bin_id', $params['bin_id']);
        }
        if (isset($params['is_completed'])) {
            $query = $query->where('is_completed', $params['is_completed']);
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params)
    {
        $cycleCount = CycleCount::findOrFail($params['id']);

        DB::transaction(function() use (&$cycleCount, $params) {
            $cycleCount->fill($params);

            if ($cycleCount->save()) {
                $cycleCount->parts()->delete();
                $this->addCycleCountHistories($cycleCount, $params['parts']);
            }

        });

        return $cycleCount;
    }

}
