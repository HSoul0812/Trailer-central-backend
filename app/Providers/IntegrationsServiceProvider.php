<?php

namespace App\Providers;

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorFields;
use App\Repositories\Integration\CollectorFieldsRepository;
use App\Repositories\Integration\CollectorFieldsRepositoryInterface;
use App\Repositories\Integration\CollectorRepository;
use App\Repositories\Integration\CollectorRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class IntegrationsServiceProvider
 * @package App\Providers
 */
class IntegrationsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CollectorRepositoryInterface::class, function() {
            return new CollectorRepository(Collector::query());
        });

        $this->app->bind(CollectorFieldsRepositoryInterface::class, function () {
            return new CollectorFieldsRepository(CollectorFields::query());
        });
    }
}
