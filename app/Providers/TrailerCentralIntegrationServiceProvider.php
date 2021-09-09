<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\InventoryLogRepository;
use App\Repositories\InventoryLogRepositoryInterface;
use App\Repositories\TrailerCentral\Integration\InventoryRepository;
use App\Repositories\TrailerCentral\Integration\InventoryRepositoryInterface;
use App\Repositories\TrailerCentral\Integration\SyncProcessRepository;
use App\Repositories\TrailerCentral\Integration\SyncProcessRepositoryInterface;
use App\Services\TrailerCentral\Integration\Console\InventorySyncService;
use App\Services\TrailerCentral\Integration\Console\InventorySyncServiceInterface;
use App\Services\TrailerCentral\Integration\InventoryLogService;
use App\Services\TrailerCentral\Integration\InventoryLogServiceInterface;
use App\Services\TrailerCentral\Integration\SyncProcessService;
use App\Services\TrailerCentral\Integration\SyncProcessServiceInterface;
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
        $this->app->bind(SyncProcessServiceInterface::class, SyncProcessService::class);

        $this->app->bind(InventoryLogRepositoryInterface::class, InventoryLogRepository::class);
        $this->app->bind(InventoryLogServiceInterface::class, InventoryLogService::class);
    }
}
