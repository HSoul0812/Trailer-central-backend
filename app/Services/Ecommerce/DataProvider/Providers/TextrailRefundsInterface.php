<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\Refund\RefundBag;
use GuzzleHttp\Exception\ClientException;

interface TextrailRefundsInterface
{
    /**
     * @param RefundBag $refundBag
     * @return array
     *
     * @throws ClientException when some remote error appears
     */
    public function requestReturn(RefundBag $refundBag): array;

    /**
     * @return int int the refund/memo id
     *
     * @throws ClientException when some remote error appears
     */
    public function createRefund(int $textrailOrderId, array $items): int;
}
