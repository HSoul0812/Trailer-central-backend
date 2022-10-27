<?php

namespace App\Mail\CRM\Leads;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HotPotatoEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data     = $data;
        $this->subject  = $this->getSubject($data);
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

        $build->view('emails.leads.hotpotato-email')
            ->text('emails.leads.hotpotato-email-plain');

        $build->with($this->data);

        return $build;
    }

    /**
     * Build Subject
     * 
     * @param array $data
     */
    public function getSubject($data) {
        // New Sales Email
        if(!empty($data['new_sales_email'])) {
            $subject = 'Salesperson Failed to Follow-up on Lead';
        } else {
            $subject = 'Reassigned Salesperson for Lead';
        }

        // Lead Name Exists?!
        if(!empty($data['lead_name'])) {
            $subject .= ' "' . $data['lead_name'] . '"';
        }

        // Return Subject
        return $subject;
    }
}
