<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Integrations\TrailerCentral\InventoryRepository;
use App\Repositories\Integrations\TrailerCentral\InventoryRepositoryInterface;
use App\Repositories\Integrations\TrailerCentral\LeadRepository;
use App\Repositories\Integrations\TrailerCentral\LeadRepositoryInterface;
use App\Repositories\Inventory\InventoryLogRepository;
use App\Repositories\Inventory\InventoryLogRepositoryInterface;
use App\Repositories\Parts\TypeRepository;
use App\Repositories\Parts\TypeRepositoryInterface;
use App\Repositories\SyncProcessRepository;
use App\Repositories\SyncProcessRepositoryInterface;
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
use App\Services\MapSearchService\HereMapSearchService;
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
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);

        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);

        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(LeadSyncServiceInterface::class, LeadSyncService::class);
        $this->app->bind(LeadLogServiceInterface::class, LeadLogService::class);

        $this->app->bind(SyncProcessRepositoryInterface::class, SyncProcessRepository::class);

        HereMapSearchService::register();
    }
}
