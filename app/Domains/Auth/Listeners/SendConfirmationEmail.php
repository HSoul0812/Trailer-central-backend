<?php

namespace App\Domains\Auth\Listeners;

use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Log;

class SendConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
            try {
                $event->user->sendEmailVerificationNotification();
            } catch (Exception $exception) {
                Log::channel('error')->error($exception->getMessage());
            }
        }
    }
}
