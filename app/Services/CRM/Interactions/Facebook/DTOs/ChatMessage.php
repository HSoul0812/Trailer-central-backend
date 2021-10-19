<?php

namespace App\Services\CRM\Interactions\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use FacebookAds\Object\AbstractCrudObject;
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
        // Get Data
        $data = $message->exportAllData();

        // Create ChatMessage
        return new self([
            'message_id' => $data['id'],
            'created_at' => Carbon::parse($data['created_time'])->toDateTimeString(),
            'from_id' => $data['from']['id'],
            'to_id' => self::parseToId($data['to']),
            'text' => $data['message'],
            'tags' => self::parseTags($data['tags']['data'])
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
}