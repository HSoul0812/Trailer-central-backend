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

    public function updateRefundSummary(int $orderId): bool;

    /**
     * @param int $orderId TC ecommerce order id
     * @return int the TexTrail order id
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when some thing goes wrong on Magento side
     */
    public function syncSingleOrderOnTextrail(int $orderId): int;
}
