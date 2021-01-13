<?php

namespace App\Providers;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\Export\IDSService;
use App\Repositories\CRM\Leads\Export\IDSLeadRepository;
use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IDSServiceInterface::class, IDSService::class);
        $this->app->bind(IDSLeadRepositoryInterface::class, IDSLeadRepository::class);
        $this->app->bind(LeadEmailRepositoryInterface::class, LeadEmailRepository::class);
    }

}
