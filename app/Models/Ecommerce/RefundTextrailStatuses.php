<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

final class RefundTextrailStatuses
{
    public const PENDING = 'Pending';
    public const REJECTED = 'Rejected';
    public const AUTHORIZED = 'Authorized';
    public const RETURN_RECEIVED = 'Return Received';

    public const MAP = [
        self::PENDING => Refund::STATUS_PENDING,
        self::REJECTED => Refund::STATUS_REJECTED,
        self::AUTHORIZED => Refund::STATUS_AUTHORIZED,
        self::RETURN_RECEIVED => Refund::STATUS_RETURN_RECEIVED,
    ];
}
