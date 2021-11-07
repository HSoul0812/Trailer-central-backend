<?php
namespace App\Http\Requests\Dms\Bill;

use App\Http\Requests\Request;

class CreateBillRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required_without_all:filter.dealer_id.eq|integer|exists:App\Models\User\User,dealer_id',
        'dealer_location_id' => 'nullable|required_without_all:dealer_location_identifier|integer|exists:App\Models\User\DealerLocation,dealer_location_id',
        'vendor_id' => 'integer',
        'doc_num' => 'nullable',
        'total' => 'numeric',
        'received_date' => 'nullable|date_format:Y-m-d',
        'due_date' => 'nullable|date_format:Y-m-d',
        'memo' => 'nullable',
        'packing_list_no' => 'nullable',
        'status' => 'in:due,paid',
        'qb_id' => 'nullable',
        'items' => 'array',
        'categories' => 'array',
        'payments' => 'array',
    ];
}