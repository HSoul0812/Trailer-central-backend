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
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when some thing goes wrong on TexTrail side
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when the order has already synced to TexTrail
     */
    public function syncSingleOrderOnTextrail(int $orderId): int;

    /**
     * @param int $orderId
     * @return bool
     *
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when the order has not synced yet to TexTrail
     */
    public function updateRequiredInfoByTextrail(int $orderId): bool;

    /**
     * @param int $textrailOrderId
     * @return CompletedOrder
     */
    public function updateStatus(int $textrailOrderId): CompletedOrder;
}
