<?php

namespace App\Repositories\CRM\Interactions;

use App\Models\CRM\Interactions\Message;
use App\Repositories\RepositoryAbstract;
use ElasticScoutDriverPlus\Paginator;

/**
 * Class IntegrationRepository
 * @package App\Repositories\Integration
 */
class MessageRepository extends RepositoryAbstract implements MessageRepositoryInterface
{
    /**
     * @param $params
     * @return Message
     */
    public function create($params): Message
    {
        $message = new Message();

        $message->id = $params['id'];
        $message->name = $params['name'];

        //$message->fill($params);
        $message->save();

        return $message;
    }

    /**
     * @param array $params
     * @return Paginator
     */
    public function search(array $params): Paginator
    {
        $search = Message::boolSearch();

        return $search->paginate($params['per_page'])->appends($params);
    }
}
