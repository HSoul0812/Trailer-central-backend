<?php

namespace App\Repositories\Parts;

use App\Exceptions\NotImplementedException;
use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use Illuminate\Support\Facades\DB;

/**
 *
 * @author David A Conway Jr.
 */
class BinRepository implements BinRepositoryInterface
{

    public function create($params)
    {
        return Bin::create($params);
    }

    public function delete($params)
    {
        $bin = Bin::findOrFail($params['bin_id']);
        return $bin->delete();
    }

    /**
     * @param array $params
     * @return Bin
     */
    public function get($params)
    {
        // Bin ID Exists?
        if (isset($params['bin_id'])) {
            return Bin::find($params['bin_id']);
        }

        // Initialize Bin Query
        $query = Bin::where('id', '>', 0);
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        if (isset($params['bin_name'])) {
            $query = $query->where('bin_name', $params['bin_name']);
        }
        if (isset($params['location'])) {
            $query = $query->where('location', $params['location']);
        }

        // Return First Value
        return $query->first();
    }

    public function getAll($params)
    {
        $query = Bin::where('dealer_id', $params['dealer_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['bin_name'])) {
            $query = $query->where('bin_name', 'like', '%' . $params['bin_name'] . '%');
        }

        // Added for consistency
        if (isset($params['search_term'])) {
            $query = $query->where('bin_name', 'LIKE', "%{$params['search_term']}%");
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params)
    {
        $bin = $this->get($params);
        $bin->fill($params);
        $bin->save();
        return $bin;
    }

    public function getOrCreate($params)
    {
        return Bin::firstOrCreate($params);
    }

    /**
     * Gets List of Bins from CSV, Returns Result
     *
     * @param array $csvData
     * @return array
     */
    public function getAllBinsCsv($dealerId, $csvData, $keyToIndexMapping)
    {
        // Loop Numbers to Find Bins
        $bins = array();
        $i = 1;
        do {
            // Get Keys
            $id = 'Bin ' . $i . ' ID';
            $qty = 'Bin ' . $i . ' qty';

            // Bin Doesn't Exist?!
            if (isset($keyToIndexMapping[$id])) {
                // Get Values
                $bin = $this->get(array(
                    'dealer_id' => $dealerId,
                    'bin_name' => $csvData[$keyToIndexMapping[$id]],
                ));

                if (empty($bin)) {
                    break;
                }

                $binId = $bin->id;

                // Return Bin Array
                $bins[] = array(
                    'bin_id' => $binId,
                    'quantity' => isset($csvData[$keyToIndexMapping[$qty]]) ? $csvData[$keyToIndexMapping[$qty]] : 0,
                );
            }

            // Increment
            $i++;
            $id = 'Bin ' . $i . ' ID';
        } while (count($bins) < 1 || isset($keyToIndexMapping[$id]));

        // Return Bins Array
        return $bins;
    }

    /**
     * @param int $dealerId
     * @return array
     */
    public function financialReportByDealer(int $dealerId): array
    {
        $binTable = Bin::getTableName();
        $binQtyTable = BinQuantity::getTableName();
        $partTable = Part::getTableName();

        $bins = Bin::select(DB::raw("{$binQtyTable}.qty, {$binTable}.bin_name, {$binQtyTable}.bin_id, {$partTable}.id," .
            " title, sku, price, dealer_cost"))
            ->leftJoin($binQtyTable, static function ($join) use ($binTable, $binQtyTable) {
                return $join->on("{$binQtyTable}.bin_id", '=', "{$binTable}.id")
                    ->where("{$binQtyTable}.qty", '>', 0);
            })
            ->leftJoin($partTable, static function ($join) use ($dealerId, $partTable, $binQtyTable) {
                return $join->on("{$partTable}.id", '=', "{$binQtyTable}.part_id")
                    ->where("{$partTable}.dealer_id", '=', $dealerId);
            })
            ->whereNotNull("{$partTable}.id")->cursor();

        $report = [];

        /** @var Bin $bin */
        foreach ($bins as $bin) {
            $report[$bin->id][$bin->bin_id]['part'] = $bin->toArray();
        }

        return $report;
    }
}
