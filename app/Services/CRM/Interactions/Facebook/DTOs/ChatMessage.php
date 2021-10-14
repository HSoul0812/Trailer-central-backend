<?php

namespace App\Services\CRM\Interactions\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ChatMessage
 * 
 * @package App\Services\CRM\Interactions\Facebook\DTOs
 */
class ChatMessage
{
    use WithConstructor, WithGetter;

    /**
     * @var int Sender ID
     */
    private $fromId;

    /**
     * @var int Recipient ID
     */
    private $toId;

    /**
     * @var string Message ID
     */
    private $messageId;

    /**
     * @var string Text Message
     */
    private $text;

    /**
     * @var string Created At
     */
    private $createdAt;
}