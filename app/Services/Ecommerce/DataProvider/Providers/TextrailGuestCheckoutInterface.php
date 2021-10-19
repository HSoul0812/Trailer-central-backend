<?php
namespace App\Services\Ecommerce\DataProvider\Providers;


interface TextrailGuestCheckoutInterface
{
    public function addItemToGuestCart(array $params, string $quoteId);
    public function createGuestCart();
    public function estimateGuestShipping(array $params);
}