<?php

namespace App\Jobs\CRM\Interactions\Facebook;

use App\Jobs\Job;
use App\Services\CRM\Interactions\Facebook\DTOs\ChatMessage;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;

class MessageWebhookJob extends Job
{
    use Dispatchable;


    /**
     * @var ChatMessage $message
     */
    private $message;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdclass $json)
    {
        // Set Chat message From JSON Payload
        $this->message = new ChatMessage([
            'from_id' => $json->sender->from_id,
            'to_id' => $json->recipient->to_id,
            'message_id' => $json->message->mid,
            'message' => $json->message->text,
            'created_at' => Carbon::createFromTimestamp($json->timestamp)->toDateTimeString()
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceInterface $service)
    {
        // Store Final CSV
        return $service->saveMessage($this->message);
    }
}
