<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() 
    {
        \Validator::extend('type_exists', 'App\Rules\Parts\TypeExists@passes');
        \Validator::extend('category_exists', 'App\Rules\Parts\CategoryExists@passes');
        \Validator::extend('brand_exists', 'App\Rules\Parts\BrandExists@passes');
        \Validator::extend('manufacturer_exists', 'App\Rules\Parts\ManufacturerExists@passes');
        \Validator::extend('price_format', 'App\Rules\PriceFormat@passes');
    }
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
