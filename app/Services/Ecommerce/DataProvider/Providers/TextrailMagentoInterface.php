<?php
namespace App\Services\Ecommerce\DataProvider\Providers;

interface TextrailMagentoInterface
{
    public function createCustomer(array $params): array;
    public function generateAccessToken(array $credentials);
    public function addItemToCart(array $params, int $quoteId);
    public function createQuote(): int;
}