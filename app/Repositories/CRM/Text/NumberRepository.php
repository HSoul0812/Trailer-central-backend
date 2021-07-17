<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\DealerLocationRepositoryInterface;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Services\CRM\Text\TextServiceInterface;
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
        $number = NumberTwilio::select(NumberTwilio::getTableName() . '.phone_number')
                ->join(Number::getTableName(), NumberTwilio::getTableName() . '.phone_number', '=', Number::getTableName() . '.twilio_number')
                ->whereNull(Number::getTableName() . '.expiration_time')
                ->orWhere(Number::getTableName() . '.expiration_time', '<', $toDate)
                ->groupBy(NumberTwilio::getTableName() . '.phone_number')
                ->chunk($chunkSize, $callable);
    }
}
