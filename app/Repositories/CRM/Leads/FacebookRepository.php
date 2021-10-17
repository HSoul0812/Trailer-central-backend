<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Models\CRM\Leads\Facebook\User;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class FacebookRepository implements FacebookRepositoryInterface {

    use SortTrait;

    private $sortOrders = [
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ]
    ];

    public function create($params) {
        // Create Lead
        return User::create($params);
    }

    public function delete($params) {
        return User::findOrFail($params['id'])->delete();
    }

    public function get($params) {
        return User::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = User::where('identifier', '>', 0)
                     ->leftJoin(FbLead::getTableName(), FbLead::getTableName().'.user_id',  '=', User::getTableName().'.user_id');

        if (isset($params['dealer_id'])) {
            $query = $query->leftJoin(Page::getTableName(), Page::getTableName().'.page_id',  '=', FbLead::getTableName().'.page_id');
            $query = $query->where(Page::getTableName().'.dealer_id', $params['dealer_id']);
        }

        if (isset($params['page_id'])) {
            $query = $query->where(User::getTableName().'.page_id', $params['page_id']);
        }

        if (isset($params['user_id'])) {
            $query = $query->where(User::getTableName().'.user_id', $params['user_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }

        $query = $query->groupBy(User::getTableName().'.id');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        // Get Lead
        $lead = User::findOrFail($params['id']);

        // Update Lead
        DB::transaction(function() use (&$lead, $params) {
            $lead->fill($params)->save();
        });

        // Return Full Details
        return $lead;
    }

    /**
     * Find By ID or User ID
     * 
     * @param array $params
     * @return null|User
     */
    public function find(array $params): ?User {
        // Get User By ID
        if(isset($params['id'])) {
            $user = User::find($params['id']);
        }

        // Get User By User ID
        if(empty($user->id) && isset($params['user_id'])) {
            $user = User::where('user_id', $params['user_id'])->first();
        }

        // Return Full Details
        return $user ?? null;
    }

    /**
     * Create Or Update User
     * 
     * @param array $params
     * @return User
     */
    public function createOrUpdate(array $params): User {
        // Get User
        $user = $this->find($params);

        // If Exists, Then Update
        if(!empty($user->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    /**
     * Create Facebook Lead
     * 
     * @param int $pageId
     * @param int $userId
     * @param int $leadId
     * @param int $mergeId
     * @return FbLead
     */
    public function convertLead(int $pageId, int $userId, int $leadId, int $mergeId = 0) {
        return FbLead::create([
            'page_id' => $pageId,
            'user_id' => $userId,
            'lead_id' => $leadId,
            'merge_id' => $mergeId
        ]);
    }

    /**
     * Lead Exists for Page/User?
     * 
     * @param int $pageId
     * @param int $userId
     * @return bool
     */
    public function leadExists(int $pageId, int $userId): bool {
        // Find FbLead
        $lead = FbLead::where('page_id', $pageId)->where('user_id', $userId)->first();

        // Lead Exists?
        return !empty($lead->id);
    }
}
