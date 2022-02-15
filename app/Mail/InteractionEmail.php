<?php

namespace App\Mail;

use App\Repositories\Traits\MailableAttachmentTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InteractionEmail extends Mailable
{
    use Queueable, SerializesModels, MailableAttachmentTrait;

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

        $build->view('emails.interactions.interaction-email')
              ->text('emails.interactions.interaction-email-plain');

        // Add Attachments
        if(!empty($this->data['attach'])) {
            $this->applyAttachments($build, $this->data['attach']);
        }

        $build->with($this->data);

        return $build;
    }
}
