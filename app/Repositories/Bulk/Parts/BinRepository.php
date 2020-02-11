<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\BinRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Bin;

/**
 *  
 * @author David A Conway Jr.
 */
class BinRepository implements BinRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }
    
    public function getOrCreate($params) {
        return Bin::firstOrCreate($params);
    }

    /**
     * Gets List of Bins and Updates Them, Returns Result
     * 
     * @param array $csvData
     * @return array
     */
    public function getAllBins($dealerId, $csvData, $keyToIndexMapping) {
        // Loop Numbers to Find Bins
        $bins = array();
        for($i = 1; $i <= 10; $i++) {
            // Get Keys
            $id  = 'Bin ' . $i . ' ID';
            $qty = 'Bin ' . $i . ' qty';
            $loc = 'Bin ' . $i . ' location';

            // Get Values
            $binId = $this->getOrCreate(array(
                'dealer_id' => $dealerId,
                'bin_name' => $csvData[$keyToIndexMapping[$id]],
                'location' => $csvData[$keyToIndexMapping[$loc]],
            ))->id;

            // Return Bin Array
            $bins[] = array(
                'bin_id'   => $binId,
                'quantity' => $csvData[$keyToIndexMapping[$qty]]
            );
        }

        // Return Bins Array
        return $bins;
    }

}
