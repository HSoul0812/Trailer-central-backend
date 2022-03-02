<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;
use App\Models\CRM\Text\NumberVerify;
use App\Models\CRM\Text\NumberVerifyCode;

interface VerifyRepositoryInterface extends Repository {
    /**
     * Is Verify Number?
     * 
     * @param string $twilioNumber
     * @param string $dealerNumber
     * @return NumberVerify
     */
    public function isVerifyNumber(string $twilioNumber, string $dealerNumber): NumberVerify;


    /**
     * Create Verify Code in DB
     * 
     * @param string $twilioNumber
     * @param string $response
     * @param string $code
     * @param null|bool $success
     * @return NumberVerifyCode
     */
    public function createCode(string $twilioNumber, string $response,
                                     string $code, ?bool $success = null): NumberVerifyCode;

    /**
     * Create Verify Code in DB
     * 
     * @param string $twilioNumber
     * @param string $response
     * @param string $code
     * @param boolean $success
     * @return NumberVerifyCode
     */
    public function updateCode(string $twilioNumber, string $response,
                                     string $code, bool $success = false): NumberVerifyCode;
}