<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Integration\Auth\TokenRepository;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepository;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepository;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Services\CRM\User\SalesAuthService;
use App\Services\CRM\User\SalesAuthServiceInterface;
use App\Services\Integration\AuthService;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Google\GoogleService;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailService;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Facebook\CatalogService;
use App\Services\Integration\Facebook\CatalogServiceInterface;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Integration Services
        $this->app->bind(SalesAuthServiceInterface::class, SalesAuthService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(GoogleServiceInterface::class, GoogleService::class);
        $this->app->bind(GmailServiceInterface::class, GmailService::class);
        $this->app->bind(CatalogServiceInterface::class, CatalogService::class);
        $this->app->bind(BusinessServiceInterface::class, BusinessService::class);

        // Integration Repositories
        $this->app->bind(TokenRepositoryInterface::class, TokenRepository::class);
        $this->app->bind(CatalogRepositoryInterface::class, CatalogRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
    }

}
