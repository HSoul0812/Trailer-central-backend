<?php
namespace App\Http\Requests\Dms\Bill;

use App\Http\Requests\Request;

class AddBillItemRequest extends Request
{
    protected $rules = [
        'id' => 'integer|exists:App\Models\CRM\Dms\Quickbooks\Bill,id',
        'items' => 'array',
    ];
}