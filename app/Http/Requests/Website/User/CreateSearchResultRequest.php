<?php
namespace App\Http\Requests\Website\User;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateSearchResultRequest extends Request
{
    protected $rules = [
        'dealer_id'  => 'integer|min:1|required|exists:dealer,dealer_id',
        'search_url' => 'string|required',
        'summary' => 'string|required'
    ];
}
