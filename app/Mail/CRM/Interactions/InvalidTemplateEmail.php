<?php

namespace App\Mail\CRM\Interactions;

use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvalidTemplateEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @const string
     */
    const BLADE_HTML = 'emails.interactions.invalid-template';
    const BLADE_PLAIN = 'emails.interactions.invalid-template-plain';

    /**
     * Create a new message instance.
     *
     * @param BuilderEmail $config
     * @param string $launchUrl
     */
    public function __construct(BuilderEmail $config, string $launchUrl)
    {
        $this->data     = [
            'name' => $config->name,
            'toName' => $config->toName,
            'typeName' => $config->getTypeName(),
            'launchUrl' => $launchUrl
        ];
        $this->subject  = 'Notice: Could not send template named "' . $config->name . '"!';
    }

    /**
     * Build the message.
     *
     * @return CustomEmail
     */
    public function build()
    {
        // Initialize Build
        $build = $this;

        // Set HTML and Plain
        $build->view(self::BLADE_HTML)
              ->text(self::BLADE_PLAIN);

        $build->with($this->data);

        return $build;
    }
}
