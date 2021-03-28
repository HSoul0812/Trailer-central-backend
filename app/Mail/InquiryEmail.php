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
     * @var string
     */
    private $inquiryType = 'general';

    /**
     * @var array
     */
    const INQUIRY_TYPES = array(
        'general',
        'cta',
        'inventory',
        'part',
        'showroom',
        'call',
        'sms'
    );

    /**
     * Create a new message instance.
     *
     * @param InquiryLead $inquiry
     */
    public function __construct(InquiryLead $inquiry)
    {
        // Set Extra Vars
        $this->data = [
            'year'     => date('Y'),
            'bgcolor'  => ($inquiry->isTrailerTrader() ? '#ffff00': '#ffffff'),
            'bgheader' => ($inquiry->isTrailerTrader() ? '#00003d': 'transparent')
        ];

        // Prepare Email Data
        $this->inquiryType = $inquiry->inquiryType;
        $this->subject     = $this->getSubject($inquiry);
        $this->callbacks[] = function ($message) use ($data) {
            if(isset($data['id'])) {
                $message->getHeaders()->get('Message-ID')->setId($data['id']);
            }
        };
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

        if (! empty($this->data['replyToEmail'])) {
            $build->replyTo($this->data['replyToEmail'], $this->data['replyToName']);
        }

        $build->getInquiryView();

        $build->with($this->data);

        return $build;
    }

    /**
     * Get Inquiry Type
     * 
     * @param InquiryLead $inquiry
     */
    private function getInquiryType(InquiryLead $inquiry) {
        // Get Type
        $type = $inquiry->inquiryType;
        if(!in_array($type, self::INQUIRY_TYPES)) {
            $type = $this->inquiryType;
        }

        // Set New Type
        return $type;
    }

    /**
     * Get Inquiry Views
     * 
     * @return type
     */
    private function getInquiryView() {
        // Check Type
        $view = $this->inquiryType;

        // CTA Must be General!
        if($view === 'cta') {
            $view = 'general';
        }

        // Set Templates
        return $this->view('emails.leads.inquiry-' . $view)
                    ->text('emails.leads.inquiry-' . $view . '-plain');
    }
}
