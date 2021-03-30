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
        $name = config('mail.from.name', 'Trailer Central');

        $build = $this->from($from, $name);

        $build->getInquiryView();

        $build->with($this->data);

        return $build;
    }

    /**
     * Get Inquiry Views
     * 
     * @return type
     */
    private function getInquiryView() {
        // Check Type
        $view = $this->data['inquiryType'];

        // CTA Must be General!
        if($view === 'cta') {
            $view = 'general';
        }

        // Set Templates
        return $this->view('emails.leads.inquiry-' . $view)
                    ->text('emails.leads.inquiry-' . $view . '-plain');
    }
}
