<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
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
     * @param int $blastId
     * @param int $leadId
     * @param null|string $messageId = null
     * @throws \Exception
     * @return BlastSent
     */
    public function sent(int $blastId, int $leadId, ?string $messageId = null): BlastSent {
        DB::beginTransaction();

        try {
            // Create Blast Sent
            $sent = BlastSent::create([
                'email_blasts_id' => $blastId,
                'lead_id' => $leadId,
                'message_id' => $messageId ?? ''
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $sent;
    }

    /**
     * Update Sent Blast
     * 
     * @param int $blastId
     * @param int $leadId
     * @param string $messageId
     * @throws \Exception
     * @return BlastSent
     */
    public function updateSent(int $blastId, int $leadId, string $messageId): BlastSent {
        // Get Blast Sent Entry
        $sent = BlastSent::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();
        if(empty($sent->email_blasts_id)) {
            return $this->sent($blastId, $leadId, $messageId);
        }

        DB::beginTransaction();

        try {
            // Update Message ID
            $sent->fill(['message_id' => $messageId]);

            // Save Blast Sent
            $sent->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $sent;
    }

    /**
     * Replace Sent Message ID
     * 
     * @param string $messageId
     * @param string $newMessageId
     * @return bool
     */
    public function replaceSentMessageId(string $messageId, string $newMessageId): bool {
        DB::beginTransaction();

        try {
            // Get Blast Sent Entry
            $sent = BlastSent::where('message_id', $messageId)->first();

            // Update Blast Sent Message ID
            $sent->fill(['message_id' => $newMessageId]);

            // Save
            $sent->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return false;
        }

        // BlastSent is Valid!
        return !empty($sent->email_blasts_id);
    }

    /**
     * Was Blast Already Sent to Email Address?
     * 
     * @param int $blastId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $blastId, string $email): bool {
        // Get Blast Sent Entry
        $sent = BlastSent::select(BlastSent::getTableName().'.*')
                         ->join(Lead::getTableName(), Lead::getTableName().'.identifier', '=', BlastSent::getTableName().'.lead_id')
                         ->where(BlastSent::getTableName() . '.email_blasts_id', $blastId)
                         ->where(Lead::getTableName() . '.email_address', $email)->first();

        // Was Blast Sent?
        return !empty($sent->email_blasts_id);
    }
}
