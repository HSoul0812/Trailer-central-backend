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

    const BIN_ID = 'Bin XXXX ID';
    const BIN_QTY = 'Bin XXXX qty';
    const BIN_LOC = 'Bin XXXX location';

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
            $id  = str_replace('XXXX', $i, BIN_ID);
            $qty = str_replace('XXXX', $i, BIN_QTY_ID);
            $loc = str_replace('XXXX', $i, BIN_LOC_ID);

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
