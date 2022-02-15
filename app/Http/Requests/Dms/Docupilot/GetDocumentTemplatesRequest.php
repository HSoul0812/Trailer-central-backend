<?php

namespace App\Http\Requests\Dms\Docupilot;

use App\Http\Requests\Request;

/**
 * Class GetDocumentTemplatesRequest
 * @package App\Http\Requests\Dms\Docupilo
 */
class GetDocumentTemplatesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:App\Models\User\User,dealer_id',
        'type' => 'string|in:quote,service',
        'type_service' => 'string|in:yes,no',
    ];
}
