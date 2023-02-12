<?php

namespace App\Mail\Integration;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\User\Integration\DealerIntegration;

class DealerIntegrationEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * DealerIntegration
     *
     * @var DealerIntegration
     */
    public $dealerIntegration;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DealerIntegration $dealerIntegration)
    {
        $this->dealerIntegration = $dealerIntegration;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): DealerIntegrationEmail
    {
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');

        $to = config('support.to.address');
        $toName = config('support.to.name');

        $build = $this->from($from, $fromName);

        return $build->to($to, $toName)
                    ->subject('Dealer has updated an Integration')
                    ->view('emails.integration.dealer-integration');
    }
}
