<?php

namespace App\Rules\Bins;

use App\Models\Parts\Bin;
use Auth;
use Illuminate\Contracts\Validation\Rule;

class BinBelongsToDealer implements Rule
{
    public function passes($attribute, $value)
    {
        return optional(Bin::find($value))->dealer_id === Auth::id();
    }

    public function message()
    {
        return "Bin does not belongs to dealer id " . Auth::id();
    }
}
