<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Text\VerifyRepositoryInterface;
use App\Repositories\CRM\Text\DealerLocationRepositoryInterface;
use App\Models\CRM\Text\NumberVerify;
use App\Models\CRM\Text\NumberVerifyCode;
use App\Services\CRM\Text\TextServiceInterface;
use Carbon\Carbon;

class VerifyRepository implements VerifyRepositoryInterface {

    /**
     * @var TextServiceInterface
     */
    private $service;

    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocation;

    private $sortOrders = [
        'date_sent' => [
            'field' => 'date_sent',
            'direction' => 'DESC'
        ],
        '-date_sent' => [
            'field' => 'date_sent',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Verify Number
     * 
     * @param string $dealerNumber
     * @param string $twilioNumber
     * @param string $type
     * @return NumberVerify
     */
    public function create(string $dealerNumber, string $twilioNumber, string $type): NumberVerify {
        // Return New NumberVerify
        return NumberVerify::create([
            'dealer_number' => $dealerNumber,
            'twilio_number' => $twilioNumber,
            'verify_type' => $type
        ]);
    }

    public function delete($params) {
        throw new NotImplementedException();
    }

    /**
     * Get Verify Number
     * 
     * @param string $dealerNumber
     * @param null|string $type
     * @return null|NumberVerify
     */
    public function get(string $dealerNumber, ?string $type = null): ?NumberVerify {
        // Return NumberVerify
        $number = NumberVerify::where('dealer_number', $dealerNumber);

        // Filter By Type
        if($type !== null) {
            $number = $number->where('verify_type', $type);
        }

        // Return First Item
        return $number->first();
    }

    public function getAll($params) {
        throw new NotImplementedException();
    }

    public function update($params) {
        throw new NotImplementedException();
    }

    /**
     * Verify Number Exists?
     * 
     * @param string $twilioNumber
     * @param null|string $dealerNumber
     * @return null|NumberVerify
     */
    public function exists(string $twilioNumber, ?string $dealerNumber = null): ?NumberVerify {
        // Return NumberVerify
        $verify = NumberVerify::where('twilio_number', $twilioNumber);

        // Also Match Dealer Number
        if($dealerNumber !== null) {
            $verify = $verify->where('dealer_number', $dealerNumber);
        }

        // Return Verify Number
        return $verify->first();
    }


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
                                     string $code, ?bool $success = null): NumberVerifyCode {
        // Initialize Verify Code Params
        $params = [
            'twilio_number' => $twilioNumber,
            'response' => $response,
            'code' => $code
        ];

        // Success Is Not NULL?!
        if($success !== null) {
            $params['success'] = $success;
        }

        // Return NumberVerifyCode
        return NumberVerifyCode::create($params);
    }

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
                                     string $code, bool $success = false): NumberVerifyCode {
        // Get NumberVerifyCode
        $verify = NumberVerifyCode::where('twilio_number', $twilioNumber)->whereNull('success')
                                  ->orderBy('created_at', 'DESC')->firstOrFail();

        DB::transaction(function() use (&$verify, $response, $code, $success) {
            // Update Number
            $verify->fill([
                'response' => $response,
                'code' => $code,
                'success' => $success
            ])->save();
        });

        // Return NumberVerifyCode
        return $verify;
    }
}
