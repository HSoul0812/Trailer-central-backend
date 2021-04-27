<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

class DeleteDealerLocationRequest extends CommonDealerLocationRequest
{
    /** @var integer */
    public $move_references_to_location_id;

    protected function getRules(): array
    {
        return array_merge([
            'move_references_to_location_id' => 'nullable|exists:dealer_location,dealer_location_id,dealer_id,' . $this->input('dealer_id')
        ], parent::getRules());
    }
}
