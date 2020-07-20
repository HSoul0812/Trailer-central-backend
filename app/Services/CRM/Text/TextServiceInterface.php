<?php

namespace App\Services\CRM\Text;

interface TextServiceInterface {
    /**
     * Send Text to Twilio
     * 
     * @param string $from_number
     * @param string $to_number
     * @param string $textMessage
     * @param string $fullName
     * @return result || array with error
     */
    public function send($from_number, $to_number, $textMessage, $fullName);

    /**
     * Return next available phone number or false if no available phone numbers
     *
     * @return string || boolean false
     */
    public function getNextAvailableNumber();
}