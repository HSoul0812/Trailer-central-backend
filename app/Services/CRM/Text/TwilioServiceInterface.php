<?php

namespace App\Services\CRM\Text;

use Twilio\Rest\Api\V2010\Account\MessageInstance;

interface TwilioServiceInterface {
    /**
     * Send Text to Twilio
     *
     * @param string $from_number
     * @param string $to_number
     * @param string $textMessage
     * @param string $fullName
     * @param array $mediaUrl
     * @param int|null $dealerId
     * @return MessageInstance
     */
    public function send(string $from_number, string $to_number, string $textMessage, string $fullName, array $mediaUrl = [], ?int $dealerId = null): MessageInstance;

    /**
     * Get All Twilio Phone Numbers on Account
     *
     * @param int $max number of results to return
     * @return array<string>
     */
    public function numbers(int $max = 20): array;

    /**
     * Get Twilio Numbers Missing From DB
     *
     * @param int $max number of results to return
     * @return array<string>
     */
    public function missing(int $max = 20): array;

    /**
     * Release Twilio Number
     *
     * @param string $number
     * @return bool | true if successfully deleted from Twilio OR DB; false if failed to delete from both
     */
    public function delete(string $number): bool;

    /**
     * @param string $fromPhone
     * @param string $toNumber
     * @param string $textMessage
     * @param array $mediaUrl
     * @return MessageInstance
     */
    public function sendViaTwilio(string $fromPhone, string $toNumber, string $textMessage, array $mediaUrl = []): MessageInstance;

    /**
     * @param string $phoneNumber
     * @return bool
     */
    public function isValidPhoneNumber(string $phoneNumber): bool;

    /**
     * @return bool
     */
    public function getIsNumberInvalid(): bool;
}
