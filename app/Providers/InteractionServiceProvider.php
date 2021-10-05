<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CRM\Email\BounceRepository;
use App\Repositories\CRM\Email\BounceRepositoryInterface;
use App\Repositories\CRM\Email\BlastRepository as EmailBlastRepository;
use App\Repositories\CRM\Email\BlastRepositoryInterface as EmailBlastRepositoryInterface;
use App\Repositories\CRM\Email\CampaignRepository as EmailCampaignRepository;
use App\Repositories\CRM\Email\CampaignRepositoryInterface as EmailCampaignRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepository as EmailTemplateRepository;
use App\Repositories\CRM\Email\TemplateRepositoryInterface as EmailTemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepository;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepository;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepository as TextBlastRepository;
use App\Repositories\CRM\Text\BlastRepositoryInterface as TextBlastRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepository as TextCampaignRepository;
use App\Repositories\CRM\Text\CampaignRepositoryInterface as TextCampaignRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepository as TextTemplateRepository;
use App\Repositories\CRM\Text\TemplateRepositoryInterface as TextTemplateRepositoryInterface;
use App\Repositories\CRM\Text\TextRepository;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepository;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Services\CRM\Email\EmailBuilderService;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Email\ImapService;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\CRM\Email\ScrapeRepliesService;
use App\Services\CRM\Email\ScrapeRepliesServiceInterface;
use App\Services\CRM\Text\BlastService as TextBlastService;
use App\Services\CRM\Text\BlastServiceInterface as TextBlastServiceInterface;
use App\Services\CRM\Text\CampaignService as TextCampaignService;
use App\Services\CRM\Text\CampaignServiceInterface as TextCampaignServiceInterface;
use App\Services\CRM\Text\TwilioService;
use App\Services\CRM\Text\TextServiceInterface;
use App\Services\CRM\Interactions\InteractionService;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailService;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailService;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;

class InteractionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Text Services
        $this->app->bind(TextServiceInterface::class, TwilioService::class);
        $this->app->bind(TextBlastServiceInterface::class, TextBlastService::class);
        $this->app->bind(TextCampaignServiceInterface::class, TextCampaignService::class);

        // Campaign Services
        $this->app->bind(EmailBuilderServiceInterface::class, EmailBuilderService::class);
        //$this->app->bind(EmailBlastServiceInterface::class, EmailBlastService::class);
        //$this->app->bind(EmailCampaignServiceInterface::class, EmailCampaignService::class);

        // Interaction Services
        $this->app->bind(ScrapeRepliesServiceInterface::class, ScrapeRepliesService::class);
        $this->app->bind(ImapServiceInterface::class, ImapService::class);
        $this->app->bind(InteractionServiceInterface::class, InteractionService::class);
        $this->app->bind(InteractionEmailServiceInterface::class, InteractionEmailService::class);
        $this->app->bind(NtlmEmailServiceInterface::class, NtlmEmailService::class);


        // Text Repositories
        $this->app->bind(TextRepositoryInterface::class, TextRepository::class);
        $this->app->bind(TextTemplateRepositoryInterface::class, TextTemplateRepository::class);
        $this->app->bind(TextCampaignRepositoryInterface::class, TextCampaignRepository::class);
        $this->app->bind(TextBlastRepositoryInterface::class, TextBlastRepository::class);
        $this->app->bind(NumberRepositoryInterface::class, NumberRepository::class);

        // EmailBuilder Repositories
        $this->app->bind(BounceRepositoryInterface::class, BounceRepository::class);
        $this->app->bind(EmailTemplateRepositoryInterface::class, EmailTemplateRepository::class);
        $this->app->bind(EmailCampaignRepositoryInterface::class, EmailCampaignRepository::class);
        $this->app->bind(EmailBlastRepositoryInterface::class, EmailBlastRepository::class);

        // Interaction Repositories
        $this->app->bind(EmailHistoryRepositoryInterface::class, EmailHistoryRepository::class);
        $this->app->bind(InteractionsRepositoryInterface::class, InteractionsRepository::class);
    }

}