<?php

namespace App\Providers;

use App\Repositories\CRM\Text\VerifyRepository;
use App\Repositories\CRM\Text\VerifyRepositoryInterface;
use App\Repositories\Marketing\TunnelRedisRepository;
use App\Repositories\Marketing\TunnelRepositoryInterface;
use App\Repositories\Marketing\VirtualCardRepository;
use App\Repositories\Marketing\VirtualCardRepositoryInterface;
use App\Repositories\Marketing\Facebook\MarketplaceRepository;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepository;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Repositories\Marketing\Facebook\ListingRepository;
use App\Repositories\Marketing\Facebook\ListingRepositoryInterface;
use App\Repositories\Marketing\Facebook\ImageRepository;
use App\Repositories\Marketing\Facebook\ImageRepositoryInterface;
use App\Repositories\Marketing\Facebook\ErrorRepository;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Repositories\Marketing\Facebook\PostingRedisRepository;
use App\Repositories\Marketing\Facebook\PostingRepositoryInterface;
use App\Repositories\Marketing\Craigslist\PostRepository;
use App\Repositories\Marketing\Craigslist\PostRepositoryInterface;
use App\Repositories\Marketing\Craigslist\AccountRepository;
use App\Repositories\Marketing\Craigslist\AccountRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ActivePostRepository;
use App\Repositories\Marketing\Craigslist\ActivePostRepositoryInterface;
use App\Repositories\Marketing\Craigslist\BalanceRepository;
use App\Repositories\Marketing\Craigslist\BalanceRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DealerRepository;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\CityRepository;
use App\Repositories\Marketing\Craigslist\CityRepositoryInterface;
use App\Repositories\Marketing\Craigslist\InventoryRepository;
use App\Repositories\Marketing\Craigslist\InventoryRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ProfileRepository;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Repositories\Marketing\Craigslist\QueueRepository;
use App\Repositories\Marketing\Craigslist\QueueRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SchedulerRepository;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SessionRepository;
use App\Repositories\Marketing\Craigslist\SessionRepositoryInterface;
use App\Repositories\Marketing\Craigslist\SubareaRepository;
use App\Repositories\Marketing\Craigslist\SubareaRepositoryInterface;
use App\Repositories\Marketing\Craigslist\TransactionRepository;
use App\Repositories\Marketing\Craigslist\TransactionRepositoryInterface;
use App\Repositories\Marketing\Craigslist\ClientRedisRepository;
use App\Repositories\Marketing\Craigslist\ClientRepositoryInterface;
use App\Services\Dispatch\Facebook\PostingHistoryService;
use App\Services\Dispatch\Facebook\PostingHistoryServiceInterface;
use App\Services\Marketing\Facebook\MarketplaceService;
use App\Services\Marketing\Facebook\MarketplaceServiceInterface;
use App\Services\Marketing\Craigslist\ProfileService;
use App\Services\Marketing\Craigslist\ProfileServiceInterface;
use App\Services\Marketing\Craigslist\ValidateService;
use App\Services\Marketing\Craigslist\ValidateServiceInterface;
use App\Services\Dispatch\Facebook\MarketplaceService as MarketplaceDispatchService;
use App\Services\Dispatch\Facebook\MarketplaceServiceInterface as MarketplaceDispatchServiceInterface;
use App\Services\Dispatch\Craigslist\CraigslistService as CraigslistDispatchService;
use App\Services\Dispatch\Craigslist\CraigslistServiceInterface as CraigslistDispatchServiceInterface;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Marketing Services
        $this->app->bind(MarketplaceServiceInterface::class, MarketplaceService::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(PostingHistoryServiceInterface::class, PostingHistoryService::class);
        $this->app->bind(ValidateServiceInterface::class, ValidateService::class);

        // Marketing Craigslist Repositories
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(ActivePostRepositoryInterface::class, ActivePostRepository::class);
        $this->app->bind(BalanceRepositoryInterface::class, BalanceRepository::class);
        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(DealerRepositoryInterface::class, DealerRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(QueueRepositoryInterface::class, QueueRepository::class);
        $this->app->bind(SchedulerRepositoryInterface::class, SchedulerRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SubareaRepositoryInterface::class, SubareaRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(VerifyRepositoryInterface::class, VerifyRepository::class);

        // Marketing Facebook Repositories
        $this->app->bind(MarketplaceRepositoryInterface::class, MarketplaceRepository::class);
        $this->app->bind(FilterRepositoryInterface::class, FilterRepository::class);
        $this->app->bind(ListingRepositoryInterface::class, ListingRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        $this->app->bind(ErrorRepositoryInterface::class, ErrorRepository::class);
        $this->app->bind(VirtualCardRepositoryInterface::class, VirtualCardRepository::class);

        // Dispatch (Redis) Repositories
        $this->app->bind(ClientRepositoryInterface::class, ClientRedisRepository::class);
        $this->app->bind(TunnelRepositoryInterface::class, TunnelRedisRepository::class);
        $this->app->bind(PostingRepositoryInterface::class, PostingRedisRepository::class);

        // Dispatch Services
        $this->app->bind(MarketplaceDispatchServiceInterface::class, MarketplaceDispatchService::class);
        $this->app->bind(CraigslistDispatchServiceInterface::class, CraigslistDispatchService::class);
    }

}