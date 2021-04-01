<?php

namespace App\Mail;

use App\Services\CRM\Leads\DTOs\InquiryLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryEmail extends Mailable
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
    public function __construct(InquiryLead $inquiry)
    {
        // Set Extra Vars
        $this->data = $inquiry->getEmailVars();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address', 'noreply@trailercentral.com');

        $build = $this->from($from, $this->data['fromName']);

        $build->view('emails.leads.inquiry.' . $this->data['inquiryView'])
              ->text('emails.leads.inquiry.' . $this->data['inquiryView'] . '-plain');

        $build->with($this->data);

        return $build;
    }
}
