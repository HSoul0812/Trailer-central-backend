<?php

namespace App\Http\Requests\CRM\Documents;

use App\Http\Requests\Request;

/**
 * Class GetDealerDocumentsRequest
 * @package App\Http\Requests\CRM\Documents
 */
class GetDealerDocumentsRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'lead_id' => 'required|integer|valid_lead',
    ];
}
