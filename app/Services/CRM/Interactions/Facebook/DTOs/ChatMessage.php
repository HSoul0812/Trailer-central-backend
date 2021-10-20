<?php

namespace App\Services\CRM\Interactions\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use FacebookAds\Object\AbstractCrudObject;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class ChatMessage
 * 
 * @package App\Services\CRM\Interactions\Facebook\DTOs
 */
class ChatMessage
{
    use WithConstructor, WithGetter;

    /**
     * @var string Message ID
     */
    private $messageId;

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
     * Get Tags String
     * 
     * @return string
     */
    public function getTags(): string {
        // Initialize Tags
        $tags = '';

        // Loop Tags
        if($this->tags) {
            foreach($this->tags as $tag) {
                if(!empty($tags)) {
                    $tags .= ',' . $tag;
                }
            }
        }

        // Return Tags
        return $tags;
    }


    /**
     * Get From Crud
     * 
     * AbstractCrudObject $message
     * @return ChatMessage
     */
    public static function getFromCrud(AbstractCrudObject $message, string $conversationId): ChatMessage {
        // Get Data
        $data = $message->exportAllData();

        // Create ChatMessage
        return new self([
            'message_id' => $data['id'],
            'conversation_id' => $conversationId,
            'created_at' => Carbon::parse($data['created_time'])->toDateTimeString(),
            'from_id' => $data['from']['id'],
            'to_id' => self::parseToId($data['to']),
            'text' => $data['message'],
            'tags' => self::parseTags($data['tags']['data'])
        ]);
    }

    /**
     * Get From Crud
     * 
     * AbstractCrudObject $message
     * @return ChatMessage
     */
    public static function getFromWebhook(array $message, string $conversationId): ChatMessage {
        // Create ChatMessage
        return new self([
            'message_id' => $message['message']['mid'],
            'conversation_id' => $conversationId,
            'created_at' => Carbon::parse($message['timestamp'])->toDateTimeString(),
            'from_id' => $message['sender']['id'],
            'to_id' => $message['recipient']['id'],
            'text' => $message['message']['text']
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
            $collection->push($tag['name']);
        }

        // Return Result
        return $collection;
    }

    /**
     * Parse To ID From To Array
     * 
     * @param array $to
     * @return int
     */
    public static function parseToId(array $to): int {
        // Parse To Data
        if(isset($to['data'])) {
            if(is_array($to['data'])) {
                $toId = (int) $to['data'][0]['id'];
            }
        }

        // Get To ID
        if(isset($to['id'])) {
            $toId = (int) $to['id'];
        }

        // Return Result
        return $toId ?? 0;
    }

    /**
     * Get Params For Conversation
     * 
     * @return array{message_id: string,
     *               conversation_id: string,
     *               from_id: int,
     *               to_id: int,
     *               message: string,
     *               tags: string,
     *               created_at: string}
     */
    public function getParams(): array {
        return [
            'message_id' => $this->messageId,
            'conversation_id' => $this->conversationId,
            'from_id' => $this->fromId,
            'to_id' => $this->toId,
            'message' => $this->text,
            'tags' => $this->getTags(),
            'created_at' => $this->createdAt
        ];
    }
}