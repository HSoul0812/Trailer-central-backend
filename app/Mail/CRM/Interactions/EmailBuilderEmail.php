<?php

namespace App\Mail\CRM\Interactions;

use App\Services\Integration\Common\DTOs\ParsedEmail;
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
     * @param ParsedEmail $email
     */
    public function __construct(ParsedEmail $email)
    {
        $this->data     = ['body' => $email->body];
        $this->subject  = $email->subject;
        $this->callbacks[] = function ($message) use ($email) {
            if(isset($email->messageId)) {
                $message->getHeaders()->get('Message-ID')->setId($email->messageId);
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

        $build->view('emails.interactions.emailbuilder-email');

        $build->with($this->data);

        return $build;
    }
}
