<?php

namespace App\Providers;

use App\Mail\CRM\CustomEmail;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Observers\CRM\Lead\LeadObserver;
use App\Models\Observers\CRM\Lead\LeadStatusObserver;
use App\Repositories\CRM\Documents\DealerDocumentsRepository;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use App\Repositories\CRM\Leads\Export\ADFLeadRepository;
use App\Repositories\CRM\Leads\Export\ADFLeadRepositoryInterface;
use App\Repositories\CRM\Leads\LeadTradeRepository;
use App\Repositories\CRM\Leads\LeadTradeRepositoryInterface;
use App\Repositories\CRM\Refund\RefundRepository;
use App\Repositories\CRM\Refund\RefundRepositoryInterface;
use App\Services\CRM\Email\BlastService;
use App\Services\CRM\Email\BlastServiceInterface;
use App\Services\CRM\Email\CampaignService;
use App\Services\CRM\Email\CampaignServiceInterface;
use App\Services\CRM\Email\InquiryEmailService;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\Import\ImportService;
use App\Services\CRM\Leads\Import\ImportServiceInterface;
use App\Services\CRM\Leads\LeadStatusService;
use App\Services\CRM\Leads\LeadStatusServiceInterface;
use App\Services\CRM\Text\InquiryTextService;
use App\Services\CRM\Text\InquiryTextServiceInterface;
use App\Services\CRM\Leads\InquiryServiceInterface;
use App\Services\CRM\Leads\InquiryService;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\CRM\Leads\LeadService;
use App\Services\CRM\Leads\AutoAssignService;
use App\Services\CRM\Leads\AutoAssignServiceInterface;
use App\Services\CRM\Leads\HotPotatoService;
use App\Services\CRM\Leads\HotPotatoServiceInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface as ADFExportServiceInterface;
use App\Services\CRM\Leads\Export\ADFService as ADFExportService;
use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Services\CRM\Leads\Export\IDSService;
use App\Services\CRM\Leads\Export\BigTexServiceInterface;
use App\Services\CRM\Leads\Export\BigTexService;
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
use App\Repositories\CRM\Leads\FacebookRepository;
use App\Repositories\CRM\Leads\FacebookRepositoryInterface;
use App\Repositories\CRM\Leads\Export\IDSLeadRepository;
use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Repositories\CRM\Leads\Export\BigTexLeadRepository;
use App\Repositories\CRM\Leads\Export\BigTexLeadRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\CRM\Leads\Export\LeadEmailRepository;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Repositories\CRM\Customer\CustomerRepository;
use App\Repositories\CRM\User\SettingsRepository;
use App\Repositories\CRM\User\SettingsRepositoryInterface;
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
        $this->app->bind(InquiryServiceInterface::class, InquiryService::class);
        $this->app->bind(InquiryEmailServiceInterface::class, InquiryEmailService::class);
        $this->app->bind(InquiryTextServiceInterface::class, InquiryTextService::class);
        $this->app->bind(AutoAssignServiceInterface::class, AutoAssignService::class);
        $this->app->bind(HotPotatoServiceInterface::class, HotPotatoService::class);
        $this->app->bind(IDSServiceInterface::class, IDSService::class);
        $this->app->bind(BigTexServiceInterface::class, BigTexService::class);
        $this->app->bind(ADFExportServiceInterface::class, ADFExportService::class);
        $this->app->bind(ImportServiceInterface::class, ImportService::class);
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);
        $this->app->bind(BlastServiceInterface::class, BlastService::class);

        // Repositories
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
        $this->app->bind(SourceRepositoryInterface::class, SourceRepository::class);
        $this->app->bind(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->bind(TypeRepositoryInterface::class, TypeRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(FacebookRepositoryInterface::class, FacebookRepository::class);
        $this->app->bind(ADFLeadRepositoryInterface::class, ADFLeadRepository::class);
        $this->app->bind(IDSLeadRepositoryInterface::class, IDSLeadRepository::class);
        $this->app->bind(BigTexLeadRepositoryInterface::class, BigTexLeadRepository::class);
        $this->app->bind(LeadEmailRepositoryInterface::class, LeadEmailRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(CustomerInventoryRepositoryInterface::class, CustomerInventoryRepository::class);
        $this->app->bind(DealerDocumentsRepositoryInterface::class, DealerDocumentsRepository::class);
        $this->app->bind(LeadTradeRepositoryInterface::class, LeadTradeRepository::class);
        $this->app->bind(LeadStatusServiceInterface::class, LeadStatusService::class);

        // Bind Refund Repository
        $this->app->bind(RefundRepositoryInterface::class, function () {
            return new RefundRepository(Refund::query());
        });

        // Bind CRM Mailer
        $this->app->bind('crm.mailer', function($app, $config) {
            return CustomEmail::getCustomMailer($app, $config);
        });

        // Bind SES Mailer
        $this->app->bind('ses.mailer', function($app, $config) {
            return CustomEmail::getCustomSesMailer($app, $config);
        });
    }

    public function boot()
    {
        \Validator::extend('valid_lead', 'App\Rules\CRM\Leads\ValidLead@passes');
        \Validator::extend('non_lead_exists', 'App\Rules\CRM\Leads\NonLeadExists@passes');
        \Validator::extend('valid_texts_log', 'App\Rules\CRM\Text\ValidTextsLog@passes');
        \Validator::extend('jotform_enabled', 'App\Rules\CRM\Leads\JotformEnabled@passes');
        \Validator::extend('unique_text_blast_campaign_name', 'App\Rules\CRM\Text\UniqueTextBlastCampaignName@passes');
        \Validator::extend('unique_text_campaign_name', 'App\Rules\CRM\Text\UniqueTextCampaignName@passes');
        \Validator::extend('unique_email_campaign_name', 'App\Rules\CRM\Email\UniqueEmailCampaignName@passes');
        \Validator::extend('unique_email_blast_name', 'App\Rules\CRM\Email\UniqueEmailBlastName@passes');

        LeadStatus::observe(LeadStatusObserver::class);
        Lead::observe(LeadObserver::class);
    }
}
