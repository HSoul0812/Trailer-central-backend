<?php

namespace App\Providers;

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorFields;
use App\Repositories\Integration\Auth\TokenRepository;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\CollectorFieldsRepository;
use App\Repositories\Integration\CollectorFieldsRepositoryInterface;
use App\Repositories\Integration\CollectorRepository;
use App\Repositories\Integration\CollectorRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepository;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Integration\Facebook\ChatRepository;
use App\Repositories\Integration\Facebook\ChatRepositoryInterface;
use App\Repositories\Integration\Facebook\FeedRepository;
use App\Repositories\Integration\Facebook\FeedRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepository;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;
use App\Repositories\Feed\TransactionExecuteQueueRepository;
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
use App\Services\Integration\Facebook\ChatService;
use App\Services\Integration\Facebook\ChatServiceInterface;
use App\Services\Integration\Facebook\BusinessService;
use App\Services\Integration\Facebook\BusinessServiceInterface;
use App\Services\Integration\Microsoft\AzureService;
use App\Services\Integration\Microsoft\AzureServiceInterface;
use App\Services\Integration\Microsoft\OfficeService;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use App\Repositories\Integration\CVR\CvrFileRepository;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Integration\CVR\CvrFileService;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use App\Services\Integration\Transaction\TransactionService;
use App\Services\Integration\Transaction\TransactionServiceInterface;
use FacebookAds\Http\Client;
use FacebookAds\Http\Request;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(AzureServiceInterface::class, AzureService::class);
        $this->app->bind(OfficeServiceInterface::class, OfficeService::class);
        $this->app->bind(CatalogServiceInterface::class, CatalogService::class);
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
        $this->app->bind(BusinessServiceInterface::class, BusinessService::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);

        // Integration Repositories
        $this->app->bind(TokenRepositoryInterface::class, TokenRepository::class);
        $this->app->bind(CatalogRepositoryInterface::class, CatalogRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(FeedRepositoryInterface::class, FeedRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);

        $this->app->bind(TransactionExecuteQueueRepositoryInterface::class, TransactionExecuteQueueRepository::class);

        $this->app->bind(CvrFileRepositoryInterface::class, CvrFileRepository::class);
        $this->app->bind(CvrFileServiceInterface::class, CvrFileService::class);

        // Collector Repositories
        $this->app->bind(CollectorRepositoryInterface::class, function() {
            return new CollectorRepository(Collector::query());
        });
        $this->app->bind(CollectorFieldsRepositoryInterface::class, function () {
            return new CollectorFieldsRepository(CollectorFields::query());
        });


        // Get Facebook Client
        $this->app->bind(Request::class, function() {
            return new Request(new Client());
        });
    }

}
