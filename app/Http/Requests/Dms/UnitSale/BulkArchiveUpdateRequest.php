<?php

namespace App\Http\Requests\Dms\UnitSale;

use App\Http\Requests\User\DealerLocationRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class BulkArchiveUpdateRequest
 *
 * @package App\Http\Requests\Dms\UnitSale
 */
class BulkArchiveUpdateRequest extends FormRequest
{
    use DealerLocationRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'dealer_id' => [
                'integer',
                'min:1',
                'required',
                Rule::exists('dealer', 'dealer_id'),
            ],
            'quote_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'quote_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('dms_unit_sale', 'id')
                    ->where('dealer_id', $this->getDealerId()),
            ],
        ];
    }
}
