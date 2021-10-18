<?php
namespace App\Services\Ecommerce\DataProvider;


interface DataProviderInterface
{
    public function estimateShippingCost(array $params);
}