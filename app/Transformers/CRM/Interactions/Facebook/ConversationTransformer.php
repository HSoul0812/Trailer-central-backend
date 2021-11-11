<?php

namespace App\Transformers\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Transformers\CRM\Interactions\Facebook\MessageTransformer;
use League\Fractal\TransformerAbstract;

class ConversationTransformer extends TransformerAbstract 
{
    /**
     * @var array
     */
    protected $defaultIncludes = [
        'messages'
    ];

    /**
     * @var MessageTransformer
     */
    private $messageTransformer;

    /**
     * ConversationTransformer constructor.
     * 
     * @param MessageTransformer $messageTransformer
     */
    public function __construct(MessageTransformer $messageTransformer) {
        $this->messageTransformer = $messageTransformer;
    }

    /**
     * Transform Conversation
     * 
     * @param Conversation $conversation
     * @return array
     */
    public function transform(Conversation $conversation)
    {
	return [
            "id" => $conversation->id,
            "conversation_id" => $conversation->conversation_id,
            "page_id" => $conversation->conversation_id,
            "user_id" => $conversation->user_id,
            "link" => $conversation->link,
            "snippet" => $conversation->snippet,
            "newest_update" => $conversation->newest_update,
            "incoming_update" => $conversation->incoming_update,
            "created_at" => $conversation->created_at,
            "updated_at" => $conversation->updated_at
        ];
    }

    /**
     * Included Messages
     * 
     * @param Conversation $conversation
     * @return type
     */
    public function includeMessages(Conversation $conversation)
    {
        return $this->collection($conversation->messages, $this->messageTransformer);
    }
}
