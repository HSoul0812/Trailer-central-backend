<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

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

    /**
     * @throws ValidationException
     */
    protected function passedValidation()
    {
        if (!$this->exists('logo') && !$this->exists('benefit_statement')) {
            $this->validator->errors()->add('logo', 'logo must be present if benefit_statement is not');
            $this->validator->errors()->add('benefit_statement', 'benefit_statement must be present if logo is not');

            throw new ValidationException($this->validator);
        }
    }
}
