<?php

namespace App\Rules\Locations;

use App\Models\User\DealerLocation;
use Auth;
use Illuminate\Contracts\Validation\Rule;

class LocationBelongsToDealer implements Rule
{
    public function passes($attribute, $value)
    {
        return DealerLocation::query()
            ->where('dealer_id', Auth::id())
            ->where('dealer_location_id', (int) $value)
            ->exists();
    }

    public function message()
    {
        return "Location ID :value doesn't belong to dealer id " . Auth::id();
    }
}
