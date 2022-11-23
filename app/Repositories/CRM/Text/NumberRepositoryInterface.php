<?php

namespace App\Repositories\CRM\Text;

use App\Models\CRM\Text\NumberTwilio;
use App\Repositories\Repository;
use App\Models\CRM\Text\Number;
use Closure;
use Illuminate\Support\Collection;

interface NumberRepositoryInterface extends Repository
{
    /**
     * Set Phone as Used
     *
     * @param string $dealerNo
     * @param string $usedNo
     * @param string $customerNo
     * @param string $customerName
     * @param int|null $dealerId
     * @return Number
     */
    public function setPhoneAsUsed(string $dealerNo, string $usedNo, string $customerNo, string $customerName, ?int $dealerId = null): Number;

    /**
     * Check if a given twilio number exists
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function existsTwilioNumber(string $phoneNumber): bool;

    /**
     * Create Twilio Number
     *
     * @param string $phoneNumber
     * @return NumberTwilio
     */
    public function createTwilioNumber(string $phoneNumber): NumberTwilio;

    /**
     * Find Active Twilio Number
     *
     * @param string $dealerNo
     * @param string $customerNo
     * @return Number|null
     */
    public function findActiveTwilioNumber(string $dealerNo, string $customerNo): ?Number;

    /**
     * Find All Twilio Numbers
     *
     * @param string $dealerNo
     * @param string $customerNo
     * @return Collection<Number>
     */
    public function findAllTwilioNumbers(string $dealerNo, string $customerNo): Collection;

    /**
     * Is Active Twilio Number?
     *
     * @param string $twilioNumber
     * @param string $maskedNumber
     * @return Number|null
     */
    public function activeTwilioNumber(string $twilioNumber, string $maskedNumber): ?Number;

    /**
     * @param string $customerNumber
     * @param int $dealerId
     * @return Number|null
     */
    public function activeTwilioNumberByCustomerNumber(string $customerNumber, int $dealerId): ?Number;

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
    public function getAllExpiredChunked(Closure $callable, int $toDate, int $chunkSize = 500): void;

    /**
     * Check if a given number is from a dealer
     * @param string $phoneNumber
     * @param string $countryCode
     * @return bool
     */
    public function isDealerNumber(string $phoneNumber, string $countryCode): bool;

    /**
     * @param int $expirationTime
     * @param string $twilioNumber
     * @param string $dealerNumber
     * @return bool
     */
    public function updateExpirationDate(int $expirationTime, string $twilioNumber, string $dealerNumber): bool;
}
