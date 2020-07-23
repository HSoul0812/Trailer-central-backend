<?php

namespace App\Services\CRM\Interactions;

interface InteractionEmailServiceInterface {
    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param array $params
     * @throws SendEmailFailedException
     */
    public function send($dealerId, $params);
}