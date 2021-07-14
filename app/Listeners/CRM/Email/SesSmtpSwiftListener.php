<?php

namespace App\Listeners\CRM\Email;

use Swift_Events_ResponseEvent;
use Swift_Events_ResponseListener;

/**
 * Class SesSmtpSwiftListener
 *
 * @package App\Listeners\CRM\Email
 */
class SesSmtpSwiftListener implements Swift_Events_ResponseListener
{
    /**
     * Invoked immediately after the Message is sent.
     */
    public function responseReceived(Swift_Events_ResponseEvent $evt)
    {
        // Check Event Results
        $source = $evt->getSource();
        print_r($source);
        $response = $evt->getResponse();
        print_r($response);
    }
}
