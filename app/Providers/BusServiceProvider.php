<?php

namespace App\Providers;

use App\Repositories\Horizon\TagRepository;
use App\Repositories\Horizon\TagRepositoryInterface;
use App\Services\Common\BatchedJobService;
use App\Services\Common\BatchedJobServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * This provider is intended to register/booting only bus-related artifacts
 */
class BusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BatchedJobServiceInterface::class, BatchedJobService::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }
}
