<?php

namespace App\Mail;

use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\LeadType;
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
        $this->data    = $inquiry->getEmailVars();
        $this->subject = $inquiry->getSubject();
        
        if (empty($this->data['inquiryView'])) {
            $this->data['inquiryView'] = LeadType::TYPE_GENERAL;
        }
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

        if(!empty($this->data['email'])) {
            $build->replyTo($this->data['email'], $this->data['fullName']);
        }

        $build->view('emails.leads.inquiry.' . $this->data['inquiryView'])
              ->text('emails.leads.inquiry.' . $this->data['inquiryView'] . '-plain');

        $build->with($this->data);

        return $build;
    }
}
