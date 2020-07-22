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
    $route->get('inventory/floorplan/payments', 'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@index');
    $route->put('inventory/floorplan/payments', 'App\Http\Controllers\v1\Inventory\Floorplan\PaymentController@create');

    /**
     * Part bins
     */
    $route->get('parts/bins', 'App\Http\Controllers\v1\Parts\BinController@index');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@show')->where('id', '[0-9]+');
    $route->post('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@update')->where('id', '[0-9]+');
    $route->delete('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@destroy')->where('id', '[0-9]+');


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
     * Part Bulk download
     */
    $route->post('parts/bulk/download', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@create');
    $route->get('parts/bulk/file/{token}', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@read');

    /**
     * Part Bulk
     */
    $route->get('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@index');
    $route->post('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@create');
    $route->get('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@show')->where('id', '[0-9]+');
    $route->put('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@update')->where('id', '[0-9]+');
    $route->delete('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@destroy')->where('id', '[0-9]+');

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
     * Parts
     */
    $route->get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
    $route->put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
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
     * Inventory Manufacturers
     */
    $route->get('inventory/manufacturers', 'App\Http\Controllers\v1\Inventory\ManufacturerController@index');

    /**
     * Inventory
     */
    $route->get('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@index');
    $route->put('inventory', 'App\Http\Controllers\v1\Inventory\InventoryController@create');
    $route->get('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+');
    $route->post('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@update')->where('id', '[0-9]+');
    $route->delete('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@destroy')->where('id', '[0-9]+');


    /*
    |--------------------------------------------------------------------------
    | Website
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Website Part Filters
     */
    $route->get('website/parts/filters', 'App\Http\Controllers\v1\Website\Parts\FilterController@index');
    $route->put('website/parts/filters', 'App\Http\Controllers\v1\Parts\FilterController@create');
    $route->get('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@show')->where('id', '[0-9]+');
    $route->post('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@update')->where('id', '[0-9]+');
    $route->delete('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@destroy')->where('id', '[0-9]+');

    /**
     * Website Blog Posts
     */
    $route->get('website/blog/posts', 'App\Http\Controllers\v1\Website\Blog\PostController@index');
    $route->put('website/blog/posts', 'App\Http\Controllers\v1\Website\Blog\PostController@create');
    $route->get('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@show')->where('id', '[0-9]+');
    $route->post('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@update')->where('id', '[0-9]+');
    $route->delete('website/blog/posts/{id}', 'App\Http\Controllers\v1\Website\Blog\PostController@destroy')->where('id', '[0-9]+');

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
        $route->post('leads/{leadId}/interactions/send-email', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@sendEmail')->where('leadId', '[0-9]+');
        $route->get('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@show')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@update')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('leads/{leadId}/interactions/{id}', 'App\Http\Controllers\v1\CRM\Interactions\InteractionsController@destroy')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
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
        $route->post('leads/{leadId}/texts/{id}/stop', 'App\Http\Controllers\v1\CRM\Text\TextController@stop')->where('leadId', '[0-9]+')->where('id', '[0-9]+');
    });

    /**
     * Texts Template
     */
    $route->group(['middleware' => 'text.template.validate'], function ($route) {
        $route->get('crm/{userId}/texts/template', 'App\Http\Controllers\v1\CRM\Text\TemplateController@index')->where('userId', '[0-9]+');
        $route->put('crm/{userId}/texts/template', 'App\Http\Controllers\v1\CRM\Text\TemplateController@create')->where('userId', '[0-9]+');
        $route->get('crm/{userId}/texts/template/{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@show')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('crm/{userId}/texts/template/{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@update')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('crm/{userId}/texts/template/{id}', 'App\Http\Controllers\v1\CRM\Text\TemplateController@destroy')->where('userId', '[0-9]+')->where('id', '[0-9]+');
    });

    /**
     * Texts Campaign
     */
    $route->group(['middleware' => 'text.campaign.validate'], function ($route) {
        $route->get('crm/{userId}/texts/campaign', 'App\Http\Controllers\v1\CRM\Text\CampaignController@index')->where('userId', '[0-9]+');
        $route->put('crm/{userId}/texts/campaign', 'App\Http\Controllers\v1\CRM\Text\CampaignController@create')->where('userId', '[0-9]+');
        $route->get('crm/{userId}/texts/campaign/{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@show')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('crm/{userId}/texts/campaign/{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@update')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('crm/{userId}/texts/campaign/{id}', 'App\Http\Controllers\v1\CRM\Text\CampaignController@destroy')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('crm/{userId}/texts/campaign/{id}/sent', 'App\Http\Controllers\v1\CRM\Text\CampaignController@sent')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->get('crm/{userId}/texts/campaign/{id}/leads', 'App\Http\Controllers\v1\CRM\Text\CampaignController@leads')->where('userId', '[0-9]+')->where('id', '[0-9]+');
    });

    /**
     * Texts Blast
     */
    $route->group(['middleware' => 'text.blast.validate'], function ($route) {
        $route->get('crm/{userId}/texts/blast', 'App\Http\Controllers\v1\CRM\Text\BlastController@index')->where('userId', '[0-9]+');
        $route->put('crm/{userId}/texts/blast', 'App\Http\Controllers\v1\CRM\Text\BlastController@create')->where('userId', '[0-9]+');
        $route->get('crm/{userId}/texts/blast/{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@show')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('crm/{userId}/texts/blast/{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@update')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->delete('crm/{userId}/texts/blast/{id}', 'App\Http\Controllers\v1\CRM\Text\BlastController@destroy')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->post('crm/{userId}/texts/blast/{id}/sent', 'App\Http\Controllers\v1\CRM\Text\BlastController@sent')->where('userId', '[0-9]+')->where('id', '[0-9]+');
        $route->get('crm/{userId}/texts/blast/{id}/leads', 'App\Http\Controllers\v1\CRM\Text\BlastController@leads')->where('userId', '[0-9]+')->where('id', '[0-9]+');
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

    // upload feed data
    $route->post('feed/uploader/{code}', 'App\Http\Controllers\v1\Feed\UploadController@upload')->where('code', '\w+');

    // Factory
    $route->get('feed/factory/showroom', 'App\Http\Controllers\v1\Feed\Factory\ShowroomController@index');

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    $route->post('user/login', 'App\Http\Controllers\v1\User\SignInController@signIn');

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
    $route->get('leads/sort-fields', 'App\Http\Controllers\v1\CRM\Leads\LeadController@sortFields');

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
        $route->post('leads/{id}', 'App\Http\Controllers\v1\CRM\Leads\LeadController@update');
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
        | Sales People
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/sales-people', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@index');

        /*
        |--------------------------------------------------------------------------
        | Sales People
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/dealer-location', 'App\Http\Controllers\v1\User\DealerLocationController@index');

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/customers', 'App\Http\Controllers\v1\Dms\Customer\CustomerController@index');
        $route->get('user/customers/balance/open', 'App\Http\Controllers\v1\Dms\Customer\OpenBalanceController@index');

        /*
        |--------------------------------------------------------------------------
        | Interactions
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('user/interactions/tasks', 'App\Http\Controllers\v1\CRM\Interactions\TasksController@index');

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

        /*
        |--------------------------------------------------------------------------
        | POS
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('pos/sales', 'App\Http\Controllers\v1\Pos\SalesController@index');
        $route->get('pos/sales/{id}', 'App\Http\Controllers\v1\Pos\SalesController@show');

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

        /*
        |--------------------------------------------------------------------------
        | Various reports
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        $route->get('reports/sales-person-sales', 'App\Http\Controllers\v1\CRM\User\SalesPersonController@salesReport');
    });

});
