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
        // Get Sent?
        $sent = $this->getSent($blastId, $leadId);
        if(!empty($sent->email_blasts_id)) {
            return $sent;
        }

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
     * @param string|null $messageId
     * @throws \Exception
     * @return BlastSent
     */
    public function updateSent(int $blastId, int $leadId, ?string $messageId, int $emailHistoryId): BlastSent {
        // Get Blast Sent Entry
        $sent = BlastSent::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();

        if(empty($sent->email_blasts_id)) {
            return $this->sent($blastId, $leadId, $messageId);
        }

        $params = ['crm_email_history_id' => $emailHistoryId];

        if ($messageId) {
            $params['message_id'] = $messageId;
        }

        // Update Message ID
        $sent->fill($params);

        // Save Blast Sent
        $sent->save();

        return $sent;
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

    /**
     * Get Blast Sent Entry for Lead
     *
     * @param int $blastId
     * @param int $leadId
     * @return null|BlastSent
     */
    public function getSent(int $blastId, int $leadId): ?BlastSent {
        // Get Blast Sent Entry
        return BlastSent::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();
    }

    /**
     * Was Blast Already Sent to Lead?
     *
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasLeadSent(int $blastId, int $leadId): bool {
        // Get Blast Sent Entry
        $sent = $this->getSent($blastId, $leadId);

        // Successful?
        return !empty($sent->email_blasts_id);
    }
}
