<?php

namespace App\Domains\QuickBooks\Actions;

use App\Domains\CRM\Services\CRMHttpClient;
use App\Models\CRM\Dms\Quickbooks\Bill;

class DeleteBillInQuickBooksAction
{
    const SESSION_DECODE_ENDPOINT = '/quickbooks/sessions/decode';

    /** @var CRMHttpClient */
    private $crmClient;

    /** @var RefreshAccessTokenIfNecessaryAction */
    private $refreshAccessTokenIfNecessary;

    public function __construct(CRMHttpClient $crmClient, RefreshAccessTokenIfNecessaryAction $refreshAccessTokenIfNecessary)
    {
        $this->crmClient = $crmClient;
        $this->refreshAccessTokenIfNecessary = $refreshAccessTokenIfNecessary;
    }

    public function execute(Bill $bill)
    {
        $this->refreshAccessTokenIfNecessary->execute($bill->dealer);
        // $sessionToken = $bill->dealer->quickbooks_session_token;
        // 1. Refresh the token if we need to
        // $response = $this->crmClient->post('')

        // 2. Send an API request to delete bill on QBO
    }
}
