<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\WebsiteUser\AuthService;
use App\Services\WebsiteUser\AuthServiceInterface;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    public function register()
    {
        parent::register();
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }
}
