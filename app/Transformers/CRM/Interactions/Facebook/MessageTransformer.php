<?php

namespace App\Transformers\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Facebook\Message;
use League\Fractal\TransformerAbstract;

class MessageTransformer extends TransformerAbstract 
{
    public function transform(Message $message)
    {
	return [
            "id" => $message->id,
            "message_id" => $message->message_id,
            "conversation_id" => $message->conversation_id,
            "interaction_id" => $message->interaction_id,
            "from_id" => $message->from_id,
            "to_id" => $message->to_id,
            "message" => $message->message,
            "tags" => $message->tags_array,
            "read" => !empty($message->read),
            "direction" => $message->direction,
            "created_at" => $message->created_at,
            "updated_at" => $message->updated_at
        ];
    }
}