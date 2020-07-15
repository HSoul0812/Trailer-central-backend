<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AutoAssignEmail extends Mailable
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
            $message->getHeaders()->addTextHeader('Message-ID', $data['id']);
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

        $build->view('emails.leads.autoassign-email')
            ->text('emails.leads.autoassign-email-plain');

        if (! empty($this->data['attach']) && is_array($this->data['attach'])) {
            foreach ($this->data['attach'] as $attach) {
                $build->attach($attach['path'], [
                    'as'    => $attach['as'],
                    'mime'  => $attach['mime']
                ]);
            }
        }

        $build->with($this->data);

        return $build;
    }

    /**
     * Build Subject
     * 
     * @param array $data
     */
    public function getSubject($data) {
        // Initialize
        $subject = 'Assigned to Handle Lead';

        // Lead Name Exists?!
        if(!empty($data['lead_name'])) {
            $subject .= ' "' . $data['lead_name'] . '"';
        }

        // Return Subject
        return $subject;
    }
}
