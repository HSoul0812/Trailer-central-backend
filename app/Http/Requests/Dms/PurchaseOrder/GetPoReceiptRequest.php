<?php

namespace App\Http\Requests\Dms\PurchaseOrder;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetPoReceiptRequest extends Request
{
    protected $rules = [
        'per_page' => 'integer',
        'search_term' => 'string|nullable',
        'dealer_id' => 'integer',
        'vendor_id' => 'integer',
        'is_billed' => 'boolean',
        'ids' => 'array',
        'ids.*' => 'integer|required'
    ];
}
