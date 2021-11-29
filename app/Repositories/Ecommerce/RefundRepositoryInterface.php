<?php

declare(strict_types=1);

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Models\Parts\Textrail\RefundedPart;
use App\Repositories\GenericRepository;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayRefundResultInterface;
use Brick\Money\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RefundRepositoryInterface extends GenericRepository
{
    public function create(array $params): Refund;

    /**
     * @param  Refund  $refund
     * @return bool
     */
    public function markAsProcessing(Refund $refund): bool;

    /**
     * @param  Refund  $refund
     * @param  PaymentGatewayRefundResultInterface  $refundResult
     * @return bool
     */
    public function markAsCompleted(Refund $refund, PaymentGatewayRefundResultInterface $refundResult): bool;

    /**
     * @param Refund $refund
     * @param string|array $message
     * @param string $stage
     * @return bool
     */
    public function markAsFailed(Refund $refund, $message, string $stage): bool;

    public function updateRma(Refund $refund, int $textrailRma): bool;

    /**
     * @param Refund $refund
     * @param array $metadata
     * @param string|array $message
     * @param string $stage
     * @return bool
     */
    public function markAsRecoverableFailure(Refund $refund, array $metadata, $message, string $stage): bool;

    public function get(int $refundId): ?Refund;

    /**
     * @param  array  $params
     * @return array<Refund>|Collection|LengthAwarePaginator
     * @throws \InvalidArgumentException when some provided argument is not valid, or it is required
     */
    public function getAll(array $params);

    /**
     * @param  array<int>  $parts  part's ids to be refunded
     * @return array<Part>|Collection
     */
    public function getPartsToBeRefunded(array $parts): Collection;

    /**
     * @param  int  $orderId
     * @return array<RefundedPart>|Collection
     */
    public function getRefundedParts(int $orderId): Collection;

    public function getRefundedAmount(int $orderId): Money;

    /**
     * @param int $orderId
     * @return array{total_amount: Money, parts_amount: Money, handling_amount: Money, shipping_amount: Money, adjustment_amount: Money, tax_amount: Money}
     */
    public function getOrderRefundSummary(int $orderId): array;
}
