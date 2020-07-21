<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
    /**
     * Set Phone as Used
     * 
     * @param string $dealerNo
     * @param string $usedNo
     * @param string $customerNo
     * @param string $customerName
     * @return Number
     */
    public function setPhoneAsUsed($dealerNo, $usedNo, $customerNo, $customerName);

    /**
     * Create Twilio Number
     * 
     * @param string $phoneNumber
     * @return NumberTwilio
     */
    public function createTwilioNumber($phoneNumber);
}