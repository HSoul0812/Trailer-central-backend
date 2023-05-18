<?php

namespace App\Mail\CRM\Leads\Export;

use App\Services\CRM\Leads\DTOs\IDSLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IDSEmail extends Mailable
{   
    use Queueable, SerializesModels;
    
    const SUBJECT = 'You have a request from your website';

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param IDSLead $ids
     */
    public function __construct(IDSLead $ids)
    {
        // Set Extra Vars
        $this->data = $ids->getEmailVars();
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
