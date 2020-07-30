<?php

namespace App\Mail;

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
    private $validInquiryTypes = array(
        'general',
        'cta',
        'inventory',
        'part',
        'showroom',
        'sms'
    );

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data        = $data;
        $this->inquiryType = $this->getInquiryType($data);
        $this->subject     = $this->getSubject($data);
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

        $build->view('emails.leads.inquiry-' . $this->inquiryType . '-email')
            ->text('emails.leads.inquiry-' . $this->inquiryType . '-email-plain');

        $build->with($this->data);

        return $build;
    }

    /**
     * Get Inquiry Type
     * 
     * @param array $data
     */
    public function getInquiryType($data) {
        // Get Type
        $type = $data['inquiry_type'];
        if(!in_array($type, $this->validInquiryTypes)) {
            $type = $this->inquiryType;
        }

        // Set New Type
        return $type;
    }

    /**
     * Build Subject
     * 
     * @param array $data
     */
    public function getSubject($data) {
        // Initialize
        switch($this->inquiryType) {
            case "inventory":
                $subject = 'Inventory Information Request on %s';
            break;
            case "part":
                $subject = "Inventory Part Information Request on %s";
            break;
            case "showroom":
                $subject = "Showroom Model Information Request on %s";
            break;
            case "cta":
                $subject = "New CTA Response on %s";
            break;
            case "sms":
                $subject = "New SMS Sent on %s";
            break;
        }

        // Generate subject depending on type
        return sprintf($subject, $data['website_domain']);
    }
}
