<?php

namespace App\Http\Requests;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Class BaseFormRequest
 *
 * @package App\Http\Requests
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * @return User
     */
    public function getAuthUser(): User
    {
        return auth()->user();
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return request();
    }
}
