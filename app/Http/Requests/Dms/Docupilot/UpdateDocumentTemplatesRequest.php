<?php

namespace App\Http\Requests\Dms\Docupilot;

use App\Http\Requests\Request;

/**
 * Class UpdateDocumentTemplatesRequest
 * @package App\Http\Requests\Dms\Docupilot
 */
class UpdateDocumentTemplatesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:App\Models\User\User,dealer_id',
        'template_id' => 'integer|required|document_template_exists',
        'type' => 'string|in:quote,service',
        'type_quote' => 'string|in:yes,no,hidden',
        'type_deal' => 'string|in:yes,no,hidden',
        'type_service' => 'string|in:yes,no',
    ];
}
