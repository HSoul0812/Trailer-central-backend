<?php

namespace App\Services\Ecommerce\Invoice;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

interface InvoiceServiceInterface
{
    public function getStripeInvoice(CompletedOrder $completedOrder): array;
}