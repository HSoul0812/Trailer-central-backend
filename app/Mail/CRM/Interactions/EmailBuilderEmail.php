<?php

namespace App\Mail\Interactions;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailBuilderEmail extends Mailable
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
        $this->subject  = $data['subject'];
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

        $build->view('emails.interactions.emailbuilder-email');

        $build->with($this->data);

        return $build;
    }
}
