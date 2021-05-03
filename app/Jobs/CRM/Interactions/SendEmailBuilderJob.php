<?php

namespace App\Jobs\CRM\Interactions;

use App\Jobs\Job;
use App\Mail\Interactions\EmailBuilderEmail;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Services\CRM\Interactions\DTOs\EmailBuilderConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SendEmailBuilderJob
 * @package App\Jobs\CRM\Interactions
 */
class SendEmailBuilderJob extends Job
{ 
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var EmailBuilderConfig
     */
    private $config;

    /**
     * SendEmailBuilder constructor.
     * @param EmailBuilderConfig $config
     */
    public function __construct(EmailBuilderConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param TemplateRepositoryInterface $templateRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @throws SendEmailBuilderFailedException
     * @return boolean
     */
    public function handle(
        TemplateRepositoryInterface $templateRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ) {
        Log::info('Mailing Email Builder Email', [
            'lead' => $this->config->leadId,
            'type' => $this->config->type,
            $this->config->type => $this->config->id
        ]);

        try {
            Mail::to($this->config->getToEmail())
                ->send(
                    new EmailBuilderEmail($config->getEmailData())
                );

            // Mark as Sent
            $this->markSent($campaignRepo, $blastRepo);

            // Log to Database
            $this->markSent($campaignRepo, $blastRepo);

            Log::info('Email Builder Mailed Successfully', [
                'lead' => $this->config->leadId,
                'type' => $this->config->type,
                $this->config->type => $this->config->id
            ]);
            return true;
        } catch (\Exception $e) {
            // Flag it as sent anyway
            $this->markSent($templateRepo, $campaignRepo, $blastRepo);
            Log::error('Email Builder Mail error', $e->getTrace());
            throw new SendEmailBuilderFailedException($e);
        }
    }

    /**
     * @param TemplateRepositoryInterface $templateRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @return boolean
     */
    private function markSent(
        TemplateRepositoryInterface $templateRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ) {
        
    }
}
