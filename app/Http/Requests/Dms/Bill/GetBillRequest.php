<?php
namespace App\Http\Requests\Dms\Bill;

use App\Http\Requests\Request;

class GetBillRequest extends Request
{
    protected $rules = [
        'id' => 'nullable|integer|exists:App\Models\CRM\Dms\Quickbooks\Bill,id',
    ];
}