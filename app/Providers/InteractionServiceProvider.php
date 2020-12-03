<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CRM\Interactions\InteractionsRepository;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepository;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepository;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepository;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepository;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\CRM\Text\TextRepository;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepository;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Services\CRM\Email\ScrapeRepliesService;
use App\Services\CRM\Email\ScrapeRepliesServiceInterface;
use App\Services\CRM\Text\TwilioService;
use App\Services\CRM\Text\TextServiceInterface;
use App\Services\CRM\Text\BlastService;
use App\Services\CRM\Text\BlastServiceInterface;
use App\Services\CRM\Text\CampaignService;
use App\Services\CRM\Text\CampaignServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailService;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Interaction Services
        $this->app->bind(TextServiceInterface::class, TwilioService::class);
        $this->app->bind(BlastServiceInterface::class, BlastService::class);
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);
        $this->app->bind(ScrapeRepliesServiceInterface::class, ScrapeRepliesService::class);
        $this->app->bind(InteractionEmailServiceInterface::class, InteractionEmailService::class);

        // Interaction Repositories
        $this->app->bind(TextRepositoryInterface::class, TextRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(BlastRepositoryInterface::class, BlastRepository::class);
        $this->app->bind(NumberRepositoryInterface::class, NumberRepository::class);
        $this->app->bind(EmailHistoryRepositoryInterface::class, EmailHistoryRepository::class);
        $this->app->bind(InteractionsRepositoryInterface::class, InteractionsRepository::class);
    }

}
