<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
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
     * @throws \Exception
     * @return BlastSent
     */
    public function sent(array $params): BlastSent {
        DB::beginTransaction();

        try {
            // Create Blast Sent
            $sent = BlastSent::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $sent;
    }

    /**
     * Was Blast Already Sent?
     * 
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasSent(int $blastId, int $leadId): bool {
        // Get Blast Sent Entry
        $sent = BlastSent::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();

        // Was Blast Sent?
        return !empty($sent->email_blasts_id);
    }
}