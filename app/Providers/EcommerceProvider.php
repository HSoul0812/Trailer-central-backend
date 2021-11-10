<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\LoggerServiceInterface;
use App\Events\Ecommerce\OrderSuccessfullyPaid;
use App\Events\Ecommerce\QtyUpdated;
use App\Http\Controllers\v1\Ecommerce\CompletedOrderController;
use App\Http\Controllers\v1\Parts\Textrail\PartsController;
use App\Jobs\Ecommerce\SyncOrderJob;
use App\Listeners\Ecommerce\PartQtyReducer;
use App\Listeners\Ecommerce\SendOrderToTextrail;
use App\Listeners\Ecommerce\UpdateOrderPartsQty;
use App\Models\Parts\Textrail\Part;
use App\Repositories\Ecommerce\CompletedOrderRepository;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Ecommerce\RefundRepository;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderService;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use App\Services\Ecommerce\DataProvider\DataProviderManager;
use App\Services\Ecommerce\DataProvider\DataProviderManagerInterface;
use App\Services\Ecommerce\DataProvider\Providers\TextrailMagento;
use App\Services\Ecommerce\DataProvider\Providers\TextrailPartsInterface;
use App\Services\Ecommerce\DataProvider\Providers\TextrailWithCheckoutInterface;
use App\Services\Ecommerce\Payment\Gateways\PaymentGatewayServiceInterface;
use App\Services\Ecommerce\Payment\Gateways\Stripe\StripeService;
use App\Services\Ecommerce\Payment\PaymentService;
use App\Services\Parts\Textrail\TextrailPartImporterServiceInterface;
use App\Repositories\Parts\Textrail\BrandRepositoryInterface;
use App\Repositories\Parts\Textrail\BrandRepository;
use App\Repositories\Parts\Textrail\CategoryRepositoryInterface;
use App\Repositories\Parts\Textrail\CategoryRepository;
use App\Repositories\Parts\Textrail\ManufacturerRepositoryInterface;
use App\Repositories\Parts\Textrail\ManufacturerRepository;
use App\Repositories\Parts\Textrail\TypeRepositoryInterface;
use App\Repositories\Parts\Textrail\TypeRepository;
use App\Repositories\Parts\Textrail\ImageRepositoryInterface;
use App\Repositories\Parts\Textrail\ImageRepository;
use App\Services\Ecommerce\Payment\PaymentServiceInterface;
use App\Services\Ecommerce\Shipping\ShippingService;
use App\Services\Ecommerce\Shipping\ShippingServiceInterface;
use App\Services\Parts\Textrail\TextrailPartImporterService;
use App\Services\Parts\Textrail\TextrailPartService;
use App\Services\Parts\Textrail\TextrailPartServiceInterface;
use App\Transformers\Ecommerce\CompletedOrderTransformer;
use App\Transformers\Parts\PartsTransformerInterface;
use App\Transformers\Parts\Textrail\PartsTransformer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;
use Stripe\StripeClientInterface;
use Illuminate\Support\Facades\Event;

class EcommerceProvider extends ServiceProvider
{
    /**
     * events and listeners for ecommerce
     */
    protected $listen = [
        // on order successfully paid
        OrderSuccessfullyPaid::class => [

            // send over Textrail Magento API
            SendOrderToTextrail::class,

            // update all order parts quantities
            UpdateOrderPartsQty::class,
        ],
        // on part update
        QtyUpdated::class => [
            PartQtyReducer::class
        ]
    ];

    /**
     * Bootstrap any ecommerce services.
     *
     * @return void
     */
    public function boot(): void
    {
        // register events and listeners
        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CompletedOrderServiceInterface::class, CompletedOrderService::class);
        $this->app->bind(CompletedOrderRepositoryInterface::class, CompletedOrderRepository::class);
        $this->app->when([CompletedOrderService::class, CompletedOrderController::class, CompletedOrderTransformer::class])
            ->needs(PartRepositoryInterface::class)
            ->give(function () {
                return app()->make(PartRepository::class);
            });
        $this->app->bind(RefundRepositoryInterface::class, RefundRepository::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(StripeClientInterface::class, static function (): StripeClient {
            return new StripeClient(Config::get('stripe_checkout.secret'));
        });
        $this->app->bind(PaymentGatewayServiceInterface::class, StripeService::class);

        $this->app->bind(ShippingServiceInterface::class, ShippingService::class);
        $this->app->bind(DataProviderManagerInterface::class, DataProviderManager::class);
        $this->app->bind(DataProviderInterface::class, TextrailMagento::class);
        $this->app->bind(TextrailPartsInterface::class, TextrailMagento::class);
        $this->app->bind(TextrailWithCheckoutInterface::class, TextrailMagento::class);

        $this->app->when(PartsController::class)
            ->needs(PartRepositoryInterface::class)
            ->give(function () {
                return new PartRepository(new Part);
            });

        $this->app->when(PartsController::class)
            ->needs(PartsTransformerInterface::class)
            ->give(function () {
                return new PartsTransformer;
            });

        $this->app->bind(TextrailPartServiceInterface::class, TextrailPartService::class);

        $this->app->bind(TextrailPartImporterServiceInterface::class, TextrailPartImporterService::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ManufacturerRepositoryInterface::class, ManufacturerRepository::class);
        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);

        $this->app->bindMethod(SyncOrderJob::class . '@handle', function (SyncOrderJob $job): void {
            $job->handle(
                $this->app->make(TextrailWithCheckoutInterface::class),
                $this->app->make(CompletedOrderRepositoryInterface::class),
                $this->app->make(LoggerServiceInterface::class)
            );
        });
    }
}
