<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDealerLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg,gif,heic,bmp,tiff', 'max:4096'],
            'benefit_statement' => ['nullable', 'string', 'max:255']
        ];
    }
}
