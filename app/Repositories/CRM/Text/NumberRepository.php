<?php

namespace App\Repositories\CRM\Text;

use Closure;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Models\User\DealerLocation;

class NumberRepository implements NumberRepositoryInterface
{
    /**
     * Create a new Number
     *
     * @param $params
     * @return Number
     */
    public function create($params): Number
    {
        return Number::create($params);
    }

    /**
     * Delete a given number by id
     *
     * @param $params
     * @return bool
     */
    public function delete($params): bool
    {
        return Number::query()->where('id', $params['id'])->delete();
    }

    /**
     * Retrieve a Number by id
     *
     * @param $params
     * @return Number
     */
    public function get($params): Number
    {
        return Number::findOrFail($params['id']);
    }

    /**
     * Get all Numbers by parameters paginated
     *
     * @param $params
     * @return LengthAwarePaginator
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = Number::query();

        if (isset($params['dealer_id'])) {
            $query = $query->where(Number::getTableName().'.dealer_id', $params['dealer_id']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update a given Number by id
     *
     * @param $params
     * @return bool
     */
    public function update($params): bool
    {
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
     * @param int|null $dealerId
     * @return Number
     */
    public function setPhoneAsUsed(string $fromNumber, string $twilioNumber, string $toNumber, string $customerName, ?int $dealerId = null): Number
    {
        // Calculate Expiration
        $expirationTime = time() + (Number::EXPIRATION_TIME * 60 * 60);

        $dealerNumber = $fromNumber;
        $customerNumber = $toNumber;

        // If customer sent text inquiry, fromNumber & toNumber were switched
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
            'expiration_time' => $expirationTime,
            'dealer_id'       => $dealerId
        ]);
    }

    /**
     * Twilio Number Exists?
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function existsTwilioNumber(string $phoneNumber): bool
    {
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
    public function createTwilioNumber(string $phoneNumber): NumberTwilio
    {
        return NumberTwilio::create(['phone_number' => $phoneNumber]);
    }

    /**
     * Find Active Twilio Number
     *
     * @param string $dealerNo
     * @param string $customerNo
     * @return Number|null
     */
    public function findActiveTwilioNumber(string $dealerNo, string $customerNo): ?Number
    {
        // Return Number
        return Number::where('dealer_number', $dealerNo)
                     ->where('customer_number', $customerNo)
                     ->orderBy('id', 'desc')
                     ->first();
    }

    /**
     * Find All Twilio Numbers
     *
     * @param string $dealerNo
     * @param string $customerNo
     * @return Collection Number
     */
    public function findAllTwilioNumbers(string $dealerNo, string $customerNo): Collection
    {
        // Return Numbers
        return Number::where('dealer_number', $dealerNo)
                     ->orWhere('customer_number', $customerNo)
                     ->orderBy('id', 'desc')
                     ->get();
    }

    /**
     * Is Active Twilio Number?
     *
     * @param string $twilioNumber
     * @param string $maskedNumber
     * @return Number|null
     */
    public function activeTwilioNumber(string $twilioNumber, string $maskedNumber): ?Number
    {
        $query = Number::query();

        $query->where('twilio_number', $twilioNumber)
            ->where(function (Builder $query) use ($maskedNumber) {
                $query->where('customer_number', $maskedNumber)
                    ->orWhere('dealer_number', $maskedNumber);
            })
            ->orderBy('id', 'desc');

        return $query->get()->first();
    }

    /**
     * @param string $customerNumber
     * @param int $dealerId
     * @return Number|null
     */
    public function activeTwilioNumberByCustomerNumber(string $customerNumber, int $dealerId): ?Number
    {
        return Number::where('customer_number', $customerNumber)
            ->where('dealer_id', $dealerId)
            ->orderBy('id', 'desc')
            ->first();
    }


    /**
     * Delete Twilio Number
     *
     * @param string $phone
     * @return bool
     */
    public function deleteTwilioNumber(string $phone): bool
    {
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
    public function getAllExpiredChunked(Closure $callable, int $toDate, int $chunkSize = 500): void
    {
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
     * @param string $countryCode
     * @return bool
     */
    public function isDealerNumber(string $phoneNumber, string $countryCode = 'US'): bool
    {
        $phoneNumber = (string) PhoneNumber::make($phoneNumber, $countryCode);

        return DealerLocation::where('sms_phone', $phoneNumber)->exists();
    }

    /**
     * @param int $expirationTime
     * @param string $twilioNumber
     * @param string $dealerNumber
     * @return bool
     */
    public function updateExpirationDate(int $expirationTime, string $twilioNumber, string $dealerNumber): bool
    {
        $query = Number::query();

        $query = $query
            ->where([
                'dealer_number' => $dealerNumber,
                'twilio_number' => $twilioNumber,
            ])
            ->orderBy('id', 'desc');

        /** @var Number $number */
        $number = $query->first();

        if (!$number instanceof Number) {
            return false;
        }

        $number->expiration_time = $expirationTime;

        return $number->save();
    }
}
