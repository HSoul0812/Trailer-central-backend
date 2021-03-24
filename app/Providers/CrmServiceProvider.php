<?php

namespace App\Providers;

use App\Services\CRM\Leads\InquiryEmailService;
use App\Services\CRM\Leads\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\LeadService;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\Export\IDSService;
use App\Services\CRM\Leads\Import\ADFServiceInterface as ADFImportServiceInterface;
use App\Services\CRM\Leads\Import\ADFService as ADFImportService;
use App\Repositories\CRM\Leads\LeadRepository;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepository;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepository;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\TypeRepository;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use App\Repositories\CRM\Leads\UnitRepository;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
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
        $this->app->bind(LeadServiceInterface::class, LeadService::class);
        $this->app->bind(InquiryEmailServiceInterface::class, InquiryEmailService::class);
        $this->app->bind(AutoAssignServiceInterface::class, AutoAssignService::class);
        $this->app->bind(IDSServiceInterface::class, IDSService::class);
        $this->app->bind(ADFImportServiceInterface::class, ADFImportService::class);

        // Repositories
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(SourceRepositoryInterface::class, SourceRepository::class);
        $this->app->bind(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(IDSLeadRepositoryInterface::class, IDSLeadRepository::class);
        $this->app->bind(LeadEmailRepositoryInterface::class, LeadEmailRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CustomerInventoryRepositoryInterface::class, CustomerInventoryRepository::class);
    }

}
