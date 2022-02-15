<?php

namespace App\Http\Requests\Dms\Docupilot;

use App\Http\Requests\Request;

/**
 * Class GetDocumentTemplateRequest
 * @package App\Http\Requests\Dms\Docupilot
 */
class GetDocumentTemplateRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:App\Models\User\User,dealer_id',
        'template_id' => 'integer|required|document_template_exists',
    ];
}
