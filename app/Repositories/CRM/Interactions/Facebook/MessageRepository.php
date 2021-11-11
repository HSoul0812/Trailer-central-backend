<?php

namespace App\Repositories\CRM\Interactions\Facebook;

use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;
use App\Models\Integration\Facebook\Page;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;

class MessageRepository implements MessageRepositoryInterface {

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
        return Message::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Message::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Message::where(Message::getTableName() . '.id', '>', 0)
                        ->leftJoin(Conversation::getTableName(), Conversation::getTableName().'.conversation_id', '=', Message::getTableName().'.conversation_id');

        if (isset($params['dealer_id'])) {
            $query = $query->leftJoin(Page::getTableName(), Page::getTableName().'.page_id', '=', Conversation::getTableName().'.page_id')
                           ->where(Page::getTableName().'.dealer_id', $params['dealer_id']);
        }

        if (isset($params['lead_id'])) {
            $query = $query->leftJoin(FbLead::getTableName(), function (JoinClause $join) {
                $join->on(FbLead::getTableName().'.page_id', '=', Conversation::getTableName().'.page_id')
                     ->on(FbLead::getTableName().'.user_id', '=', Conversation::getTableName().'.user_id');
            })->where(FbLead::getTableName().'.lead_id', $params['lead_id']);
        }

        if (isset($params['conversation_id'])) {
            $query = $query->where(Conversation::getTableName().'.conversation_id', $params['conversation_id']);
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

        $query = $query->groupBy(Message::getTableName().'.id');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        // Get Lead
        $message = $this->find($params);
        if(empty($message->id)) {
            throw new ModelNotFoundException;
        }

        // Update Lead
        DB::transaction(function() use (&$message, $params) {
            $message->fill($params)->save();
        });

        // Return Full Details
        return $message;
    }

    /**
     * Find By ID or Message ID
     *
     * @param array $params
     * @return null|Message
     */
    public function find(array $params): ?Message {
        // Get Message By ID
        if(isset($params['id'])) {
            $message = Message::find($params['id']);
        }

        // Get Message By Message ID
        if(empty($message->id) && isset($params['message_id'])) {
            $message = Message::where('message_id', $params['message_id'])->first();
        }

        // Return Full Details
        return $message ?? null;
    }

    /**
     * Create Or Update Message
     *
     * @param array $params
     * @return Message
     */
    public function createOrUpdate(array $params): Message {
        // Get Message
        $message = $this->find($params);

        // If Exists, Then Update
        if(!empty($message->id)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }

    public function bulkUpdate(array $params): bool
    {
        if (empty($params['ids']) || !is_array($params['ids'])) {
            throw new RepositoryInvalidArgumentException('ids has been missed. Params - ' . json_encode($params));
        }

        $ids = $params['ids'];
        unset($params['ids']);

        /** @var Message<Collection> $interactionMessages */
        $interactionMessages = Message::query()->whereIn('id', $ids)->get();

        foreach ($interactionMessages as $interactionMessage) {
            $interactionMessage->update($params);
        }

        return true;
    }
}
