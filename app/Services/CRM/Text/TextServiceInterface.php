<?php

namespace App\Services\CRM\Text;

interface TextServiceInterface {
    /**
     * Send Text
     * 
     * @param string $from_number
     * @param string $to_number
     * @param string $textMessage
     * @param string $fullName
     * @return result || array with error
     */
    public function send($from_number, $to_number, $textMessage, $fullName);
}