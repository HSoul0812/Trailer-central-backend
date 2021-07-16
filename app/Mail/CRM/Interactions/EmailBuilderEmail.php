<?php

namespace App\Mail\CRM\Interactions;

use App\Mail\CRM\CustomEmail;

class EmailBuilderEmail extends CustomEmail
{
    /**
     * @const string
     */
    const BLADE_HTML = 'emails.interactions.emailbuilder-email';
    const BLADE_PLAIN = null;
}
