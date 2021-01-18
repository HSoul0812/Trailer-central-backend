<?php

namespace App\Mail\CRM\Leads\Export;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IDSEmail extends Mailable
{
    
    const SUBJECT = 'You have a request from your website';
    
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
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $build = $this->from(config('mail.from.address'), config('mail.from.name'));
        $build->subject(self::SUBJECT);

        $build->view('emails.leads.ids');

        $build->with($this->data);

        return $build;
    }

}
