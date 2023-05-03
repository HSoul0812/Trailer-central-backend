<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailedToFetchBotIpEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $providerName,
        public string $url,
        public string $errorMessage,
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->to(config('crawlers.report.cache_crawlers_ip_addresses.mail_to'))
            ->subject("Failed to fetch $this->providerName bots IP addresses!")
            ->markdown('emails.failed-to-fetch-bots-ip');
    }
}
