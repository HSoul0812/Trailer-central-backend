<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

final class RefundTextrailStatuses
{
    public const DENIED = 'Denied';
    public const AUTHORIZED = 'Authorized';
    public const RECEIVED = 'Received';

    public const MAP = [
        self::DENIED => Refund::STATUS_REJECTED,
        self::AUTHORIZED => Refund::STATUS_AUTHORIZED,
        self::RECEIVED => Refund::STATUS_RETURN_RECEIVED
    ];
}
