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
            'email' => ['required', 'email',
                Rule::unique('website_user')->where('website_id', $this->website_id)
            ],
            'phone' => 'nullable',
            'password' => 'required|min:8',
            'repassword' => 'required|same:password',
            'website_id' => 'required',
        ];
    }
}
