<?php

namespace App\Listeners\CRM\Email;

use Illuminate\Mail\Events\MessageSent;

/**
 * Class EmailBuilderNotification
 *
 * @package App\Listeners\CRM\Email
 */
class EmailBuilderNotification
{
    public function handle(MessageSent $message)
    {
        dd($message);
    }
}
