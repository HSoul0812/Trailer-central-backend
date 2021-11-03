<?php

namespace App\Models\Observers\CRM\Interactions;

use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;

/**
 * Class FbMessageObserver
 * @package App\Models\Observers\CRM\Interactions
 */
class FbMessageObserver
{
    /**
     * @var InteractionMessageRepositoryInterface
     */
    private $interactionMessageRepository;

    /**
     * @param InteractionMessageRepositoryInterface $interactionMessageRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionMessageRepository)
    {
        $this->interactionMessageRepository = $interactionMessageRepository;
    }

    /**
     * @param Message $message
     * @return void
     */
    public function created(Message $message)
    {
        $isIncoming = strcmp($message->from_id, $message->conversation->user_id) === 0;

        $this->interactionMessageRepository->create([
            'message_type' => InteractionMessage::MESSAGE_TYPE_FB,
            'tb_primary_id' => $message->id,
            'tb_name' => Message::getTableName(),
            'name' => null,
            'hidden' => false,
            'is_read' => !$isIncoming,
        ]);
    }

    /**
     * @param Message $message
     * @return void
     */
    public function updated(Message $message)
    {
        $this->interactionMessageRepository->searchable([
            'tb_primary_id' => $message->id,
            'tb_name' => Message::getTableName(),
        ]);
    }

    /**
     * @param Message $message
     * @return void
     */
    public function deleted(Message $message)
    {
        $this->interactionMessageRepository->delete([
            'tb_primary_id' => $message->id,
            'tb_name' => Message::getTableName(),
        ]);
    }
}
