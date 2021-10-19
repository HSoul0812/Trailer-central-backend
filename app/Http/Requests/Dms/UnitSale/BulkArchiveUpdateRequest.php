<?php

namespace App\Http\Requests\Dms\UnitSale;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

/**
 * Class BulkArchiveUpdateRequest
 *
 * @package App\Http\Requests\Dms\UnitSale
 */
class BulkArchiveUpdateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $authUser = $this->getAuthUser();

        return [
            'quote_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'quote_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('dms_unit_sale', 'id')
                    ->where('dealer_id', $authUser->dealer_id),
            ],
        ];
    }
}
