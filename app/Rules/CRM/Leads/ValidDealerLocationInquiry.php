<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\DealerLocation;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Auth;

class ValidDealerLocationInquiry implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        // Must Be Authorized!
        $user = Auth::user();
        if (empty($user)) {
            return false;
        }

        // No Dealer Location?
        if(empty($value)) {
            return true;
        }

        // Get Valid Dealer Location!
        $dealerLocation = DealerLocation::withTrashed()->find($value);
        if(empty($dealerLocation)) {
            return false;
        }

        $website = Website::whereDealerId($user->dealer_id)->first();
        $dealersIds = $website->getFilterValue('dealer_id') ?? [];

        if (empty($dealersIds)) {
            // Does Dealer Location Belong to Dealer?!
            if($dealerLocation->dealer_id !== $user->dealer_id) {
                return false;
            }
        } else {
            $isLocationFound = false;
            foreach ($dealersIds as $dealerId) {
                if($dealerLocation->dealer_id == $dealerId) {
                    $isLocationFound = true;
                    break;
                }
            }
            return $isLocationFound;
        }


        // Success!
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Dealer Location must exist';
    }
}
