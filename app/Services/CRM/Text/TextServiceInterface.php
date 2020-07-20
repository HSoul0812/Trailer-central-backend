<?php

namespace App\Services\CRM\Text;

use Twilio\Rest\Client;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
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