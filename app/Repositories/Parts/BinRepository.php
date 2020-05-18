<?php

namespace App\Repositories\Parts;

use App\Exceptions\NotImplementedException;
use App\Models\Parts\Bin;
use App\Repositories\Parts\BinRepositoryInterface;

/**
 *
 * @author David A Conway Jr.
 */
class BinRepository implements BinRepositoryInterface
{

    public function create($params)
    {
        throw new NotImplementedException;
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        // Bin ID Exists?
        if (isset($params['bin_id'])) {
            return Bin::first($params['bin_id']);
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
        if (isset($params['dealer_id'])) {
            $query = Bin::whereIn('dealer_id', $params['dealer_id']);
        } else {
            $query = Bin::where('id', '>', 0);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['bin_name'])) {
            $query = $query->where('bin_name', 'like', '%' . $params['bin_name'] . '%');
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params)
    {
        throw new NotImplementedException;
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
                $binId = $this->get(array(
                    'dealer_id' => $dealerId,
                    'bin_name' => $csvData[$keyToIndexMapping[$id]],
                ))->id;

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

}
