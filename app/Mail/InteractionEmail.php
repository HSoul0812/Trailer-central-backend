<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InteractionEmail extends Mailable
{
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
        $this->subject  = $data['subject'] ?? 'Trailer Central';
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
        $build = $this;

        if (! empty($this->data['replyToEmail'])) {
            $build->replyTo($this->data['replyToEmail'], $this->data['replyToName']);
        }

        $build->view('emails.interactions.interaction-email')
            ->text('emails.interactions.interaction-email-plain');

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
}
