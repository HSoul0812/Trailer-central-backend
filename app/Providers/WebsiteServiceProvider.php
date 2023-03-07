<?php

namespace App\Providers;

use App\Repositories\Website\Config\AvailableValues\WebsiteConfigDefaultAvailableValuesRepository;
use App\Repositories\Website\Config\AvailableValues\WebsiteConfigDefaultAvailableValuesRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\Config\DefaultConfigRepositoryInterface;
use App\Repositories\Website\Config\DefaultConfigRepository;
use App\Repositories\Website\Tracking\TrackingRepository;
use App\Repositories\Website\Tracking\TrackingRepositoryInterface;
use App\Repositories\Website\Tracking\TrackingUnitRepository;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use App\Repositories\Website\WebsiteDealerUrlRepository;
use App\Repositories\Website\WebsiteDealerUrlRepositoryInterface;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepositoryInterface;
use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserRepositoryInterface;
use App\Repositories\Website\Image\WebsiteImageRepository;
use App\Repositories\Website\Image\WebsiteImageRepositoryInterface;
use App\Repositories\Website\WebsiteUserSearchResultRepository;
use App\Repositories\Website\WebsiteUserSearchResultRepositoryInterface;
use App\Services\Website\ExtraWebsiteConfigService;
use App\Services\Website\ExtraWebsiteConfigServiceInterface;
use App\Services\Website\WebsiteDealerUrlService;
use App\Services\Website\WebsiteDealerUrlServiceInterface;
use App\Services\Website\WebsiteUserService;
use App\Services\Website\WebsiteUserServiceInterface;
use Illuminate\Support\ServiceProvider;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\HtmlConverterInterface;

class WebsiteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Repositories
        $this->app->bind(TrackingRepositoryInterface::class, TrackingRepository::class);
        $this->app->bind(TrackingUnitRepositoryInterface::class, TrackingUnitRepository::class);
        $this->app->bind(WebsiteConfigRepositoryInterface::class, WebsiteConfigRepository::class);
        $this->app->bind(DefaultConfigRepositoryInterface::class, DefaultConfigRepository::class);
        $this->app->bind(WebsiteConfigDefaultAvailableValuesRepositoryInterface::class, WebsiteConfigDefaultAvailableValuesRepository::class);

        $this->app->bind(ExtraWebsiteConfigServiceInterface::class, ExtraWebsiteConfigService::class);

        $this->app->bind(WebsiteDealerUrlRepositoryInterface::class, WebsiteDealerUrlRepository::class);
        $this->app->bind(WebsiteUserRepositoryInterface::class, WebsiteUserRepository::class);
        $this->app->bind(WebsiteUserSearchResultRepositoryInterface::class, WebsiteUserSearchResultRepository::class);
        $this->app->bind(
            WebsiteUserFavoriteInventoryRepositoryInterface::class,
            WebsiteUserFavoriteInventoryRepository::class
        );
        $this->app->bind(WebsiteImageRepositoryInterface::class, WebsiteImageRepository::class);

        // Services
        $this->app->bind(WebsiteDealerUrlServiceInterface::class, WebsiteDealerUrlService::class);
        $this->app->bind(WebsiteUserServiceInterface::class, WebsiteUserService::class);

        // Blog
        $this->app->bind('App\Repositories\Website\Blog\BulkRepositoryInterface', 'App\Repositories\Website\Blog\BulkRepository');
        $this->app->bind('App\Services\Import\Blog\CsvImportServiceInterface', 'App\Services\Import\Blog\CsvImportService');

        // Helpers
        $this->app->bind(HtmlConverterInterface::class, HtmlConverter::class);
    }

}
