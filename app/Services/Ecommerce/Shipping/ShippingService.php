<?php
namespace App\Services\Ecommerce\Shipping;


use App\Services\Ecommerce\DataProvider\DataProviderManagerInterface;

class ShippingService implements ShippingServiceInterface
{
    /** @var DataProviderManagerInterface */
    private $dataProviderManager;

    /**
     * ShippingService constructor.
     * @param DataProviderManagerInterface $dataProviderManager
     */
    public function __construct(DataProviderManagerInterface $dataProviderManager)
    {
        $this->dataProviderManager = $dataProviderManager;
    }

    public function calculateShippingCosts(array $params): array
    {
        return $this->dataProviderManager->getProvider()->estimateShippingCost($params);
    }
}