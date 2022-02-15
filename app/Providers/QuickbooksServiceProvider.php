<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Quickbooks\DealerLocationService;
use App\Services\Quickbooks\DealerLocationServiceInterface;
use Illuminate\Support\ServiceProvider;

class QuickbooksServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DealerLocationServiceInterface::class, DealerLocationService::class);
    }
}
