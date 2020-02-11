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
        // Bin ID Exists?
        if(isset($params['bin_id'])) {
            return Bin::where('id', $params['bin_id'])->first();
        }

        // Get By Dealer ID and Name
        return Bin::where('bin_name', $params['bin_name'])->where('dealer_id', $params['dealer_id'])->first();
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

            // Get Values
            $binId = $this->get(array(
                'dealer_id' => $dealerId,
                'bin_name' => $csvData[$keyToIndexMapping[$id]],
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
