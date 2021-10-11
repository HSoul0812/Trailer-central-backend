<?php

namespace App\Repositories\CRM\Interactions\Facebook;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Interactions\Facebook\ConversationRepositoryInterface;
use App\Models\CRM\Interactions\Conversation;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

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
        // Create Lead
        return Conversation::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
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
        // Get Lead
        $conversation = Conversation::findOrFail($params['id']);

        // Update Lead
        DB::transaction(function() use (&$conversation, $params) {
            $conversation->fill($params)->save();
        });

        // Return Full Details
        return $conversation;
    }
}
