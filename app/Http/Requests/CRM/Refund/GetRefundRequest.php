<?php

namespace App\Http\Requests\CRM\Refund;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\Refund;

/**
 * Class GetRefundRequest
 * @package App\Http\Requests\CRM\Refund
 */
class GetRefundRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'id' => 'required|integer',
    ];

    /**
     * {@inheritDoc}
     */
    protected function getObject(): Refund
    {
        return new Refund();
    }

    /**
     * {@inheritDoc}
     */
    protected function getObjectIdValue()
    {
        return $this->input('id');
    }

    /**
     * {@inheritDoc}
     */
    protected function validateObjectBelongsToUser() : bool
    {
        return true;
    }
}
