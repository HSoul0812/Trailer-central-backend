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
     * @param array $data
     */
    public function __construct(array $data)
    {
        // Set Extra Vars
        $data['year']      = date('Y');
        $data['bgcolor']   = (($data['website'] === 'trailertrader.com') ? '#ffff00': '#ffffff');
        $data['bgheader']  = (($data['website'] === 'trailertrader.com') ? '#00003d': 'transparent');

        // Prepare Email Data
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

        $build->getInquiryView();

        $build->with($this->data);

        return $build;
    }

    /**
     * Get Inquiry Type
     * 
     * @param array $data
     */
    private function getInquiryType($data) {
        // Get Type
        $type = $data['inquiry_type'];
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

    /**
     * Build Subject
     * 
     * @param array $data
     */
    private function getSubject($data) {
        // Initialize
        switch($this->inquiryType) {
            case "call":
                $subject = "You Just Received a Click to Call From %s";
                return sprintf($subject, trim($data['first_name'] . ' ' . $data['last_name']));
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
            default:
                $subject = "New SMS Sent on %s";
            break;
        }

        // Generate subject depending on type
        return sprintf($subject, $data['website_domain']);
    }
}
