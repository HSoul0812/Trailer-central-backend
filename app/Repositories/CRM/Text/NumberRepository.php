<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\DealerLocationRepositoryInterface;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Services\CRM\Text\TwilioServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\User\DealerLocation;

class NumberRepository implements NumberRepositoryInterface {

    /**
     * @var TwilioServiceInterface
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
     * @param string $fromNumber
     * @param string $twilioNumber
     * @param string $toNumber
     * @param string $customerName
     * @return Number
     */
    public function setPhoneAsUsed($fromNumber, $twilioNumber, $toNumber, $customerName) {
        // Calculate Expiration
        $expirationTime = time() + (Number::EXPIRATION_TIME * 60 * 60);

        $dealerNumber = $fromNumber;
        $customerNumber = $toNumber;
        // if customer sent text inquiry, fromNumber & toNumber were switched
        if ($this->isDealerNumber($toNumber)) {
            $dealerNumber = $toNumber;
            $customerNumber = $fromNumber;
        }

        // Create Number in DB
        return $this->create([
            'dealer_number'   => $dealerNumber,
            'twilio_number'   => $twilioNumber,
            'customer_number' => $customerNumber,
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
     * Is Phone Number is a Dealer Number?
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function isDealerNumber($phoneNumber, $countryCode = 'US')
    {
        $phoneNumber = (string) PhoneNumber::make($phoneNumber, $countryCode);

        return DealerLocation::where('sms_phone', $phoneNumber)->exists();
    }
}
