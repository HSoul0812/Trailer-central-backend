<?php

namespace App\Http\Requests\Dms\Quickbooks;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;

/**
 * @author Mert
 */
class UpdateQuickbookApprovalRequest extends Request {

    protected $rules = [
        'id' => 'integer|required|exists:quickbook_approval_deleted,id',
        'status' => 'in:'. QuickbookApproval::TO_SEND,
    ];

}
