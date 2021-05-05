<?php

namespace App\Services\CRM\Email;

interface EmailBuilderServiceInterface {
    /**
     * Send Lead Emails for Blast
     * 
     * @param array $params
     * @throws SendBlastEmailsFailedException
     * @return bool
     */
    public function sendBlast(array $params): bool;
}