<?php

declare(strict_types=1);

namespace Tests\Unit\Services\User\DealerLocationService;

use App\Contracts\LoggerServiceInterface;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepository;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\InventoryRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationQuoteFeeRepository;
use App\Repositories\User\DealerLocationRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxItemRepository;
use App\Repositories\User\DealerLocationSalesTaxItemRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxRepository;
use App\Repositories\User\DealerLocationSalesTaxRepositoryInterface;
use App\Repositories\User\GeoLocationRepositoryInterface;
use App\Services\Common\LoggerService;
use Mockery;

class DealerLocationServiceDependencies
{
    /** @var DealerLocationRepositoryInterface */
    public $locationRepo;

    /** @var InventoryRepositoryInterface */
    public $inventoryRepo;

    /** @var ApiEntityReferenceRepositoryInterface */
    public $apiEntityReferenceRepo;

    /** @var DealerLocationSalesTaxRepositoryInterface */
    public $salesTaxRepo;

    /** @var DealerLocationSalesTaxItemRepositoryInterface */
    public $salesTaxItemRepo;

    /** @var DealerLocationQuoteFeeRepository */
    public $quoteFeeRepo;

    /** @var GeoLocationRepositoryInterface */
    public $geolocationRepo;

    /** @var LoggerServiceInterface */
    public $loggerService;

    public function __construct()
    {
        $this->locationRepo = Mockery::mock(DealerLocationRepository::class);
        $this->inventoryRepo = Mockery::mock(InventoryRepository::class);
        $this->apiEntityReferenceRepo = Mockery::mock(ApiEntityReferenceRepository::class);
        $this->salesTaxRepo = Mockery::mock(DealerLocationSalesTaxRepository::class);
        $this->salesTaxItemRepo = Mockery::mock(DealerLocationSalesTaxItemRepository::class);
        $this->quoteFeeRepo = Mockery::mock(DealerLocationQuoteFeeRepository::class);
        $this->loggerService = Mockery::mock(LoggerService::class);
        $this->geolocationRepo = Mockery::mock(GeoLocationRepositoryInterface::class);
    }

    public function getOrderedArguments(): array
    {
        return [
            $this->locationRepo,
            $this->inventoryRepo,
            $this->apiEntityReferenceRepo,
            $this->salesTaxRepo,
            $this->salesTaxItemRepo,
            $this->quoteFeeRepo,
            $this->geolocationRepo,
            $this->loggerService
        ];
    }
}
