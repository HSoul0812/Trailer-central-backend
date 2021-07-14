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
            $emailHistoryId = $event->message->getHeaders()->get('X-Email-History-ID');
            if(!empty($sesMessageId) && !empty($emailHistoryId)) {
                $messageId = $sesMessageId->getValue();
                $emailId = $emailHistoryId->getValue();

                // Update Message ID on Email History ID
                //$this->emailbuilder->replaceMessageId($emailId, $messageId);
            }
        }
    }
}
