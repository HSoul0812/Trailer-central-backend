<?php

namespace App\Http\Requests\CRM\Documents;

use App\Http\Requests\Request;

/**
 * Class CreateDealerDocumentsRequest
 * @package App\Http\Requests\CRM\Documents
 */
class CreateDealerDocumentsRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'lead_id' => 'required|integer|valid_lead',
        'files' => 'array|min:1',
        'files.*' => 'file|max:10000|mimes:pdf'
    ];
}
