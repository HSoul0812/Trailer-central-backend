<?php

namespace App\Services\Ecommerce\CompletedOrder;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

interface CompletedOrderServiceInterface
{
    /**
     * @param  array  $params
     * @return CompletedOrder
     */
    public function create(array $params): CompletedOrder;

    public function updateRefundStatus(int $orderId): bool;
}
