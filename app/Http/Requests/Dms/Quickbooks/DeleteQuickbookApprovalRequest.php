<?php

namespace App\Http\Requests\Dms\Quickbooks;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;

/**
 * @author Mert
 */
class DeleteQuickbookApprovalRequest extends Request {

    use DeleteQuickbookRequestTrait;

    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'id' => 'integer|required|exists:quickbook_approval,id'
        ];
    }

    protected function getObject(): QuickbookApproval
    {
        return new QuickbookApproval();
    }

    protected function getObjectIdValue(): int
    {
        return $this->input('id');
    }

    protected function validateObjectBelongsToUser(): bool
    {
        return true;
    }
}
