<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class UpdateBinRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer|bin_exists|bin_belongs_to_dealer',
        'location' => 'required|integer|location_belongs_to_dealer',
        'bin_name' => 'required'
    ];

    public function validate(): bool
    {
        $this->merge([
            'id' => (int) request()->route('id'),
        ]);

        return parent::validate();
    }
}
