<?php


namespace App\Http\Requests\Dms;


use Dingo\Api\Http\FormRequest;

class DeleteFinancingCompanyRequest extends FormRequest
{
    protected $rules = [
        'id' => 'required|integer' // todo add a 'owned' validator
    ];

}
