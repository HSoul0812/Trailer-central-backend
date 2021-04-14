<?php

namespace App\Mail\CRM\Leads\Export;

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
     * @param InquiryLead $inquiry
     */
    public function __construct(ADFLead $adf, InquiryLead $inquiry)
    {
        // Set Extra Vars
        $this->data    = $adf->getEmailVars();
        $this->subject = $inquiry->getSubject();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address', 'postmaster@trailercentral.com');

        $build = $this->from($from, $this->data['fromName']);

        $build->subject(self::SUBJECT);

        $build->view('emails.leads.ids');

        $build->with($this->data);

        return $build;
    }

}
