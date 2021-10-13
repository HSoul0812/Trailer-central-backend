<?php


namespace App\Http\Requests\Website;


use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateUserRequest extends Request
{
    /**
     * @var array
     */
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
            'password' => 'required|min:8',
            'website_id' => 'required',
        ];
    }
}
