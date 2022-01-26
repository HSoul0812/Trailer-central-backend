<?php

namespace App\Services\Ecommerce\DataProvider\Providers;

interface TextrailWithCustomerCheckoutInterface
{
    public function createCustomer(array $params): array;

    public function generateAccessToken(array $credentials);

    public function addItemToCart(array $params, int $quoteId);

    public function createQuote(): int;

    public function createOrderFromCart(string $cartId): string;

    public function getOrderInfo(int $orderId): array;

    /**
     * @param array $params
     * @throws \App\Exceptions\Ecommerce\TextrailException handles guzzle exception during estimation.
     * @return array{cost: float, tax: float, cart_id: string, customer_id: int}
     */
    public function estimateCustomerShippingCost(array $params): array;
}
