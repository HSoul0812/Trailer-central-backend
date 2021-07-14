<?php

namespace App\Listeners\CRM\Email;

use Illuminate\Mail\Events\MessageSent;

/**
 * Class SesMessageSentNotification
 *
 * 
 *
 * @package App\Listeners\CRM\Email
 */
class SesMessageSentNotification
{
    public function handle(MessageSent $event)
    {
        if ($event->message) {
            // Check Headers
            print_r($event->message->getHeaders());
        }
    }
}
