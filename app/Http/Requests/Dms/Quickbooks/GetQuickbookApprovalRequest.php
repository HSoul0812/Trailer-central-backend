<?php

namespace App\Http\Requests\Dms\Quickbooks;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;

/**
 * @author Marcel
 */
class GetQuickbookApprovalRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
        'search_term' => 'string',
        'status' => 'in:'. QuickbookApproval::TO_SEND .','. QuickbookApproval::SENT .',' . QuickbookApproval::FAILED . ',' . QuickbookApproval::REMOVED,
        'sort' => 'in:created_at,-created_at,action_type,-action_type,tb_name,-tb_name'
    ];

}
