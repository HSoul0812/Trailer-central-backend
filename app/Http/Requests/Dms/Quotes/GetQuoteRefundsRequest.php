<?php

namespace App\Http\Requests\Dms\Quotes;

use Dingo\Api\Http\FormRequest;

class GetQuoteRefundsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'sort' => 'string',
            'with' => 'string',
            'per_page' => 'required|int|max:500',
            'page' => 'required|int',
        ];
    }
}