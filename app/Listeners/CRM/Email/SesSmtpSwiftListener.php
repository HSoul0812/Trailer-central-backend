<?php

namespace App\Listeners\CRM\Email;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;

/**
 * Class SesSmtpSwiftListener
 *
 * @package App\Listeners\CRM\Email
 */
class SesSmtpSwiftListener implements Swift_Events_SendListener
{
    /**
     * Invoked immediately before the Message is sent.
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        // not used
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        // Check Event Results
        $message = $evt->getMessage();

        // SES Message Tags
        $messageTags = $message->getHeaders()->get('X-SES-MESSAGE-TAGS');
        if(!empty($messageTags)) {
            $email = $messageTags->getValue();
            print_r($email);

            // Get Result
            $result = $evt->getResult();
            print_r($result);
        }
    }
}
