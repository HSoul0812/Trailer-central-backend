<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;
use App\Models\Parts\Bin;

class DeleteBinRequest extends Request
{
    protected $rules = [
        'bin_id' => 'bail|required|integer|bin_exists|bin_belongs_to_dealer'
    ];

    /**
     * @return mixed
     */
    protected function getObject()
    {
        return new Bin;
    }

    protected function getObjectIdValue()
    {
        return $this->bin_id;
    }

    protected function validateObjectBelongsToUser(): bool
    {
        return true;
    }
}
