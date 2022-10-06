<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\WebsiteUser\WebsiteUserRepository;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\WebsiteUser\AuthService;
use App\Services\WebsiteUser\AuthServiceInterface;
use App\Services\WebsiteUser\PasswordResetService;
use App\Services\WebsiteUser\PasswordResetServiceInterface;
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
        $this->app->bind(WebsiteUserRepositoryInterface::class, WebsiteUserRepository::class);

        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(PasswordResetServiceInterface::class, PasswordResetService::class);
    }
}
