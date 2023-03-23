<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailedToFetchBotIpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $providerName,
        public string $url,
        public string $errorMessage,
    )
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->to(config('trailertrader.middlewares.human_only.emails.failed_bot_ips_fetch.mail_to'))
            ->subject("Failed to fetch $this->providerName bots IP addresses!")
            ->markdown('emails.failed-to-fetch-bots-ip');
    }
}
