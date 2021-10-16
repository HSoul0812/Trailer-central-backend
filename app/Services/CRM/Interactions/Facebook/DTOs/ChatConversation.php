<?php

namespace App\Services\CRM\Interactions\Facebook\DTOs;

use FacebookAds\Object\AbstractCrudObject;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ChatConversation
 * 
 * @package App\Services\CRM\Interactions\Facebook\DTOs
 */
class ChatConversation
{
    use WithConstructor, WithGetter;

    /**
     * @var string Conversation ID
     */
    private $conversationId;

    /**
     * @var int Sender ID
     */
    private $fromId;

    /**
     * @var int Recipient ID
     */
    private $toId;

    /**
     * @var string Text Message
     */
    private $text;

    /**
     * @var Collection<string> Tags
     */
    private $tags;

    /**
     * @var string Created At
     */
    private $createdAt;


    /**
     * Get From Crud
     * 
     * AbstractCrudObject $message
     * @return ChatMessage
     */
    public static function getFromCrud(AbstractCrudObject $message): ChatMessage {
        return new self([
            'message_id' => $message->id,
            'created_at' => Carbon::parse($message->created_time)->toDateTimeString(),
            'from_id' => $message->from->id,
            'to_id' => $message->to->id,
            'text' => $message->message,
            'tags' => self::parseTags($message->tags->data)
        ]);
    }

    /**
     * Parse Raw Tags Data
     * 
     * @return Collection<string>
     */
    public static function parseTags(array $tags): Collection {
        // Get Tags
        $collection = new Collection();
        foreach($tags as $tag) {
            $collection->push($tag->name);
        }

        // Return Result
        return $collection;
    }
}