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

    /**
     * Get All Twilio Phone Numbers on Account
     * 
     * @return array<string>
     */
    public function numbers(): array;

    /**
     * Get Twilio Numbers Missing From DB
     * 
     * @return array<string>
     */
    public function missing(): array;

    /**
     * Release Twilio Number
     * 
     * @param string $number
     * @return bool | true if successfully deleted from Twilio OR DB; false if failed to delete from both
     */
    public function delete(string $number): bool;
}