<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException
use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BlastRepository implements BlastRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Blast::findOrFail($params['id']);
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
