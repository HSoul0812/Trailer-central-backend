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
        });
    });

    /**
     * Part bins
     */
    $route->get('parts/bins', 'App\Http\Controllers\v1\Parts\BinController@index');
    $route->put('parts/bins', 'App\Http\Controllers\v1\Parts\BinController@create');
    $route->post('parts/bins/{id}', 'App\Http\Controllers\v1\Parts\BinController@create')->where('id', '[0-9]+');
    $route->delete('parts/bins/{id}', 'App\Http\Controllers\v1\Parts\BinController@destroy')->where('id', '[0-9]+');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@show')->where('id', '[0-9]+');
    $route->post('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@update')->where('id', '[0-9]+');
    $route->delete('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@destroy')->where('id', '[0-9]+');
    $route->post('reports/financials-stock-export', 'App\Http\Controllers\v1\Bulk\Parts\BulkReportsController@financialsExport');
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
    $route->get('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@show')->where('id', '[0-9]+');
    $route->post('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@update')->where('id', '[0-9]+');
    $route->delete('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@destroy')->where('id', '[0-9]+');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Inventory Overlay
     */

    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('inventory/overlay', 'App\Http\Controllers\v1\Inventory\CustomOverlayController@index');
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
    $route->get('inventory/{inventory_id}/delivery_price', 'App\Http\Controllers\v1\Inventory\InventoryController@delivery_price')->where('inventory_id', '[0-9]+');

    /**
     * Inventory
     */
    $route->get('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@index');
    $route->put('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@create');
    $route->get('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+');
    $route->post('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@update')->where('id', '[0-9]+');
    $route->delete('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@destroy')->where('id', '[0-9]+');
    $route->get('inventory/exists', 'App\Http\Controllers\v1\Inventory\InventoryController@exists');

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
    |--------------------------------------------------------------------------
    | Website
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('website', 'App\Http\Controllers\v1\Website\WebsiteController@index');

    $route->put('website/{websiteId}/enable-proxied-domain-ssl', 'App\Http\Controllers\v1\Website\WebsiteController@enableProxiedDomainSsl');

    $route->get('website/{websiteId}/call-to-action', 'App\Http\Controllers\v1\Website\Config\CallToActionController@index');
    $route->put('website/{websiteId}/call-to-action', 'App\Http\Controllers\v1\Website\Config\CallToActionController@createOrUpdate')->where('websiteId', '[0-9]+');


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
    $route->group(['middleware' => 'website.validate'], function ($route) {
        $route->get('website/{websiteId}/payment-calculator/settings', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@index')->where('websiteId', '[0-9]+');
        $route->put('website/{websiteId}/payment-calculator/settings', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@create')->where('websiteId', '[0-9]+');
        $route->get('website/{websiteId}/payment-calculator/settings/{id}', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@show')->where('websiteId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('website/{websiteId}/payment-calculator/settings/{id}', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@update')->where('websiteId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('website/{websiteId}/payment-calculator/settings/{id}', 'App\Http\Controllers\v1\Website\PaymentCalculator\SettingsController@destroy')->where('websiteId', '[0-9]+')->where('id', '[0-9]+');
    });

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
    $route->group(['prefix' => 'website/{websiteId}/user'], function($route) {
        $route->post('signup', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@create');
        $route->post('login', 'App\Http\Controllers\v1\Website\User\WebsiteUserController@login');
    });

    /**
     * Website User Favorite Inventory
     */
    $route->group(['prefix' => 'website/inventory/favorite', 'middleware' => 'api.auth', 'providers' => ['website_auth']], function ($route) {
        $route->get('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@index');
        $route->post('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@create');
        $route->delete('', 'App\Http\Controllers\v1\Website\User\WebsiteUserFavoriteInventoryController@delete');
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
        // TO DO: Need a Send Email endpoint that doesn't Require Lead ID By Default
        //$route->post('leads/interactions/send-email', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@sendEmail');
        $route->get('leads/{leadId}/interactions', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@index')->where('leadId', '[0-9]+');
        $route->put('leads/{leadId}/interactions', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@create')->where('leadId', '[0-9]+');
        $route->post('leads/{leadId}/interactions/send-email', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@sendEmail')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@show')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@update')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
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

    $route->post('user/password-reset/start', 'App\Http\Controllers\v1\User\SignInController@initPasswordReset');
    $route->post('user/password-reset/finish', 'App\Http\Controllers\v1\User\SignInController@finishPasswordReset');
    $route->post('user/login', 'App\Http\Controllers\v1\User\SignInController@signIn');

    $route->group(['middleware' => 'accesstoken.validate'], function ($route) {
        $route->get('user', 'App\Http\Controllers\v1\User\SignInController@details');
        $route->post('user/check-admin-password', 'App\Http\Controllers\v1\User\SignInController@checkAdminPassword');

        $route->get('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@index');
        $route->post('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@create');
        $route->put('user/secondary-users', 'App\Http\Controllers\v1\User\SecondaryUsersController@updateBulk');

        $route->put('user/password/update', 'App\Http\Controllers\v1\User\SignInController@updatePassword');

        $route->get('user/auto-import/settings', 'App\Http\Controllers\v1\User\AutoImportController@index');
        $route->put('user/auto-import/settings', 'App\Http\Controllers\v1\User\AutoImportController@updateSettings');

        $route->get('user/overlay/settings', 'App\Http\Controllers\v1\User\OverlaySettingsController@index');
        $route->post('user/overlay/settings', 'App\Http\Controllers\v1\User\OverlaySettingsController@updateSettings');

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
    $route->get('leads/types', 'App\Http\Controllers\v1\CRM\Leads\LeadTypeController@index');
    $route->get('leads/sources', 'App\Http\Controllers\v1\CRM\Leads\LeadSourceController@index');
    $route->get('leads/sort-fields', 'App\Http\Controllers\v1\CRM\Leads\LeadController@sortFields');
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
        $route->get('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@show')->where('id', '[0-9]+');
        $route->post('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@update')->where('id', '[0-9]+');
        $route->put('leads', 'App\Http\Controllers\v1\CRM\Leads\LeadController@create');

        /*
        |--------------------------------------------------------------------------
        | Quotes
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/quotes', 'App\Http\Controllers\v1\Dms\UnitSaleController@index');

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

        $route->group(['prefix' => 'user/dealer-location/{locationId}'], function ($route) {
            $route->get('/mileage-fee', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@index');
            $route->post('/mileage-fee', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@create');
            $route->delete('/mileage-fee/{feeId}', 'App\Http\Controllers\v1\User\DealerLocationMileageFeeController@delete');
        });

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
            $route->group([
                'prefix' => 'import'
            ], function ($route) {
                $route->get('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@index');
                $route->put('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@update');
                $route->delete('/', 'App\Http\Controllers\v1\CRM\Leads\LeadImportController@delete');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Integrations
        |--------------------------------------------------------------------------
        |
        |
        |
        */
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

                // Validate SMTP/IMAP
                $route->put('validate', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@valid');

                // Sales People w/Auth
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
                    /*$route->get('/', 'App\Http\Controllers\v1\CRM\Email\TemplateController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\TemplateController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\TemplateController@destroy')->where('id', '[0-9]+');*/
                    $route->post('{id}/send', 'App\Http\Controllers\v1\CRM\Email\TemplateController@send')->where('id', '[0-9]+');
                });

                // Email Builder Campaign
                $route->group([
                    'prefix' => 'campaign',
                    'middleware' => 'emailbuilder.campaign.validate'
                ], function ($route) {
                    /*$route->get('/', 'App\Http\Controllers\v1\CRM\Email\CampaignController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\CampaignController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\CampaignController@destroy')->where('id', '[0-9]+');*/
                    $route->post('{id}/send', 'App\Http\Controllers\v1\CRM\Email\CampaignController@send')->where('id', '[0-9]+');
                });

                // Email Builder Blast
                $route->group([
                    'prefix' => 'blast',
                    'middleware' => 'emailbuilder.blast.validate'
                ], function ($route) {
                    /*$route->get('/', 'App\Http\Controllers\v1\CRM\Email\BlastController@index');
                    $route->put('/', 'App\Http\Controllers\v1\CRM\Email\BlastController@create');
                    $route->get('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@show')->where('id', '[0-9]+');
                    $route->post('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@update')->where('id', '[0-9]+');
                    $route->delete('{id}', 'App\Http\Controllers\v1\CRM\Email\BlastController@destroy')->where('id', '[0-9]+');*/
                    $route->post('{id}/send', 'App\Http\Controllers\v1\CRM\Email\BlastController@send')->where('id', '[0-9]+');
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
});
