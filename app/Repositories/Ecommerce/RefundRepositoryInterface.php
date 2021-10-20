<?php

declare(strict_types=1);

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\Refund;
use App\Models\Parts\Textrail\Part;
use App\Repositories\GenericRepository;
use App\Services\Ecommerce\Payment\RefundResultInterface;
use Brick\Money\Money;
use Illuminate\Support\Collection;

interface RefundRepositoryInterface extends GenericRepository
{
    public function create(array $params): Refund;

    /**
     * @param  Refund  $refund
     * @param  RefundResultInterface  $refundResult
     * @return bool
     */
    public function markAsFinished(Refund $refund, RefundResultInterface $refundResult): bool;

    /**
     * @param  Refund  $refund
     * @param  string  $errorMessage
     * @return bool
     */
    public function markAsFailed(Refund $refund, string $errorMessage): bool;

    /**
     * When there was some error after the refund has been done successfully on the payment gateway side,
     * it should be provided a result in order to make traceable
     *
     * @param  Refund  $refund
     * @param  RefundResultInterface|null  $refundResult
     * @return bool
     */
    public function markAsRecoverableFailure(Refund $refund, RefundResultInterface $refundResult): bool;

    public function get(int $refundId): ?Refund;

    /**
     * @param  array  $params
     * @return array<Refund>|Collection
     * @throws \InvalidArgumentException when some provided argument is not valid, or it is required
     */
    public function getAll(array $params): Collection;

    /**
     * @param  array<int>  $parts  part's ids to be refunded
     * @return array<Part>|Collection
     */
    public function getPartsToBeRefunded(array $parts): Collection;

    /**
     * @param  int  $orderId
     * @return array<Part>|Collection
     */
    public function getRefundedParts(int $orderId): Collection;

    public function getRefundedAmount(int $orderId): Money;
}
