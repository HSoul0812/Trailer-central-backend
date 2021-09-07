<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Dms\Integration\InventoryRepository;
use App\Repositories\Dms\Integration\InventoryRepositoryInterface;
use App\Repositories\Dms\Integration\SyncProcessRepository;
use App\Repositories\Dms\Integration\SyncProcessRepositoryInterface;
use App\Repositories\StockLogRepository;
use App\Repositories\StockLogRepositoryInterface;
use App\Services\Dms\Integration\InventorySyncService;
use App\Services\Dms\Integration\InventorySyncServiceInterface;
use App\Services\Dms\Integration\StockLogService;
use App\Services\Dms\Integration\StockLogServiceInterface;
use App\Services\Dms\Integration\SyncProcessService;
use App\Services\Dms\Integration\SyncProcessServiceInterface;
use Illuminate\Support\ServiceProvider;

class DmsIntegrationServiceProvider extends ServiceProvider
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

        $this->app->bind(StockLogRepositoryInterface::class, StockLogRepository::class);
        $this->app->bind(StockLogServiceInterface::class, StockLogService::class);
    }
}
