<?php


namespace App\Http\Requests\Website\User;


use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateRequest extends Request
{
    protected $rules = [];
    protected function getRules(): array
    {
        return [
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'email' => ['required', 'email:rfc,dns',
                Rule::unique('website_user')->where('website_id', $this->website_id)
            ],
            'phone' => 'nullable',
            'password' => 'required|valid_password',
            'repassword' => 'required|same:password',
            'website_id' => 'required',
        ];
    }

    public function message() {
        return [
            'validation.valid_password' => 'Password should be at least 1 Capital letter, 1 Number and min 8 chars.'
        ];
    }
}
