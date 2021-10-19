<?php
namespace App\Services\Ecommerce\Shipping;


interface ShippingServiceInterface
{
    public function calculateShippingCosts(array $params): array;
}