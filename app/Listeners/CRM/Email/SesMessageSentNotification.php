<?php

namespace App\Listeners\CRM\Email;

use App\Services\CRM\Email\EmailBuilderServiceInterface;
use Illuminate\Mail\Events\MessageSent;

/**
 * Class SesMessageSentNotification
 * 
 * @package App\Listeners\CRM\Email
 */
class SesMessageSentNotification
{
    /**
     * @var EmailBuilderService
     */
    private $emailbuilder;

    /**
     * Initialize EmailBuilderServiceInterface
     * 
     * @param EmailBuilderService $emailbuilder
     */
    public function __construct(EmailBuilderServiceInterface $emailbuilder) {
        $this->emailbuilder = $emailbuilder;
    }

    /**
     * Handle MessageSent Event
     * 
     * @param MessageSent $event
     */
    public function handle(MessageSent $event)
    {
        if ($event->message) {
            // Check Headers
            $sesMessageId = $event->message->getHeaders()->get('X-SES-Message-ID');
            $builderType = $event->message->getHeaders()->get('X-Builder-Email-Type');
            if(!empty($sesMessageId) && !empty($builderType)) {
                $messageId = $sesMessageId->getValue();

                // Get Builder Details
                $type = $builderType->getValue();
                $id = $event->message->getHeaders()->get('X-Builder-Email-ID')->getValue();
                $lead = $event->message->getHeaders()->get('X-Builder-Email-Lead')->getValue();
                $history = $event->message->getHeaders()->get('X-Builder-History-ID')->getValue();

                // Update Message ID on Email History ID
                $this->emailbuilder->replaceMessageId($type, $id, $lead, $history, $messageId);
            }
        }
    }
}
