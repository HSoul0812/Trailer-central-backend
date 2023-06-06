<?php

namespace App\Rules\Inventory;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Inventory\Inventory;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Auth;

class ValidInventoryInquiry implements Rule
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

        // Get Valid Inventory!
        $inventory = Inventory::find($value);
        if(empty($inventory)) {
            return false;
        }

        $website = Website::whereDealerId($user->dealer_id)->first();
        if(is_null($website)){
            return false;
        }

        $dealersIds = $website->getFilterValue('dealer_id') ?? [];

        if (empty($dealersIds)) {
            // Does Inventory Belong to Dealer?!
            if($inventory->dealer_id !== $user->dealer_id) {
                return false;
            }
        } else {
            $dealersIds = (array)$dealersIds;
            foreach ($dealersIds as $dealerId) {
                if($inventory->dealer_id == $dealerId) {
                    return true;
                }
            }
            return false;
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
        return 'Inventory must exist';
    }
}
