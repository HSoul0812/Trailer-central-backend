<?php

namespace App\Repositories\CRM\Interactions\Facebook;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConversationRepository implements ConversationRepositoryInterface {

    use SortTrait;

    private $sortOrders = [
        '-newest_update' => [
            'field' => 'newest_update',
            'direction' => 'ASC'
        ],
        'newest_update' => [
            'field' => 'newest_update',
            'direction' => 'DESC'
        ],
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
        // Create Conversation
        return Conversation::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        // Return Conversation By Conversation ID
        if(isset($params['conversation_id'])) {
            return Conversation::where('conversation_id', $params['conversation_id'])->first();
        }

        // Return Conversation By ID
        return Conversation::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Conversation::where('identifier', '>', 0);

        if (isset($params['dealer_id'])) {
            $query = $query->leftJoin(Page::getTableName(), Page::getTableName().'.page_id',  '=', Conversation::getTableName().'.page_id');
            $query = $query->where(Page::getTableName().'.dealer_id', $params['dealer_id']);
        }

        if (isset($params['page_id'])) {
            $query = $query->where(Conversation::getTableName().'.page_id', $params['page_id']);
        }

        if (isset($params['user_id'])) {
            $query = $query->where(Conversation::getTableName().'.user_id', $params['user_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }

        $query = $query->groupBy(Conversation::getTableName().'.id');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        // Get Conversation
        $conversation = $this->find($params);
        if(empty($conversation->id)) {
            throw new ModelNotFoundException;
        }

        // Update Conversation
        DB::transaction(function() use (&$conversation, $params) {
            $conversation->fill($params)->save();
        });

        // Return Full Details
        return $conversation;
    }

    /**
     * Find By ID or Conversation ID
     * 
     * @param array $params
     * @return null|Conversation
     */
    public function find(array $params): ?Conversation {
        // Get Conversation By ID
        if(isset($params['id'])) {
            $conversation = Conversation::find($params['id']);
        }

        // Get Conversation By Conversation ID
        if(empty($conversation->id) && isset($params['conversation_id'])) {
            $conversation = Conversation::where('conversation_id', $params['conversation_id'])->first();
        }

        // Return Full Details
        return $conversation ?? null;
    }

    /**
     * Create Or Update Conversation
     * 
     * @param array $params
     * @return Conversation
     */
    public function createOrUpdate(array $params): Conversation {
        // Get Conversation
        $conversation = $this->find($params);

        // If Exists, Then Update
        if(!empty($conversation->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    /**
     * Find By Page ID and User ID
     * 
     * @param int $pageId
     * @param int $userId
     * @return null|Conversation
     */
    public function getByParticipants(int $pageId, int $userId): ?Conversation {
        // Get Conversation By ID
        return Conversation::where('page_id', $pageId)->where('user_id', $userId)->first();
    }

    /**
     * Get Newest Conversation Update From Page
     * 
     * @param int $pageId
     * @return null|string
     */
    public function getNewestUpdate(int $pageId): ?string {
        return Conversation::where('page_id', $pageId)->max('newest_update');
    }
}
