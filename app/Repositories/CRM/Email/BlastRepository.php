<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BlastRepository implements BlastRepositoryInterface
{
    private $blastModel;
    private $blastSentModel;
    private $leadModel;
    private $sortOrders = [
        'id' => [
            'field' => 'email_blasts_id',
            'direction' => 'ASC'
        ],
        '-id' => [
            'field' => 'email_blasts_id',
            'direction' => 'DESC'
        ],
        'name' => [
            'field' => 'campaign_name',
            'direction' => 'ASC'
        ],
        '-name' => [
            'field' => 'campaign_name',
            'direction' => 'DESC'
        ],
    ];

    public function __construct(Blast $blast, BlastSent $blastSent, Lead $lead)
    {
        $this->blastModel = $blast;
        $this->blastSentModel = $blastSent;
        $this->leadModel = $lead;
    }

    public function create($params)
    {
        return $this->blastModel::create($params);
    }

    public function delete($params)
    {
        return $this->blastModel::destroy($params['id']);
    }

    public function get($params)
    {
        return $this->blastModel::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        $query = $this->blastModel::with('template')->with('brands')->with('categories');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 20;
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (isset($params['is_delivered'])) {
            $query = $query->where('delivered', !empty($params['is_delivered']) ? 1 : 0);
        }

        if (isset($params['is_cancelled'])) {
            $query = $query->where('cancelled', !empty($params['is_cancelled']) ? 1 : 0);
        }

        if (isset($params['send_date'])) {
            if ($params['send_date'] === 'due_now') {
                $query = $query->where('send_date', '<', Carbon::now()->toDateTimeString());
            } else {
                $query = $query->where('send_date', '<', $params['send_date']);
            }
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params): Blast
    {
        $blast = $this->get(['id' => $params['id']]);

        DB::beginTransaction();

        try {
            // Fill Text Details
            $blast->fill($params)->save();

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Text blast update error. Message - ' . $ex->getMessage(), $ex->getTrace());
            throw new Exception('Text blast update error');
        }

        return $blast;
    }

    /**
     * Get All Active Blasts For Dealer
     *
     * @param int $userId
     * @return Collection of Blast
     */
    public function getAllActive(int $userId): Collection
    {
        return $this->blastModel::where('user_id', $userId)->where('delivered', 0)
                    ->where(function (Builder $query) {
                        $query->where('cancelled', 0)
                              ->orWhereNull('cancelled');
                    })->where('email_template_id', '<>', 0)
                    ->whereNotNull('email_template_id')
                    ->where('send_date', '<', DB::raw('now()'))->get();
    }

    /**
     * Mark Blast as Sent
     *
     * @param int $blastId
     * @param int $leadId
     * @param null|string $messageId = null
     * @throws Exception
     * @return BlastSent
     */
    public function sent(int $blastId, int $leadId, ?string $messageId = null): BlastSent
    {
        // Get Sent?
        $sent = $this->getSent($blastId, $leadId);
        if (!empty($sent->email_blasts_id)) {
            return $sent;
        }

        DB::beginTransaction();

        try {
            // Create Blast Sent
            $sent = $this->blastSentModel::create([
                'email_blasts_id' => $blastId,
                'lead_id' => $leadId,
                'message_id' => $messageId ?? ''
            ]);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
        }

        return $sent;
    }

    /**
     * Update Sent Blast
     *
     * @param int $blastId
     * @param int $leadId
     * @param string|null $messageId
     * @param int $emailHistoryId
     * @return BlastSent
     * @throws Exception
     */
    public function updateSent(int $blastId, int $leadId, ?string $messageId, int $emailHistoryId): BlastSent
    {
        // Get Blast Sent Entry
        $sent = $this->blastSentModel::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();

        if (empty($sent->email_blasts_id)) {
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
    public function wasSent(int $blastId, string $email): bool
    {
        // Get Blast Sent Entry
        $sent = $this->blastSentModel::select($this->blastSentModel::getTableName().'.*')
                         ->join($this->leadModel::getTableName(), $this->leadModel::getTableName().'.identifier', '=', $this->blastSentModel::getTableName().'.lead_id')
                         ->where($this->blastSentModel::getTableName() . '.email_blasts_id', $blastId)
                         ->where($this->leadModel::getTableName() . '.email_address', $email)->first();

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
    public function getSent(int $blastId, int $leadId): ?BlastSent
    {
        // Get Blast Sent Entry
        return $this->blastSentModel::where('email_blasts_id', $blastId)->where('lead_id', $leadId)->first();
    }

    /**
     * Was Blast Already Sent to Lead?
     *
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasLeadSent(int $blastId, int $leadId): bool
    {
        // Get Blast Sent Entry
        $sent = $this->getSent($blastId, $leadId);

        // Successful?
        return !empty($sent->email_blasts_id);
    }



    /**
     * Add Sort Query
     *
     * @param Builder $query
     * @param string $sort
     * @return Builder
     */
    private function addSortQuery(Builder $query, string $sort): ?Builder
    {
        if (!isset($this->sortOrders[$sort])) {
            return null;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
