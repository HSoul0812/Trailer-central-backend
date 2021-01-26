<?php

namespace App\Providers;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\Export\IDSService;
use App\Services\CRM\Leads\Import\ADFServiceInterface as ADFImportServiceInterface;
use App\Services\CRM\Leads\Import\ADFService as ADFImportService;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\Export\IDSLeadRepository;
use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface as CustomerInventoryRepositoryInterface;
use App\Repositories\Dms\Customer\InventoryRepository as CustomerInventoryRepository;
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
        // Services
        $this->app->bind(IDSServiceInterface::class, IDSService::class);
        $this->app->bind(ADFImportServiceInterface::class, ADFImportService::class);
        $this->app->bind(AutoAssignServiceInterface::class, AutoAssignService::class);

        // Repositories
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(IDSLeadRepositoryInterface::class, IDSLeadRepository::class);
        $this->app->bind(LeadEmailRepositoryInterface::class, LeadEmailRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CustomerInventoryRepositoryInterface::class, CustomerInventoryRepository::class);
    }

}
