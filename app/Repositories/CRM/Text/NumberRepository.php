<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\DealerLocationRepositoryInterface;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Models\CRM\Text\NumberVerify;
use App\Models\CRM\Text\NumberVerifyCode;
use App\Services\CRM\Text\TextServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class NumberRepository implements NumberRepositoryInterface {

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
    
    public function create($params) {
        return Number::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException();
    }

    public function get($params) {
        throw new NotImplementedException();
    }

    public function getAll($params) {
        throw new NotImplementedException();
    }

    public function update($params) {
        // Save Text Number
        return Number::findOrFail($params['id'])->fill($params)->save();
    }

    /**
     * Set Phone as Used
     * 
     * @param string $dealerNo
     * @param string $usedNo
     * @param string $customerNo
     * @param string $customerName
     * @return Number
     */
    public function setPhoneAsUsed($dealerNo, $usedNo, $customerNo, $customerName) {
        // Calculate Expiration
        $expirationTime = time() + (Number::EXPIRATION_TIME * 60 * 60);

        // Create Number in DB
        return $this->create([
            'dealer_number'   => $dealerNo,
            'twilio_number'   => $usedNo,
            'customer_number' => $customerNo,
            'customer_name'   => $customerName,
            'expiration_time' => $expirationTime
        ]);
    }

    /**
     * Twilio Number Exists?
     * 
     * @param string $phoneNumber
     * @return bool
     */
    public function existsTwilioNumber(string $phoneNumber): bool {
        $number = NumberTwilio::where('phone_number', $phoneNumber)->first();

        // Successful?
        return !empty($number->phone_number);
    }

    /**
     * Create Twilio Number
     * 
     * @param string $phoneNumber
     * @return NumberTwilio
     */
    public function createTwilioNumber($phoneNumber) {
        return NumberTwilio::create(['phone_number' => $phoneNumber]);
    }

    /**
     * Find Active Twilio Number
     * 
     * @param string $dealerNo
     * @param string $customerNo
     * @return Number
     */
    public function findActiveTwilioNumber($dealerNo, $customerNo) {
        // Return Number
        return Number::where('dealer_number', $dealerNo)
                     ->where('customer_number', $customerNo)
                     ->first();
    }

    /**
     * Find All Twilio Numbers
     * 
     * @param string $dealerNo
     * @param string $customerNo
     * @return array Number
     */
    public function findAllTwilioNumbers($dealerNo, $customerNo) {
        // Return Numbers
        return Number::where('dealer_number', $dealerNo)
                     ->orWhere('customer_number', $customerNo)
                     ->all();
    }

    /**
     * Is Active Twilio Number?
     * 
     * @param string $twilioNumber
     * @param string $maskedNumber
     * @return Number
     */
    public function isActiveTwilioNumber(string $twilioNumber, string $maskedNumber): Number {
        // Return Number
        return Number::where('twilio_number', $twilioNumber)
                     ->where(function(Builder $query) use($maskedNumber) {
                        $query = $query->where('customer_number', $maskedNumber)
                                       ->orWhere('dealer_number', $maskedNumber);
                     })->first();
    }


    /**
     * Delete Twilio Number
     * 
     * @param string $phone
     * @return bool
     */
    public function deleteTwilioNumber(string $phone): bool {
        // Return Numbers
        $deleted = NumberTwilio::where('phone_number', $phone)->delete();

        // Successfully Deleted?
        return !empty($deleted);
    }

    /**
     * Find All Expired Numbers (Chunked)
     * 
     * @param Closure $callable
     * @param int $toDate
     * @param int $chunkSize
     * @return void
     */
    public function getAllExpiredChunked(\Closure $callable, int $toDate, int $chunkSize = 500): void {
        NumberTwilio::select(NumberTwilio::getTableName() . '.phone_number')
                ->leftJoin(Number::getTableName(), NumberTwilio::getTableName() . '.phone_number', '=', Number::getTableName() . '.twilio_number')
                ->whereNull(Number::getTableName() . '.expiration_time')
                ->orWhere(Number::getTableName() . '.expiration_time', '<', $toDate)
                ->groupBy(NumberTwilio::getTableName() . '.phone_number')
                ->chunk($chunkSize, $callable);
    }


    /**
     * Get Verify Number
     * 
     * @param string $dealerNumber
     * @param string $type
     * @return null|NumberVerify
     */
    public function getVerifyNumber(string $dealerNumber, string $type): ?NumberVerify {
        // Return NumberVerify
        return NumberVerify::where('twilio_number', $twilioNumber)
                           ->where('verify_type', $type)->first();
    }

    /**
     * Is Verify Number?
     * 
     * @param string $twilioNumber
     * @param string $dealerNumber
     * @return NumberVerify
     */
    public function isVerifyNumber(string $twilioNumber, string $dealerNumber): NumberVerify {
        // Return NumberVerify
        return NumberVerify::where('twilio_number', $twilioNumber)
                           ->where('dealer_number', $dealerNumber)->first();
    }

    /**
     * Create Verify Number
     * 
     * @param string $dealerNumber
     * @param string $twilioNumber
     * @param string $type
     * @return NumberVerify
     */
    public function createVerifyNumber(string $dealerNumber, string $twilioNumber, string $type): NumberVerify {
        // Return New NumberVerify
        return NumberVerify::create([
            'dealer_number' => $dealerNumber,
            'twilio_number' => $twilioNumber,
            'verify_type' => $type
        ]);
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
    public function createVerifyCode(string $twilioNumber, string $response,
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
     * @return NumberVerify
     */
    public function updateVerifyCode(string $twilioNumber, string $response,
                                     string $code, bool $success = false): NumberVerify {
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
