<?php

namespace App\Services\Ecommerce\Shipping;

interface ShippingServiceInterface
{
    /**
     * @param array $params
     * @return array{cost: float, tax: float, cart_id: string, customer_id: int}
     */
    public function calculateShippingCosts(array $params): array;
}
