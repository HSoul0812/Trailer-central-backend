<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Geolocation\GeolocationRepository;
use App\Repositories\Geolocation\GeolocationRepositoryInterface;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepository;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepositoryInterface;
use App\Repositories\Integrations\TrailerCentral\InventoryRepository;
use App\Repositories\Integrations\TrailerCentral\InventoryRepositoryInterface;
use App\Repositories\Integrations\TrailerCentral\LeadRepository;
use App\Repositories\Integrations\TrailerCentral\LeadRepositoryInterface;
use App\Repositories\Inventory\InventoryLogRepository;
use App\Repositories\Inventory\InventoryLogRepositoryInterface;
use App\Repositories\Page\PageRepository;
use App\Repositories\Page\PageRepositoryInterface;
use App\Repositories\Parts\ListingCategoryMappingsRepository;
use App\Repositories\Parts\ListingCategoryMappingsRepositoryInterface;
use App\Repositories\Parts\TypeRepository;
use App\Repositories\Parts\TypeRepositoryInterface;
use App\Repositories\Glossary\GlossaryRepository;
use App\Repositories\Glossary\GlossaryRepositoryInterface;
use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepository;
use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepositoryInterface;
use App\Repositories\SyncProcessRepository;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Repositories\SysConfig\SysConfigRepository;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use App\Repositories\ViewedDealer\ViewedDealerRepository;
use App\Repositories\ViewedDealer\ViewedDealerRepositoryInterface;
use App\Services\Dealers\DealerService;
use App\Services\Dealers\DealerServiceInterface;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersService;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersServiceInterface;
use App\Services\Integrations\TrailerCentral\Console\Inventory\LogService as InventoryLogService;
use App\Services\Integrations\TrailerCentral\Console\Inventory\LogServiceInterface as InventoryLogServiceInterface;
use App\Services\Integrations\TrailerCentral\Console\Inventory\SyncService as InventorySyncService;
use App\Services\Integrations\TrailerCentral\Console\Inventory\SyncServiceInterface as InventorySyncServiceInterface;
use App\Services\Integrations\TrailerCentral\Console\Leads\LogService as LeadLogService;
use App\Services\Integrations\TrailerCentral\Console\Leads\LogServiceInterface as LeadLogServiceInterface;
use App\Services\Integrations\TrailerCentral\Console\Leads\SyncService as LeadSyncService;
use App\Services\Integrations\TrailerCentral\Console\Leads\SyncServiceInterface as LeadSyncServiceInterface;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\IpInfo\IpInfoService;
use App\Services\IpInfo\IpInfoServiceInterface;
use App\Services\Leads\LeadService;
use App\Services\Leads\LeadServiceInterface;
use App\Services\SubscribeEmailSearch\SubscribeEmailSearchService;
use App\Services\SubscribeEmailSearch\SubscribeEmailSearchServiceInterface;
use App\Services\MapSearch\GoogleMapSearchService;
use App\Services\SysConfig\SysConfigService;
use App\Services\SysConfig\SysConfigServiceInterface;
use Http;
use Illuminate\Support\ServiceProvider;

class TrailerCentralIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(InventorySyncServiceInterface::class, InventorySyncService::class);
        $this->app->bind(InventoryLogRepositoryInterface::class, InventoryLogRepository::class);
        $this->app->bind(InventoryLogServiceInterface::class, InventoryLogService::class);
        $this->app->bind(LeadServiceInterface::class, LeadService::class);
        $this->app->bind(SubscribeEmailSearchServiceInterface::class, SubscribeEmailSearchService::class);

        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);

        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);
        $this->app->bind(ListingCategoryMappingsRepositoryInterface::class, ListingCategoryMappingsRepository::class);
        $this->app->bind(GlossaryRepositoryInterface::class, GlossaryRepository::class);
        $this->app->bind(SubscribeEmailSearchRepositoryInterface::class, SubscribeEmailSearchRepository::class);

        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(LeadSyncServiceInterface::class, LeadSyncService::class);
        $this->app->bind(LeadLogServiceInterface::class, LeadLogService::class);

        $this->app->bind(AuthTokenRepositoryInterface::class, AuthTokenRepository::class);

        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(SysConfigServiceInterface::class, SysConfigService::class);

        $this->app->bind(SyncProcessRepositoryInterface::class, SyncProcessRepository::class);
        $this->app->bind(GeolocationRepositoryInterface::class, GeolocationRepository::class);
        $this->app->bind(SysConfigRepositoryInterface::class, SysConfigRepository::class);

        $this->app->bind(IpInfoServiceInterface::class, IpInfoService::class);

        $this->app->bind(UsersServiceInterface::class, UsersService::class);

        $this->app->bind(ImageServiceInterface::class, ImageService::class);

        $this->app->bind(ViewedDealerRepositoryInterface::class, ViewedDealerRepository::class);

        $this->app->bind(DealerServiceInterface::class, DealerService::class);

        GoogleMapSearchService::register();
    }

    public function boot(): void
    {
        Http::macro('tcApi', function() {
            return Http::withHeaders([
                'access-token' => config('trailercentral.integration.api.access_token'),
            ])->baseUrl(config('services.trailercentral.api'));
        });
    }
}
