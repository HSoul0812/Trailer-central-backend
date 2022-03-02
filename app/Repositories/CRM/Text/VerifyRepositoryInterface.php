<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\GenericRepository;
use App\Models\CRM\Text\NumberVerify;
use App\Models\CRM\Text\NumberVerifyCode;

interface VerifyRepositoryInterface extends GenericRepository {
    /**
     * Create Verify Number
     * 
     * @param string $dealerNumber
     * @param string $twilioNumber
     * @param string $type
     * @return NumberVerify
     */
    public function create(string $dealerNumber, string $twilioNumber, string $type): NumberVerify;

    /**
     * Get Verify Number
     * 
     * @param string $dealerNumber
     * @param null|string $type
     * @return null|NumberVerify
     */
    public function get(string $dealerNumber, ?string $type = null): ?NumberVerify;

    /**
     * Is Verify Number?
     * 
     * @param string $twilioNumber
     * @param null|string $dealerNumber
     * @return null|NumberVerify
     */
    public function exists(string $twilioNumber, ?string $dealerNumber = null): ?NumberVerify;


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