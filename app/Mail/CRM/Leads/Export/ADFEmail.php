<?php

namespace App\Mail\CRM\Leads\Export;

use App\Services\CRM\Leads\DTOs\ADFLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ADFEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param ADFLead $adf
     */
    public function __construct(ADFLead $adf)
    {
        // Set Extra Vars
        $this->data    = $adf->getEmailVars();
        $this->subject = $adf->subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address', 'postmaster@trailercentral.com');

        $build = $this->from($from, $this->data['providerName']);

        $build->subject($this->subject);

        $build->text('emails.leads.adf');

        $build->with($this->data);

        return $build;
    }

}
