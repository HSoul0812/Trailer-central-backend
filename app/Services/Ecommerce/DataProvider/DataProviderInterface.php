<?php

namespace App\Services\Ecommerce\DataProvider;

interface DataProviderInterface
{
    /**
     * @param array $params
     * @return array{cost: float, tax: float, cart_id: string, customer_id: ?int, carrier_code: string, method_code: string}
     */
    public function estimateShippingCost(array $params);
}
