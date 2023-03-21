<?php

use Dingo\Api\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** @var Router $api */
/** @var Router $route */

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($route) {

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Completed Orders
     */
    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('ecommerce/orders', 'App\Http\Controllers\v1\Ecommerce\CompletedOrderController@index');
        $route->get('ecommerce/orders/{id}', 'App\Http\Controllers\v1\Ecommerce\CompletedOrderController@show')->where('id', '[0-9]+');
        $route->post('ecommerce/shipping-costs', 'App\Http\Controllers\v1\Ecommerce\ShippingController@calculateCosts');
        $route->post('ecommerce/available-shipping-methods', 'App\Http\Controllers\v1\Ecommerce\ShippingController@getAvailableShippingMethods');
        $route->post('ecommerce/refunds/{order_id}','App\Http\Controllers\v1\Ecommerce\RefundController@issue')->where('order_id', '[0-9]+');
        $route->get('ecommerce/refunds','App\Http\Controllers\v1\Ecommerce\RefundController@index');
        $route->get('ecommerce/refunds/{refund_id}','App\Http\Controllers\v1\Ecommerce\RefundController@show')->where('order_id', '[0-9]+');
        $route->get('ecommerce/invoice/{id}', 'App\Http\Controllers\v1\Ecommerce\InvoiceController@show')->where('id', '[0-9]+');
    });

    $route->group(['middleware' => 'stripe.webhook.validate'], function ($route) {
        $route->post('ecommerce/orders', 'App\Http\Controllers\v1\Ecommerce\CompletedOrderController@create');
    });

    // Utils
    $route->group([
        'prefix' => 'utils',
    ], function ($route) {
        $route->get('/ip', 'App\Http\Controllers\v1\Marketing\Utils\NetworkController@getIp');
    });

    // Tunnel Operations
    $route->group([
        'prefix' => 'tunnels',
    ], function ($route) {
        $route->post('/check', 'App\Http\Controllers\v1\Marketing\Tunnels\TunnelsController@check');
    });

    $route->group(['middleware' => 'textrail.webhook.validate'], function ($route) {
        $route->post('ecommerce/orders/{textrail_order_id}/approve', 'App\Http\Controllers\v1\Ecommerce\CompletedOrderController@markAsApproved')->where('textrail_order_id', '[0-9]+');
        $route->post('ecommerce/cancellation/{textrail_order_id}', 'App\Http\Controllers\v1\Ecommerce\RefundController@cancelOrder')->where('textrail_order_id', '[0-9]+');
        $route->post('ecommerce/returns/{rma}', 'App\Http\Controllers\v1\Ecommerce\RefundController@updateReturnStatus')->where('rma', '[0-9]+');
        $route->post('ecommerce/orders/{textrail_order_id}/returns', 'App\Http\Controllers\v1\Ecommerce\RefundController@create')->where('textrail_order_id', '[0-9]+');
    });

    /**
     * Floorplan Payments
     */

    $route->group([
        'prefix' => 'inventory'
    ], function ($route) {
        $route->group([
            'prefix' => 'floorplan',
        ], function ($route) {
            $route->get(
                'payments',
                'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@index'
            );
            $route->put(
                'payments',
                'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@create'
            );
            $route->put(
                'bulk/payments',
                'App\Http\Controllers\v1\Inventory\Floorplan\Bulk\PaymentController@create'
            );
            $route->get(
                'payments/check-number-exists',
                'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@checkNumberExists'
            );

            $route->group([
                'prefix' => 'vendors',
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Inventory\Floorplan\VendorController@index');
                $route->put('/', 'App\Http\Controllers\v1\Inventory\Floorplan\VendorController@create');
                $route->get('{id}', 'App\Http\Controllers\v1\Inventory\Floorplan\VendorController@show')
                    ->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\Inventory\Floorplan\VendorController@update')
                    ->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\Inventory\Floorplan\VendorController@destroy')
                    ->where('id', '[0-9]+');
            });

            $route->get(
                'download/csv',
                'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@downloadCsv'
            );
        });
    });

    /**
     * Part bins
     */
    $route->get('parts/bins', 'App\Http\Controllers\v1\Parts\BinController@index');
    $route->put('parts/bins', 'App\Http\Controllers\v1\Parts\BinController@create');
    $route->post('parts/bins/{id}', 'App\Http\Controllers\v1\Parts\BinController@update')->where('id', '[0-9]+');
    $route->delete('parts/bins/{id}', 'App\Http\Controllers\v1\Parts\BinController@destroy')->where('id', '[0-9]+');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@show')->where('id', '[0-9]+');
    $route->post('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@update')->where('id', '[0-9]+');
    $route->delete('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@destroy')->where('id', '[0-9]+');
    $route->post('reports/financials-stock-export/pdf', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@financialsExportPdf');
    $route->post('reports/financials-stock-export/csv', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@financialsExportCsv');
    $route->post('reports/financials-stock', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@financials');
    $route->get('reports/read', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@read');

    /**
     * Part Categories
     */
    $route->get('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@index');
    $route->put('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@create');
    $route->get('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@show')->where('id', '[0-9]+');
    $route->post('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@update')->where('id', '[0-9]+');
    $route->delete('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@destroy')->where('id', '[0-9]+');

    /**
     * Part Cycle Counts
     */
    $route->get('parts/cycle-counts', 'App\Http\Controllers\v1\Parts\CycleCountController@index');
    $route->put('parts/cycle-counts', 'App\Http\Controllers\v1\Parts\CycleCountController@create');
    $route->post('parts/cycle-counts/{id}', 'App\Http\Controllers\v1\Parts\CycleCountController@update')->where('id', '[0-9]+');
    $route->delete('parts/cycle-counts/{id}', 'App\Http\Controllers\v1\Parts\CycleCountController@destroy')->where('id', '[0-9]+');

    /**
     * Part Manufacturers
     */
    $route->get('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@index');
    $route->put('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@create');
    $route->get('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@show')->where('id', '[0-9]+');
    $route->post('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@update')->where('id', '[0-9]+');
    $route->delete('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@destroy')->where('id', '[0-9]+');


    /**
     * Monitored jobs
     */
    $route->get('jobs', 'App\Http\Controllers\v1\Jobs\MonitoredJobsController@index');
    $route->get('jobs/status/{token}', 'App\Http\Controllers\v1\Jobs\MonitoredJobsController@statusByToken');
    $route->get('jobs/status', 'App\Http\Controllers\v1\Jobs\MonitoredJobsController@status');

    /**
     * Part Bulk download
     */
    $route->post('parts/bulk/download', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@create');
    $route->get('parts/bulk/file/{token}', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@readByToken');
    $route->get('parts/bulk/file', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@read');
    $route->get('parts/bulk/status/{token}', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@statusByToken');

    /**
     * Part Bulk
     */
    $route->get('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@index');
    $route->post('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@create');
    $route->get('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@show');
    $route->put('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@update');
    $route->delete('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@destroy');

    /**
     * Part Types
     */
    $route->get('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@index');
    $route->put('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@create');
    $route->get('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@show')->where('id', '[0-9]+');
    $route->post('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@update')->where('id', '[0-9]+');
    $route->delete('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@destroy')->where('id', '[0-9]+');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@show')->where('id', '[0-9]+');
    $route->post('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@update')->where('id', '[0-9]+');
    $route->delete('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@destroy')->where('id', '[0-9]+');

    /**
     * Part orders
     */
    $route->get('parts/orders', 'App\Http\Controllers\v1\Parts\PartOrdersController@index');
    $route->put('parts/orders', 'App\Http\Controllers\v1\Parts\PartOrdersController@create');
    $route->get('parts/orders/{id}', 'App\Http\Controllers\v1\Parts\PartOrdersController@show')->where('id', '[0-9]+');
    $route->post('parts/orders/{id}', 'App\Http\Controllers\v1\Parts\PartOrdersController@update')->where('id', '[0-9]+');
    $route->delete('parts/orders/{id}', 'App\Http\Controllers\v1\Parts\PartOrdersController@destroy')->where('id', '[0-9]+');

    /**
     * Parts
     */
    $route->get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
    $route->put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
    $route->get('parts/search', 'App\Http\Controllers\v1\Parts\PartsController@search');
    $route->get('parts/{part}', 'App\Http\Controllers\v1\Parts\PartsController@display')->where('id', '[0-9]+');
    $route->post('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@update')->where('id', '[0-9]+');
    $route->delete('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@destroy')->where('id', '[0-9]+');

    /**
     * Textrail Parts
     */
    $route->get('textrail/parts', 'App\Http\Controllers\v1\Parts\Textrail\PartsController@index');
    $route->get('textrail/parts/{id}', 'App\Http\Controllers\v1\Parts\Textrail\PartsController@show')->where('id', '[0-9]+');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Inventory Bulk
     */
    $route->get('inventory/bulk', 'App\Http\Controllers\v1\Bulk\Inventory\BulkUploadController@index');
    $route->post('inventory/bulk', 'App\Http\Controllers\v1\Bulk\Inventory\BulkUploadController@create');
    $route->get('inventory/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Inventory\BulkUploadController@show');
    $route->put('inventory/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Inventory\BulkUploadController@update');
    $route->delete('inventory/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Inventory\BulkUploadController@destroy');

    /**
     * Inventory Bulk download
     */
    $route->post('inventory/bulk/create', 'App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController@create');
    $route->get('inventory/bulk/output/{token}', 'App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController@readByToken');
    $route->get('inventory/bulk/output', 'App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController@read');
    $route->get('inventory/bulks', 'App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController@index');
    $route->get('inventory/bulk/status/{token}', 'App\Http\Controllers\v1\Bulk\Inventory\BulkDownloadController@statusByToken');

    /**
     * Inventory Overlay
     */
    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('inventory/overlay', 'App\Http\Controllers\v1\Inventory\CustomOverlayController@index');
        $route->post('inventory/overlay', 'App\Http\Controllers\v1\Inventory\CustomOverlayController@update');
        $route->post('inventory/bulk-overlay', 'App\Http\Controllers\v1\Inventory\CustomOverlayController@bulkUpdate');
    });

    /**
     * Inventory Entity
     */
    $route->get('inventory/entity', 'App\Http\Controllers\v1\Inventory\EntityController@index');

    /**
     * Inventory Manufacturers
     */
    $route->get('inventory/manufacturers', 'App\Http\Controllers\v1\Inventory\ManufacturerController@index');

    /**
     * Inventory Brands
     */
    $route->get('inventory/brands', 'App\Http\Controllers\v1\Inventory\Manufacturers\BrandController@index');

    /**
     * Inventory Categories
     */
    $route->get('inventory/categories', 'App\Http\Controllers\v1\Inventory\CategoryController@index');

    /**
     * Inventory Statuses
     */
    $route->get('inventory/statuses', 'App\Http\Controllers\v1\Inventory\StatusController@index');

    /**
     * Inventory Attributes
     */
    $route->get('inventory/attributes', 'App\Http\Controllers\v1\Inventory\AttributeController@index');
    $route->put(
        'inventory/{id}/attributes',
        'App\Http\Controllers\v1\Inventory\InventoryAttributeController@update'
    )->where('id', '[0-9]+');

    /**
     * Inventory Attributes
     */
    $route->get('inventory/features', 'App\Http\Controllers\v1\Inventory\FeatureController@index');

    /**
     * Inventory transactions history
     */
    $route->get('inventory/{inventory_id}/history', 'App\Http\Controllers\v1\Inventory\InventoryController@history')->where('inventory_id', '[0-9]+');

    /**
     * Inventory distance
     */
    $route->get('inventory/{inventory_id}/delivery_price', 'App\Http\Controllers\v1\Inventory\InventoryController@deliveryPrice')->where('inventory_id', '[0-9]+');

    /**
     * Inventory
     */
    $route->get('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@index');
    $route->get('inventory/get_all_titles', 'App\Http\Controllers\v1\Inventory\InventoryController@getAllTitles');
    $route->put('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@create');
    $route->get('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+');
    $route->get('inventory/stocks/{stock}', 'App\Http\Controllers\v1\Inventory\InventoryController@findByStock');
    $route->post('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@update')->where('id', '[0-9]+');
    $route->post('inventory/mass', 'App\Http\Controllers\v1\Inventory\InventoryController@massUpdate');
    $route->delete('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@destroy')->where('id', '[0-9]+');
    $route->get('inventory/exists', 'App\Http\Controllers\v1\Inventory\InventoryController@exists');
    $route->post('inventory/{id}/export', 'App\Http\Controllers\v1\Inventory\InventoryController@export')->where('id', '[0-9]+');
    $route->post('inventory/search', 'App\Http\Controllers\v1\Inventory\InventoryController@search');
    /**
     * Inventory images
     */
    $route->put('inventory/{id}/images', 'App\Http\Controllers\v1\Inventory\ImageController@create')->where('id', '[0-9]+');
    $route->delete('inventory/{id}/images', 'App\Http\Controllers\v1\Inventory\ImageController@bulkDestroy')->where('id', '[0-9]+');
    /**
     * Inventory files
     */
    $route->put('inventory/{id}/files', 'App\Http\Controllers\v1\Inventory\FileController@create')->where('id', '[0-9]+');
    $route->delete('inventory/{id}/files', 'App\Http\Controllers\v1\Inventory\FileController@bulkDestroy')->where('id', '[0-9]+');

    /*
    |--------------------------------------------------------------------------
    | Packages
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('inventory/packages', 'App\Http\Controllers\v1\Inventory\PackageController@index');
    $route->get('inventory/packages/{id}', 'App\Http\Controllers\v1\Inventory\PackageController@show');
    $route->put('inventory/packages', 'App\Http\Controllers\v1\Inventory\PackageController@create');
    $route->post('inventory/packages/{id}', 'App\Http\Controllers\v1\Inventory\PackageController@update');
    $route->delete('inventory/packages/{id}', 'App\Http\Controllers\v1\Inventory\PackageController@destroy');

    /*
     * Cache
     */
    $route->post('inventory/cache/invalidate/dealer', 'App\Http\Controllers\v1\Inventory\InventoryCacheController@invalidateByDealer');

    /*
    |--------------------------------------------------------------------------
    | Website
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('website', 'App\Http\Controllers\v1\Website\WebsiteController@index');

    $route->put('website/{websiteId}/enable-proxied-domain-ssl', 'App\Http\Controllers\v1\Website\WebsiteController@enableProxiedDomainSsl');

    $route->get('website/default-config', 'App\Http\Controllers\v1\Website\Config\DefaultWebsiteConfigController@index');

    $route->get('website/{websiteId}/website-config', 'App\Http\Controllers\v1\Website\Config\WebsiteConfigController@index');
    $route->put('website/{websiteId}/website-config', 'App\Http\Controllers\v1\Website\Config\WebsiteConfigController@createOrUpdate')->where('websiteId', '[0-9]+');

    $route->get('website/{websiteId}/extra-website-config', 'App\Http\Controllers\v1\Website\Config\ExtraWebsiteConfigController@index');
    $route->put('website/{websiteId}/extra-website-config', 'App\Http\Controllers\v1\Website\Config\ExtraWebsiteConfigController@createOrUpdate');

    $route->get('website/{websiteId}/call-to-action', 'App\Http\Controllers\v1\Website\Config\CallToActionController@index');
    $route->put('website/{websiteId}/call-to-action', 'App\Http\Controllers\v1\Website\Config\CallToActionController@createOrUpdate')->where('websiteId', '[0-9]+');

    $route->get('website/{websiteId}/showroom', 'App\Http\Controllers\v1\Website\Config\ShowroomController@index');
    $route->put('website/{websiteId}/showroom', 'App\Http\Controllers\v1\Website\Config\ShowroomController@update')->where('websiteId', '[0-9]+');
    $route->post('website/{websiteId}/showroom', 'App\Http\Controllers\v1\Website\Config\ShowroomController@create')->where('websiteId', '[0-9]+');

    /**
     * Manufacturers
     */
    $route->get('manufacturers', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@index');
    $route->post('manufacturers/bulk_year', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@bulkUpdateYear');
    $route->post('manufacturers/bulk_visibility', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@bulkUpdateVisibility');

    /**
     * Log
     */
    $route->put('website/log', 'App\Http\Controllers\v1\Website\Log\LogController@create');

    /**
     * Website Part Filters
     */
    $route->get('website/parts/filters', 'App\Http\Controllers\v1\Website\Parts\FilterController@index');
    $route->put('website/parts/filters', 'App\Http\Controllers\v1\Website\Parts\FilterController@create');
    $route->get('website/parts/filters/{id}', 'App\Http\Controllers\v1\Website\Parts\FilterController@show')->where('id', '[0-9]+');
    $route->post('website/parts/filters/{id}', 'App\Http\Controllers\v1\Website\Parts\FilterController@update')->where('id', '[0-9]+');
    $route->delete('website/parts/filters/{id}', 'App\Http\Controllers\v1\Website\Parts\FilterController@destroy')->where('id', '[0-9]+');

    /**
     * Website Textrail Part Filters
     */
       $route->get('website/parts/textrail/filters', 'App\Http\Controllers\v1\Website\Parts\Textrail\FilterController@index');

    /**
     * Website Blog Posts
     */
    $route->get('website/blog/posts', 'App\Http\Controllers\v1\Website\Blog\PostController@index');
    $route->put('website/blog/posts', 'App\Http\Controllers\v1\Website\Blog\PostController@create');
    $route->get('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@show')->where('id', '[0-9]+');
    $route->post('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@update')->where('id', '[0-9]+');
    $route->delete('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@destroy')->where('id', '[0-9]+');
    $route->post('website/blog/bulk', 'App\Http\Controllers\v1\Website\Blog\BulkController@create');

    /**
     * Website Payment Calculator Settings
     */
     $route->get('website/{websiteId}/payment-calculator/settings', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@index')->where('websiteId', '[0-9]+');
     $route->put('website/{websiteId}/payment-calculator/settings', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@create')->where('websiteId', '[0-9]+');
     $route->post('website/{websiteId}/payment-calculator/settings/{id}', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@update')->where('websiteId', '[0-9]+')->where('id', '[0-9]+');
     $route->delete('website/{websiteId}/payment-calculator/settings/{id}', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@destroy')->where('websiteId', '[0-9]+')->where('id', '[0-9]+');

    /**
     * Website Towing Capacity
     */
    $route->get('website/towing-capacity/makes/year/{year}', 'App\Http\Controllers\v1\Website\TowingCapacity\MakeController@index')->where('year', '[0-9]+');
    $route->get('website/towing-capacity/vehicles/years', 'App\Http\Controllers\v1\Website\TowingCapacity\VehicleController@getYears');
    $route->get('website/towing-capacity/models/year/{year}/make/{makeId}', 'App\Http\Controllers\v1\Website\TowingCapacity\VehicleController@getModels')->where('year', '[0-9]+')->where('makeId', '[0-9]+');
    $route->get('website/towing-capacity/vehicles/year/{year}/make/{makeId}', 'App\Http\Controllers\v1\Website\TowingCapacity\VehicleController@getVehicles')->where('year', '[0-9]+')->where('makeId', '[0-9]+');

    /**
     * Website mail
     */
    $route->put('website/mail/lead/{leadId}/auto-respond', 'App\Http\Controllers\v1\Website\Mail\MailController@autoRespond');

    /**
     * Website Forms
     */
    $route->group(['middleware' => 'forms.field-map.validate'], function ($route) {
        $route->get('website/forms/field-map', 'App\Http\Controllers\v1\Website\Forms\FieldMapController@index');
        $route->put('website/forms/field-map', 'App\Http\Controllers\v1\Website\Forms\FieldMapController@create');
        $route->get('website/forms/field-map/types', 'App\Http\Controllers\v1\Website\Forms\FieldMapController@types');
    });


    /**
     * Website users
     */
    $route->group(['prefix' => 'website/{websiteId}/user'], function ($route) {
        $route->post('signup', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@create');
        $route->post('login', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@login');
    });

    /**
     * Website account profile
     */
    $route->group(['prefix' => 'website/account', 'middleware' => 'api.auth', 'providers' => ['website_auth']], function ($route) {
        $route->get('', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@get');
        $route->put('', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@update');
    });

    /**
     * Website User Favorite Inventory
     */
    $route->group(['prefix' => 'website/inventory/favorite', 'middleware' => 'api.auth', 'providers' => ['website_auth']], function ($route) {
        $route->get('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@index');
        $route->post('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@create');
        $route->delete('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@delete');
    });

    /**
     * Website User Search Result
     */
    $route->group(['prefix' => 'website/user/search-result', 'middleware' => 'api.auth', 'providers' => ['website_auth']], function ($route) {
        $route->get('', 'App\Http\Controllers\v1\Website\User\WebsiteUserSearchResultController@index');
        $route->post('', 'App\Http\Controllers\v1\Website\User\WebsiteUserSearchResultController@create');
        $route->delete('', 'App\Http\Controllers\v1\Website\User\WebsiteUserSearchResultController@delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Interactions
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Interactions
     */
    $route->group(['middleware' => 'interaction.validate'], function ($route) {
        $route->get('leads/{leadId}/interactions', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@index')->where('leadId', '[0-9]+');
        $route->put('leads/{leadId}/interactions', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@create')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@show')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@update')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('interactions/send-email', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@sendEmail');
        $route->get('leads/{leadId}/contact-date', 'App\Http\Controllers\v1\CRM\Interactions\TasksController@getContactDate');
    });

    /**
     * Texts Logs
     */
    $route->group(['middleware' => 'text.validate'], function ($route) {
        $route->get('leads/{leadId}/texts', 'App\Http\Controllers\v1\CRM\Text\TextController@index')->where('leadId', '[0-9]+');
        $route->put('leads/{leadId}/texts', 'App\Http\Controllers\v1\CRM\Text\TextController@create')->where('leadId', '[0-9]+');
        $route->put('leads/{leadId}/texts/send', 'App\Http\Controllers\v1\CRM\Text\TextController@send')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/texts/{id}', 'App\Http\Controllers\v1\CRM\Text\TextController@show')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('leads/{leadId}/texts/{id}', 'App\Http\Controllers\v1\CRM\Text\TextController@update')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('leads/{leadId}/texts/{id}', 'App\Http\Controllers\v1\CRM\Text\TextController@destroy')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
    });

    // Stop Text!
    $route->post('leads/texts/stop', 'App\Http\Controllers\v1\CRM\Text\StopController@index');
    // Reply
    $route->put('leads/texts/reply', 'App\Http\Controllers\v1\CRM\Text\TextController@reply')->middleware(['accesstoken.validate', 'replytext.validate']);;

    /**
     * Facebook Webhooks
     */
    $route->get('leads/facebook/message', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\WebhookController@verifyMessage');
    $route->post('leads/facebook/message', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\WebhookController@message');

    /**
     * Facebook Endpoints
     */
    $route->group(['middleware' => 'facebook.message.validate'], function ($route) {
        $route->get('leads/{leadId}/facebook/conversation', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\ConversationController@show')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/facebook/conversations', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\ConversationController@index')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/facebook/message', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\MessageController@index')->where('leadId', '[0-9]+');
        $route->post('leads/{leadId}/facebook/message', 'App\Http\Controllers\v1\CRM\Interactions\Facebook\MessageController@send')->where('leadId', '[0-9]+');
    });

    /**
     * Interaction Messages
     */
    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('leads/interaction-message/search', 'App\Http\Controllers\v1\CRM\Interactions\InteractionMessageController@search');
        $route->get('leads/interaction-message/search/count-of/{field}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionMessageController@searchCountOf');
        $route->post('leads/interaction-message/bulk', 'App\Http\Controllers\v1\CRM\Interactions\InteractionMessageController@bulkUpdate');
        $route->post('leads/interaction-message/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionMessageController@update');
    });


    /*
    |--------------------------------------------------------------------------
    | Vendors
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Vendors
     */
    $route->get('vendors', 'App\Http\Controllers\v1\Parts\VendorController@index');
    $route->put('vendors', 'App\Http\Controllers\v1\Parts\VendorController@create');
    $route->get('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@show')->where('id', '[0-9]+');
    $route->post('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@update')->where('id', '[0-9]+');
    $route->delete('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@destroy')->where('id', '[0-9]+');

    /*
    |--------------------------------------------------------------------------
    | Feeds
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->post('feed/atw', 'App\Http\Controllers\v1\Feed\AtwController@create');
    $route->put('feed/atw', 'App\Http\Controllers\v1\Feed\AtwController@update');

    // upload feed data
    $route->post('feed/uploader/{code}', 'App\Http\Controllers\v1\Feed\UploadController@upload')->where('code', '\w+');

    // Factory
    $route->get('feed/factory/showroom', 'App\Http\Controllers\v1\Feed\Factory\ShowroomController@index');
    $route->get('feed/factory/showroom/{id}', 'App\Http\Controllers\v1\Feed\Factory\ShowroomController@show');

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->post('user/passwordless', 'App\Http\Controllers\v1\User\SignInController@passwordless');
    $route->post('user/password-reset/start', 'App\Http\Controllers\v1\User\SignInController@initPasswordReset');
    $route->post('user/password-reset/finish', 'App\Http\Controllers\v1\User\SignInController@finishPasswordReset');
    $route->post('user/login', 'App\Http\Controllers\v1\User\SignInController@signIn');

    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('user', 'App\Http\Controllers\v1\User\SignInController@details');
        $route->post('user/check-admin-password', 'App\Http\Controllers\v1\User\SignInController@checkAdminPassword');

        $route->group(['middleware' => 'accounts.manage.permission'], function ($route) {
            $route->get('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@index');
            $route->post('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@create');
            $route->put('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@updateBulk');
        });

        $route->put('user/password/update', 'App\Http\Controllers\v1\User\SignInController@updatePassword');

        $route->get('user/auto-import/settings', 'App\Http\Controllers\v1\User\AutoImportController@index');
        $route->put('user/auto-import/settings', 'App\Http\Controllers\v1\User\AutoImportController@updateSettings');

        $route->get('user/overlay/settings', 'App\Http\Controllers\v1\User\OverlaySettingsController@index');
        $route->post('user/overlay/settings', 'App\Http\Controllers\v1\User\OverlaySettingsController@updateSettings');

        $route->get('user/crm/settings', 'App\Http\Controllers\v1\CRM\User\SettingsController@index');
        $route->post('user/crm/settings', 'App\Http\Controllers\v1\CRM\User\SettingsController@updateSettings');

        $route->put('user/newsletter', 'App\Http\Controllers\v1\User\SettingsController@updateNewsletter');
        $route->get('user/newsletter', 'App\Http\Controllers\v1\User\SettingsController@getNewsletter');

        $route->put('user/xml-export', 'App\Http\Controllers\v1\User\SettingsController@updateXmlExport');
        $route->get('user/xml-export', 'App\Http\Controllers\v1\User\SettingsController@getXmlExport');

        $route->put('user/adf/settings', 'App\Http\Controllers\v1\User\AdfSettingsController@updateBulk');
        $route->get('user/adf/settings', 'App\Http\Controllers\v1\User\AdfSettingsController@index');

        $route->get('user/time-clock/employees', 'App\Http\Controllers\v1\User\TimeClockController@employees');
        $route->get('user/time-clock/tracking', 'App\Http\Controllers\v1\User\TimeClockController@tracking');
        $route->post('user/time-clock/punch', 'App\Http\Controllers\v1\User\TimeClockController@punch');
    });

    /*
    |--------------------------------------------------------------------------
    | Leads
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->get('leads/status', 'App\Http\Controllers\v1\CRM\Leads\LeadStatusController@index');
    $route->put('leads/status', 'App\Http\Controllers\v1\CRM\Leads\LeadStatusController@create');
    $route->get('leads/status/public', 'App\Http\Controllers\v1\CRM\Leads\LeadStatusController@publicStatuses');
    $route->post('leads/status/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadStatusController@update');
    $route->get('leads/types', 'App\Http\Controllers\v1\CRM\Leads\LeadTypeController@index');
    $route->get('leads/types/public', 'App\Http\Controllers\v1\CRM\Leads\LeadTypeController@publicTypes');
    $route->get('leads/sources', 'App\Http\Controllers\v1\CRM\Leads\LeadSourceController@index');
    $route->get('leads/sort-fields', 'App\Http\Controllers\v1\CRM\Leads\LeadController@sortFields');
    $route->get('leads/sort-fields/crm', 'App\Http\Controllers\v1\CRM\Leads\LeadController@sortFieldsCrm');
    $route->get('leads/unique-full-names', 'App\Http\Controllers\v1\CRM\Leads\LeadController@uniqueFullNames');
    $route->get('leads/filters', 'App\Http\Controllers\v1\CRM\Leads\LeadController@filters');
    $route->get('crm/states', 'App\Http\Controllers\v1\CRM\StatesController@index');

    /*
    |--------------------------------------------------------------------------
    | Interactions
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('user/interactions/tasks/sort-fields', 'App\Http\Controllers\v1\CRM\Interactions\TasksController@sortFields');

    /*
    |--------------------------------------------------------------------------
    | Dealers
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('users', 'App\Http\Controllers\v1\User\UserController@index');
    $route->post('users', 'App\Http\Controllers\v1\User\UserController@create');

    $route->get('users-by-name', 'App\Http\Controllers\v1\User\UserController@listByName')->middleware('integration-permission:get_dealers_by_name,can_see');

    $route->post('user/classified', 'App\Http\Controllers\v1\User\UserController@updateDealerClassifieds');


    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->get('integrations', 'App\Http\Controllers\v1\Integration\IntegrationController@index');
    $route->get('integrations/{id}', 'App\Http\Controllers\v1\Integration\IntegrationController@show');

    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        /*
        |--------------------------------------------------------------------------
        | Leads
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        $route->get('leads', 'App\Http\Controllers\v1\CRM\Leads\LeadController@index');
        $route->post('leads/assign/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@assign');
        $route->get('leads/first', 'App\Http\Controllers\v1\CRM\Leads\LeadController@first');
        $route->get('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@show')->where('id', '[0-9]+');
        $route->post('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@update')->where('id', '[0-9]+');
        $route->put('leads', 'App\Http\Controllers\v1\CRM\Leads\LeadController@create');
        $route->post('leads/find-matches', 'App\Http\Controllers\v1\CRM\Leads\LeadController@getMatches');
        $route->post('leads/{id}/merge', 'App\Http\Controllers\v1\CRM\Leads\LeadController@mergeLeads');
        $route->get('leads/output', 'App\Http\Controllers\v1\CRM\Leads\LeadController@output');
        $route->delete('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@destroy');

        /*
        |--------------------------------------------------------------------------
        | Dealer Documents
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'leads/{leadId}/documents',
            'middleware' => 'leads.document.validate'
        
        ], function ($route) {

            $route->get('/', 'App\Http\Controllers\v1\CRM\Documents\DealerDocumentsController@index');
            $route->post('/', 'App\Http\Controllers\v1\CRM\Documents\DealerDocumentsController@create');
            $route->delete('/{documentId}', 'App\Http\Controllers\v1\CRM\Documents\DealerDocumentsController@destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Quotes
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/quotes', 'App\Http\Controllers\v1\Dms\UnitSaleController@index');
        $route->put('user/quotes/bulk-archive', 'App\Http\Controllers\v1\Dms\UnitSaleController@bulkArchive');
        $route->put('user/quotes/setting', 'App\Http\Controllers\v1\Dms\Quote\QuoteSettingController@updateDealerSetting');

        /*
        |--------------------------------------------------------------------------
        | POS Quotes
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->post('pos-quotes', 'App\Http\Controllers\v1\Pos\PosController@createPosQuote');

        /*
        |--------------------------------------------------------------------------
        | Quotes Refunds
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/refunds', 'App\Http\Controllers\v1\Dms\User\UserRefundsController@index');

        /*
        |--------------------------------------------------------------------------
        | Dealer Locations
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/dealer-location', 'App\Http\Controllers\v1\User\DealerLocationController@index');
        $route->put('user/dealer-location', 'App\Http\Controllers\v1\User\DealerLocationController@create');

        $route->get('user/dealer-location/{id}', 'App\Http\Controllers\v1\User\DealerLocationController@show')->where('id', '[0-9]+');
        $route->post('user/dealer-location/{id}', 'App\Http\Controllers\v1\User\DealerLocationController@update')->where('id', '[0-9]+');
        $route->delete('user/dealer-location/{id}', 'App\Http\Controllers\v1\User\DealerLocationController@destroy')->where('id', '[0-9]+');

        $route->get('user/dealer-location/check/{name}', 'App\Http\Controllers\v1\User\DealerLocationController@check');
        $route->get('user/dealer-location-quote-fees', 'App\Http\Controllers\v1\User\DealerLocationController@quoteFees');
        $route->get('user/dealer-location-available-tax-categories', 'App\Http\Controllers\v1\User\DealerLocationController@availableTaxCategories');
        $route->get('user/dealer-location-titles', 'App\Http\Controllers\v1\User\DealerLocationController@getDealerLocationTitles');

        $route->group(['prefix' => 'user/dealer-location/{locationId}'], function ($route) {
            $route->get('/mileage-fee', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@index');
            $route->post('/mileage-fee', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@create');
            $route->post('/mileage-fee/all', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@bulkCreate');
            $route->delete('/mileage-fee/{feeId}', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@delete');
        });

        /*
        |--------------------------------------------------------------------------
        | Dealer Website Images
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('website/{websiteId}/images', 'App\Http\Controllers\v1\Website\Image\WebsiteImagesController@index')->where('websiteId', '[0-9]+');
        $route->post('website/{websiteId}/images', 'App\Http\Controllers\v1\Website\Image\WebsiteImagesController@create')->where('websiteId', '[0-9]+');
        $route->put('website/{websiteId}/images/{imageId}', 'App\Http\Controllers\v1\Website\Image\WebsiteImagesController@update')->where(['websiteId' => '[0-9]+', 'imageId' => '[0-9]+']);
        $route->delete('website/{websiteId}/images/{imageId}', 'App\Http\Controllers\v1\Website\Image\WebsiteImagesController@delete')->where(['websiteId' => '[0-9]+', 'imageId' => '[0-9]+']);

        /*
        |--------------------------------------------------------------------------
        | Dealer Logos
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->post('user/logo', 'App\Http\Controllers\v1\User\DealerLogoController@store');

        /*
        |--------------------------------------------------------------------------
        | Dealer integrations
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/integrations', 'App\Http\Controllers\v1\User\DealerIntegrationController@index');
        $route->get('user/integrations/{id}', 'App\Http\Controllers\v1\User\DealerIntegrationController@show');
        $route->post('user/integrations/{id}', 'App\Http\Controllers\v1\User\DealerIntegrationController@update');
        $route->delete('user/integrations/{id}', 'App\Http\Controllers\v1\User\DealerIntegrationController@delete');

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        /**
         * Inventory for customers
         */
        $route->get('user/customers/inventory', 'App\Http\Controllers\v1\Dms\Customer\InventoryController@index');
        $route->get('user/customers/{customer_id}/inventory', 'App\Http\Controllers\v1\Dms\Customer\InventoryController@getAllByCustomer')->where('customer_id', '[0-9]+');
        $route->delete('user/customers/{customer_id}/inventory', 'App\Http\Controllers\v1\Dms\Customer\InventoryController@bulkDestroy')->where('customer_id', '[0-9]+');
        $route->post('user/customers/{customer_id}/inventory', 'App\Http\Controllers\v1\Dms\Customer\InventoryController@attach')->where('customer_id', '[0-9]+');


        $route->get('user/customers', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@index');
        $route->put('user/customers', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@create');
        $route->post('user/customers/{id}', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@update');
        $route->get('user/customers/balance/open', 'App\Http\Controllers\v1\Dms\Customer\OpenBalanceController@index');
        $route->get('user/customers/search', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@search');
        $route->delete('user/customers/{id}', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@destroy');
        $route->get('user/customers/{id}', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@show');

        /*
        |--------------------------------------------------------------------------
        | Inquiry
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'inquiry'
        ], function ($route) {
            $route->put('create', 'App\Http\Controllers\v1\CRM\Leads\InquiryController@create');
            $route->put('send', 'App\Http\Controllers\v1\CRM\Leads\InquiryController@send');
            $route->put('text', 'App\Http\Controllers\v1\CRM\Leads\InquiryController@text');
            // TO DO: Create Endpoint to Combine Send Text + Inquiry
            //$route->post('text', 'App\Http\Controllers\v1\CRM\Leads\InquiryController@text');
        });

        /*
        |--------------------------------------------------------------------------
        | Interactions
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/interactions/tasks', 'App\Http\Controllers\v1\CRM\Interactions\TasksController@index');

        /*
        |--------------------------------------------------------------------------
        | Leads
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'leads'
        ], function ($route) {
            /*
            |--------------------------------------------------------------------------
            | ADF Import
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            /*$route->group([
                'prefix' => 'import'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@index');
                $route->put('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@update');
                $route->delete('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@delete');
            });*/

            /*
            |--------------------------------------------------------------------------
            | Lead Products
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->get('products', 'App\Http\Controllers\v1\CRM\Leads\ProductController@index');
        });

        /*
        |--------------------------------------------------------------------------
        | Lead Trades
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'leads/{leadId}/trades',
            'middleware' => 'leads.trade.validate'
        ], function ($route) {
            $route->get('/', 'App\Http\Controllers\v1\CRM\Leads\LeadTradeController@index');
            $route->post('/', 'App\Http\Controllers\v1\CRM\Leads\LeadTradeController@create');
            $route->post('{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadTradeController@update')->where('id', '[0-9]+');
            $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadTradeController@destroy')->where('id', '[0-9]+');
            $route->get('{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadTradeController@show')->where('id', '[0-9]+');
        });

        $route->group([
            'prefix' => 'integration'
        ], function ($route) {
            /*
            |--------------------------------------------------------------------------
            | Integration Auth
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'auth',
                'middleware' => 'integration.auth.validate'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Integration\AuthController@index');
                $route->put('/', 'App\Http\Controllers\v1\Integration\AuthController@create');
                $route->post('/', 'App\Http\Controllers\v1\Integration\AuthController@valid');
                $route->put('login', 'App\Http\Controllers\v1\Integration\AuthController@login');
                $route->put('code', 'App\Http\Controllers\v1\Integration\AuthController@code');
                $route->get('{id}', 'App\Http\Controllers\v1\Integration\AuthController@show')->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\Integration\AuthController@update')->where('id', '[0-9]+');
            });

            /*
            |--------------------------------------------------------------------------
            | Facebook
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'facebook',
                'middleware' => 'facebook.catalog.validate'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Integration\FacebookController@index');
                $route->put('/', 'App\Http\Controllers\v1\Integration\FacebookController@create');
                $route->post('/', 'App\Http\Controllers\v1\Integration\FacebookController@payload');
                $route->get('{id}', 'App\Http\Controllers\v1\Integration\FacebookController@show')->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\Integration\FacebookController@update')->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\Integration\FacebookController@destroy')->where('id', '[0-9]+');
            });

            /*
            |--------------------------------------------------------------------------
            | Facebook Chat
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'fbchat',
                'middleware' => 'facebook.chat.validate'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@index');
                $route->put('/', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@create');
                $route->get('{id}', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@show')->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@update')->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@destroy')->where('id', '[0-9]+');
                $route->post('{id}/salespeople', 'App\Http\Controllers\v1\Integration\Facebook\ChatController@assignSalespeople')->where('id', '[0-9]+');
            });

            /*
            |--------------------------------------------------------------------------
            | CVR
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'cvr'
            ], function ($route) {
                $route->post('/', 'App\Http\Controllers\v1\Integration\CvrController@create');
                $route->get('{token}', 'App\Http\Controllers\v1\Integration\CvrController@statusByToken');
            });

            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'transaction',
                'middleware' => 'integration.access_token.validate'
            ], function ($route) {
                $route->post('/', 'App\Http\Controllers\v1\Integration\TransactionController@post');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'user'
        ], function ($route) {
            /*
            |--------------------------------------------------------------------------
            | Admin Settings
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'settings'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\User\SettingsController@index');
                $route->post('/', 'App\Http\Controllers\v1\User\SettingsController@update');
                $route->get('email', 'App\Http\Controllers\v1\User\SettingsController@email');
            });

            /*
            |--------------------------------------------------------------------------
            | Sales People
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'sales-people',
                'middleware' => 'sales-person.validate'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@index');
                $route->put('/', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@create');
                $route->get('{id}', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@show')->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@update')->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@destroy')->where('id', '[0-9]+');
                $route->get('config', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@config');

                // Validate SMTP/IMAP
                $route->put('validate', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@valid');

                // Sales People w/Auth
                $route->put('login', 'App\Http\Controllers\v1\CRM\User\SalesAuthController@login');
                $route->put('code', 'App\Http\Controllers\v1\CRM\User\SalesAuthController@code');
                $route->put('auth', 'App\Http\Controllers\v1\CRM\User\SalesAuthController@create');
                $route->get('{id}/auth', 'App\Http\Controllers\v1\CRM\User\SalesAuthController@show')->where('id', '[0-9]+');
                $route->post('{id}/auth', 'App\Http\Controllers\v1\CRM\User\SalesAuthController@update')->where('id', '[0-9]+');
            });

            /*
            |--------------------------------------------------------------------------
            | Email Builder
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'emailbuilder'
            ], function ($route) {
                // Email Builder Template
                $route->group([
                    'prefix' => 'template',
                    'middleware' => 'emailbuilder.template.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Email\TemplateController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\TemplateController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@destroy')->where('id', '[0-9]+');
                    $route->post('{id}/send', 'App\Http\Controllers\v1\CRM\Email\TemplateController@send')->where('id', '[0-9]+');
                    $route->post('test', 'App\Http\Controllers\v1\CRM\Email\TemplateController@test');
                });

                // Email Builder Campaign
                $route->group([
                    'prefix' => 'campaign',
                    'middleware' => 'emailbuilder.campaign.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Email\CampaignController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\CampaignController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@destroy')->where('id', '[0-9]+');
                    $route->post('{id}/send', 'App\Http\Controllers\v1\CRM\Email\CampaignController@send')->where('id', '[0-9]+');
                });

                // Email Builder Blast
                $route->group([
                    'prefix' => 'blast',
                    'middleware' => 'emailbuilder.blast.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Email\BlastController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\BlastController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@destroy')->where('id', '[0-9]+');
                });
            });

            /*
            |--------------------------------------------------------------------------
            | Texts
            |--------------------------------------------------------------------------
            |
            |
            |
            */
            $route->group([
                'prefix' => 'texts'
            ], function ($route) {
                // Texts Template
                $route->group([
                    'prefix' => 'template',
                    'middleware' => 'text.template.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Text\TemplateController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Text\TemplateController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@destroy')->where('id', '[0-9]+');
                });

                // Texts Campaign
                $route->group([
                    'prefix' => 'campaign',
                    'middleware' => 'text.campaign.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Text\CampaignController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Text\CampaignController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@destroy')->where('id', '[0-9]+');
                    $route->post('{id}/sent', 'App\Http\Controllers\v1\CRM\Text\CampaignController@sent')->where('id', '[0-9]+');
                });

                // Texts Blast
                $route->group([
                    'prefix' => 'blast',
                    'middleware' => 'text.blast.validate'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\CRM\Text\BlastController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Text\BlastController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@destroy')->where('id', '[0-9]+');
                    $route->post('{id}/sent', 'App\Http\Controllers\v1\CRM\Text\BlastController@sent')->where('id', '[0-9]+');
                });
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Marketing
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'marketing'
        ], function ($route) {
            // Craigslist
            $route->group([
                'prefix' => 'clapp'
            ], function ($route) {
                // Inventory
                $route->group([
                    'prefix' => 'inventory'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\Marketing\Craigslist\InventoryController@index');
                });

                // Scheduler
                $route->get('scheduler', 'App\Http\Controllers\v1\Marketing\Craigslist\SchedulerController@index');
                $route->get('upcoming', 'App\Http\Controllers\v1\Marketing\Craigslist\SchedulerController@upcoming');
                $route->get('billing', 'App\Http\Controllers\v1\Marketing\Craigslist\BillingController@index');

                // Posts
                $route->group([
                    'prefix' => 'posts'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\Marketing\Craigslist\ActivePostController@index');
                });

                // Profile
                $route->group([
                    'prefix' => 'profile'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\Marketing\Craigslist\ProfileController@index');
                });
            });

            // Facebook Marketplace
            $route->group([
                'prefix' => 'pagetab',
                'middleware' => 'marketing.facebook.pagetab'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@index');
                $route->post('/', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@create');
                $route->put('/', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@update'); // requires page_id instead
                $route->get('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@show')->where('id', '[0-9]+');
                $route->put('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@update')->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\PagetabController@destroy')->where('id', '[0-9]+');
            });

            // Facebook Marketplace
            $route->group([
                'prefix' => 'facebook',
                'middleware' => 'marketing.facebook.marketplace'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@index');
                $route->post('/', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@create');
                $route->get('status', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@status');
                $route->put('sms', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@sms');
                $route->get('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@show')->where('id', '[0-9]+');
                $route->put('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@update')->where('id', '[0-9]+');
                $route->delete('{id}', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@destroy')->where('id', '[0-9]+');
                $route->put('{id}/dismiss', 'App\Http\Controllers\v1\Marketing\Facebook\MarketplaceController@dismiss')->where('id', '[0-9]+');
            });

        });
    });


    /*
    |--------------------------------------------------------------------------
    | DMS routes
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->group([
        'prefix' => 'dms',
        'middleware' => 'accesstoken.validate',
    ], function ($route) {
        /*
        |--------------------------------------------------------------------------
        | Service Order
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('service-orders', 'App\Http\Controllers\v1\Dms\ServiceOrderController@index');
        $route->get('service-orders/{id}', 'App\Http\Controllers\v1\Dms\ServiceOrderController@show');
        $route->put('service-orders/{id}', 'App\Http\Controllers\v1\Dms\ServiceOrderController@update');
        $route->get('service-item-technicians/by-dealer', 'App\Http\Controllers\v1\Dms\ServiceOrder\ServiceItemTechnicianController@byDealer');
        $route->get('service-item-technicians/by-location/{locationId}', 'App\Http\Controllers\v1\Dms\ServiceOrder\ServiceItemTechnicianController@byLocation');
        $route->get('service-item-technicians', 'App\Http\Controllers\v1\Dms\ServiceOrder\ServiceItemTechnicianController@index');

        $route->get('service-order/technicians', 'App\Http\Controllers\v1\Dms\ServiceOrder\TechnicianController@index');
        $route->get('service-order/types', 'App\Http\Controllers\v1\Dms\ServiceOrder\TypesController@index');

        /*
        |--------------------------------------------------------------------------
        | POS
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('pos/search', 'App\Http\Controllers\v1\Pos\PosController@search');
        $route->get('pos/sales', 'App\Http\Controllers\v1\Pos\SalesController@index');
        $route->get('pos/sales/{id}', 'App\Http\Controllers\v1\Pos\SalesController@show');
        $route->get('pos/registers', 'App\Http\Controllers\v1\Dms\Pos\RegisterController@index');
        $route->post('pos/registers', 'App\Http\Controllers\v1\Dms\Pos\RegisterController@create');

        /*
        |--------------------------------------------------------------------------
        | Invoices
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('invoices', 'App\Http\Controllers\v1\Dms\InvoiceController@index');
        $route->get('invoices/{id}', 'App\Http\Controllers\v1\Dms\InvoiceController@show');

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('payments/{id}', 'App\Http\Controllers\v1\Dms\PaymentController@show');

        /*
        |--------------------------------------------------------------------------
        | Refunds
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('refunds', 'App\Http\Controllers\v1\Dms\RefundController@index');
        $route->get('refunds/{id}', 'App\Http\Controllers\v1\Dms\RefundController@show');

        /*
        |--------------------------------------------------------------------------
        | Purchase Orders
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        // Purchase Order Receipts
        $route->get('po-receipts', 'App\Http\Controllers\v1\Dms\PurchaseOrder\PurchaseOrderReceiptController@index');
        $route->get('po-receipts/{id}', 'App\Http\Controllers\v1\Dms\PurchaseOrder\PurchaseOrderReceiptController@show');

        /*
        |--------------------------------------------------------------------------
        | Financing companies
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('financing-companies', 'App\Http\Controllers\v1\Dms\FinancingCompanyController@index');
        $route->get('financing-companies/{id}', 'App\Http\Controllers\v1\Dms\FinancingCompanyController@show');
        $route->post('financing-companies', 'App\Http\Controllers\v1\Dms\FinancingCompanyController@create');
        $route->put('financing-companies/{id}', 'App\Http\Controllers\v1\Dms\FinancingCompanyController@update');
        $route->delete('financing-companies/{id}', 'App\Http\Controllers\v1\Dms\FinancingCompanyController@destroy');

        /*
        |--------------------------------------------------------------------------
        | Tax Calculator
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('tax-calculators', 'App\Http\Controllers\v1\Dms\TaxCalculatorController@index');

        /*
        |--------------------------------------------------------------------------
        | Quickbooks
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        /**
         * Accounts
         */
        $route->get('quickbooks/accounts', 'App\Http\Controllers\v1\Dms\Quickbooks\AccountController@index');
        $route->put('quickbooks/accounts', 'App\Http\Controllers\v1\Dms\Quickbooks\AccountController@create');

        /**
         * Quickbook Approval
         */
        $route->get('quickbooks/quickbook-approvals', 'App\Http\Controllers\v1\Dms\Quickbooks\QuickbookApprovalController@index');
        $route->delete('quickbooks/quickbook-approvals/{id}', 'App\Http\Controllers\v1\Dms\Quickbooks\QuickbookApprovalController@destroy')->where('id', '[0-9]+');
        $route->put('quickbooks/quickbook-approvals/{id}/{status}', 'App\Http\Controllers\v1\Dms\Quickbooks\QuickbookApprovalController@moveStatus');

        /*
        |--------------------------------------------------------------------------
        | Various reports
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('reports/sales-person-sales', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@salesReport');
        $route->get('reports/service-technician-sales', 'App\Http\Controllers\v1\Dms\ServiceOrder\ServiceItemTechnicianController@serviceReport');
        $route->post('reports/service-technician-sales-export', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@serviceReportExport');
        $route->post('reports/custom-sales', 'App\Http\Controllers\v1\Pos\SalesReportController@customReport');
        $route->post('reports/export-custom-sales', 'App\Http\Controllers\v1\Pos\SalesReportController@exportCustomReport');
        $route->get('reports/service-monthly-hours', 'App\Http\Controllers\v1\Dms\ServiceOrder\ReportsController@monthly');
        /*
        |--------------------------------------------------------------------------
        | Parts related
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        /**
         * Parts audit logs
         */
        $route->get('parts/audit-logs', 'App\Http\Controllers\v1\Parts\AuditLogController@index');
        $route->get('parts/audit-logs/date', 'App\Http\Controllers\v1\Parts\AuditLogDateController@index');
        $route->get('parts/audit-logs/date/csv', 'App\Http\Controllers\v1\Parts\AuditLogDateController@csv');

        /*
        |--------------------------------------------------------------------------
        | Printer
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->group([
            'prefix' => 'printer'
        ], function ($route) {
            // Get Preset Instructions
            $route->get('instruction', 'App\Http\Controllers\v1\Dms\Printer\InstructionController@index');

            // Get Forms
            $route->group([
                'prefix' => 'forms',
                'middleware' => 'printer.form.validate'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Dms\Printer\FormController@index');
                $route->get('{id}', 'App\Http\Controllers\v1\Dms\Printer\FormController@show')->where('id', '[0-9]+');
                $route->put('{id}/instruction', 'App\Http\Controllers\v1\Dms\Printer\FormController@instruction')->where('id', '[0-9]+');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Docupilot
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('docupilot/document-templates', 'App\Http\Controllers\v1\Dms\Docupilot\DocumentTemplatesController@index');
        $route->get('docupilot/document-templates/{id}', 'App\Http\Controllers\v1\Dms\Docupilot\DocumentTemplatesController@show');
        $route->post('docupilot/document-templates/{id}', 'App\Http\Controllers\v1\Dms\Docupilot\DocumentTemplatesController@update');

        /*
        |--------------------------------------------------------------------------
        | QZ Tray
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('qz-tray/digital-cert', 'App\Http\Controllers\v1\Dms\QzTray\QzTrayController@digitalCert');
        $route->post('qz-tray/signature', 'App\Http\Controllers\v1\Dms\QzTray\QzTrayController@signature');

        /*
        |--------------------------------------------------------------------------
        | Others
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        $route->get('settings', 'App\Http\Controllers\v1\Dms\SettingsController@show');
        $route->put('settings', 'App\Http\Controllers\v1\Dms\SettingsController@update');

        $route->get('unit-sale-labor/technicians', 'App\Http\Controllers\v1\Dms\UnitSaleLaborController@getTechnicians');
        $route->get('unit-sale-labor/service-report', 'App\Http\Controllers\v1\Dms\UnitSaleLaborController@getServiceReport');
    });

    /*
    |--------------------------------------------------------------------------
    | Integration
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('integration/collectors', 'App\Http\Controllers\v1\Integration\CollectorController@index');
    $route->post('integration/collectors/{id}', 'App\Http\Controllers\v1\Integration\CollectorController@update');
    $route->get('integration/collector/fields', 'App\Http\Controllers\v1\Integration\CollectorFieldsController@index');

    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->post('files/local', 'App\Http\Controllers\v1\File\FileController@uploadLocal');
    $route->post('files/local/twilio', 'App\Http\Controllers\v1\File\FileController@twilioUploadLocal');
    $route->post('images/local', 'App\Http\Controllers\v1\File\ImageController@uploadLocal');

    /*
    |--------------------------------------------------------------------------
    | Bill
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->post('bills', 'App\Http\Controllers\v1\Dms\Quickbooks\BillController@create');
    $route->put('bills/{id}', 'App\Http\Controllers\v1\Dms\Quickbooks\BillController@update')->where('id', '[0-9]+');
    $route->get('bills/{id}', 'App\Http\Controllers\v1\Dms\Quickbooks\BillController@show')->where('id', '[0-9]+');
    $route->get('bills', 'App\Http\Controllers\v1\Dms\Quickbooks\BillController@index');


    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->group([
        'prefix' => 'dispatch'
    ], function ($route) {
        // Craigslist Extension
        $route->group([
            'prefix' => 'craigslist'
        ], function ($route) {
            // Login to Craigslist Dispatch
            $route->post('/', 'App\Http\Controllers\v1\Dispatch\CraigslistController@login');

            // Craigslist
            $route->group([
                'middleware' => 'dispatch.craigslist'
            ], function ($route) {
                // Can See is Required
                $route->group([
                    'middleware' => 'integration-permission:craigslist_dispatch,can_see'
                ], function ($route) {
                    $route->get('/', 'App\Http\Controllers\v1\Dispatch\CraigslistController@index');
                    $route->get('{id}', 'App\Http\Controllers\v1\Dispatch\CraigslistController@show')->where('id', '[0-9]+');
                });

                // Can See and Change is Required
                $route->group([
                    'middleware' => 'integration-permission:craigslist_dispatch,can_see_and_change'
                ], function ($route) {
                    $route->put('{id}', 'App\Http\Controllers\v1\Dispatch\CraigslistController@create')->where('id', '[0-9]+');
                    //$route->post('{id}', 'App\Http\Controllers\v1\Dispatch\CraigslistController@update')->where('id', '[0-9]+');
                });
            });
        });

        // Facebook Marketplace Extension
        $route->group([
            'prefix' => 'facebook'
        ], function ($route) {
            // Login to Facebook Dispatch
            $route->post('/', 'App\Http\Controllers\v1\Dispatch\FacebookController@login');

            // Facebook Marketplace
            $route->group([
                'middleware' => 'dispatch.facebook'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\Dispatch\FacebookController@index');
                $route->put('verify', 'App\Http\Controllers\v1\Dispatch\FacebookController@verify');
                $route->get('{id}', 'App\Http\Controllers\v1\Dispatch\FacebookController@show')->where('id', '[0-9]+');
                $route->post('{id}', 'App\Http\Controllers\v1\Dispatch\FacebookController@create')->where('id', '[0-9]+');
                $route->put('{id}', 'App\Http\Controllers\v1\Dispatch\FacebookController@update')->where('id', '[0-9]+');
                $route->post('{id}/metrics', 'App\Http\Controllers\v1\Dispatch\FacebookController@metrics')->where('id', '[0-9]+');
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->post(
        'stripe/webhook',
        'App\Http\Controllers\v1\Webhook\SubscriptionController@handleWebhook'
    );

    $route->group([
        'prefix' => 'subscriptions'
    ], function ($route) {
        $route->get(
            'customer',
            'App\Http\Controllers\v1\Subscription\SubscriptionController@getCustomerByDealerId'
        );

        $route->get(
            'plans',
            'App\Http\Controllers\v1\Subscription\SubscriptionController@getExistingPlans'
        );

        $route->post(
            'subscribe',
            'App\Http\Controllers\v1\Subscription\SubscriptionController@subscribeToPlanByDealerId'
        );

        $route->post(
            'update-card',
            'App\Http\Controllers\v1\Subscription\SubscriptionController@updateCardByDealerId'
        );
    });


    $route->group([
        'prefix' => 'webhook'
    ], function ($route) {
        // Twilio Webhooks
        $route->group([
            'prefix' => 'twilio'
        ], function ($route) {
            $route->post('verify', 'App\Http\Controllers\v1\Webhook\TwilioController@verify');
        });
    });

});
