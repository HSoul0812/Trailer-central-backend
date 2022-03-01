<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberVerify;

interface NumberRepositoryInterface extends Repository {
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

    /**
     * Find Active Twilio Number
     * 
     * @param type $dealerNo
     * @param type $customerNo
     * @return Number
     */
    public function findActiveTwilioNumber($dealerNo, $customerNo);

    /**
     * Find All Twilio Numbers
     * 
     * @param type $dealerNo
     * @param type $customerNo
     * @return array Number
     */
    public function findAllTwilioNumbers($dealerNo, $customerNo);

    /**
     * Is Active Twilio Number?
     * 
     * @param string $twilioNumber
     * @param string $maskedNumber
     * @return Number
     */
    public function isActiveTwilioNumber(string $twilioNumber, string $maskedNumber): Number;


    /**
     * Delete Twilio Number
     * 
     * @param string $phone
     * @return bool
     */
    public function deleteTwilioNumber(string $phone): bool;

    /**
     * Find All Expired Numbers (Chunked)
     * 
     * @param Closure $callable
     * @param int $toDate
     * @param int $chunkSize
     * @return void
     */
    public function getAllExpiredChunked(\Closure $callable, int $toDate, int $chunkSize = 500): void;


    /**
     * Is Verify Twilio Number?
     * 
     * @param string $twilioNumber
     * @param string $dealerNumber
     * @return NumberVerify
     */
    public function isVerifyNumber(string $twilioNumber, string $dealerNumber): NumberVerify;
}