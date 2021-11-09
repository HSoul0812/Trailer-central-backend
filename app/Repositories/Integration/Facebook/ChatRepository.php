<?php

namespace App\Repositories\Integration\Facebook;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Facebook\Chat;
use App\Repositories\Traits\SortTrait;

class ChatRepository implements ChatRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Chat
     * 
     * @param array $params
     * @return Chat
     */
    public function create($params) {
        // Create Chat
        return Chat::create($params);
    }

    /**
     * Delete Chat
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Chat
        return Chat::findOrFail($id)->delete();
    }

    /**
     * Get Chat
     * 
     * @param array $params
     * @return Chat
     */
    public function get($params) {
        // Find Chat By ID
        return Chat::findOrFail($params['id']);
    }

    /**
     * Get All Chats That Match Params
     * 
     * @param array $params
     * @return Collection<Chat>
     */
    public function getAll($params) {
        $query = Chat::where('user_id', '=', $params['user_id']);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['sales_person_id'])) {
            $query = $query->where('sales_person_id', $params['sales_person_id']);
        } else {
            $query = $query->where('sales_person_id', 0);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Chat
     * 
     * @param array $params
     * @return Chat
     */
    public function update($params) {
        $chat = Chat::findOrFail($params['id']);

        DB::transaction(function() use (&$chat, $params) {
            // Fill Chat Details
            $chat->fill($params)->save();
        });

        return $chat;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * Assign Sales Person to Chat
     * 
     * @param int $id
     * @param array $sales_person_ids
     * @return array
     */
    public function assignSalespeople($id, $sales_person_ids) {
        // Find Chat by ID then assign Sales Person
        return Chat::findOrFail($id)->salesPersons()->sync($sales_person_ids);
    }
}
