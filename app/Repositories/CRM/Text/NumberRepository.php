<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\DealerLocationRepositoryInterface;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Services\CRM\Text\TextServiceInterface;

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

    /**
     * Get Active Twilio Number
     * 
     * @param type $dealerNo
     * @param type $customerNo
     * @return array
     */
    public function get($dealerNo, $customerNo) {
        return Number::select('twilio_number')
            ->where('dealer_number', $dealerNo)
            ->where('customer_number', $customerNo)
            ->first();
    }

    public function getAll($dealerNo, $customerNo) {
        return Number::select('twilio_number')
            ->where('dealer_number', $dealerNo)
            ->orWhere('customer_number', $customerNo)
            ->all();
    }

    public function update($params) {
        $number = Number::findOrFail($params['id']);

        DB::transaction(function() use (&$number, $params) {
            // Fill Text Details
            $number->fill($params)->save();
        });

        return $number;
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
        $expirationTime = time() + (self::EXPIRATION_TIME * 60 * 60);

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
     * Create Twilio Number
     * 
     * @param string $phoneNumber
     * @return NumberTwilio
     */
    public function createTwilioNumber($phoneNumber) {
        return NumberTwilio::create(['phone_number' => $phoneNumber]);
    }
}
