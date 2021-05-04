<?php

namespace App\Repositories\CRM\Email;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Models\CRM\Email\BlastSent;

class BlastRepository implements BlastRepositoryInterface {

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

    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * return BlastSent
     */
    public function sent($params) {
        DB::beginTransaction();

        try {
            // Create Blast Sent
            $stop = BlastSent::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $stop;
    }
}
