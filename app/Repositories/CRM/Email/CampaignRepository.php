<?php

namespace App\Repositories\CRM\Email;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Models\CRM\Email\CampaignSent;
use App\Exceptions\NotImplementedException;

class CampaignRepository implements CampaignRepositoryInterface {

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
     * Mark Campaign as Sent
     * 
     * @param array $params
     * return CampaignSent
     */
    public function sent($params) {
        DB::beginTransaction();

        try {
            // Create Campaign Sent
            $stop = CampaignSent::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $stop;
    }
}
