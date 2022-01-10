<?php
namespace App\Services\Ecommerce\DataProvider;

interface DataProviderManagerInterface
{
    public function getProvider(): DataProviderInterface;
}