<?php

namespace App\Http\Requests\CRM\Documents;

use App\Http\Requests\Request;

/**
 * Class DeleteDealerDocumentRequest
 * @package App\Http\Requests\CRM\Documents
 */
class DeleteDealerDocumentRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'lead_id' => 'required|integer|valid_lead',
        'document_id' => 'required|integer'
    ];
}
