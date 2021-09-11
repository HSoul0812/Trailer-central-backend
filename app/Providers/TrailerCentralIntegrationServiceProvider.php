<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Integrations\TrailerCentral\InventoryRepository;
use App\Repositories\Integrations\TrailerCentral\InventoryRepositoryInterface;
use App\Repositories\Inventory\InventoryLogRepository;
use App\Repositories\Inventory\InventoryLogRepositoryInterface;
use App\Repositories\SyncProcessRepository;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Inventory\Console\LogService as InventoryLogService;
use App\Services\Integrations\TrailerCentral\Inventory\Console\LogServiceInterface as InventoryLogServiceInterface;
use App\Services\Integrations\TrailerCentral\Inventory\Console\SyncService as InventorySyncService;
use App\Services\Integrations\TrailerCentral\Inventory\Console\SyncServiceInterface as InventorySyncServiceInterface;
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

        $this->app->bind(SyncProcessRepositoryInterface::class, SyncProcessRepository::class);

        $this->app->bind(InventoryLogRepositoryInterface::class, InventoryLogRepository::class);
        $this->app->bind(InventoryLogServiceInterface::class, InventoryLogService::class);
    }
}
