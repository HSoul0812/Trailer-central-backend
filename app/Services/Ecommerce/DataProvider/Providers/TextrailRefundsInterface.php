<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\Refund\RefundBag;
use GuzzleHttp\Exception\ClientException;

interface TextrailRefundsInterface
{
    /**
     * @return int refund id
     *
     * @throws ClientException when some remote error appears
     */
    public function issueRefund(RefundBag $refundBag): int;
}
