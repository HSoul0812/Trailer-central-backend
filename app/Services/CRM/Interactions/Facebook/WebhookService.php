<?php

namespace App\Services\CRM\Interactions\Facebook;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\User;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Services\CRM\Interactions\WebhookServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Carbon\Carbon;

/**
 * Class WebhookService
 * 
 * @package App\Services\CRM\Interactions\Facebook
 */
class WebhookService implements WebhookServiceInterface
{
    /**
     * InteractionsRepository constructor.
     * 
     * @param EmailHistoryRepositoryInterface
     */
    public function __construct(
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        NtlmEmailServiceInterface $ntlm,
        InteractionEmailServiceInterface $service,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailHistory,
        TokenRepositoryInterface $tokens,
        StatusRepositoryInterface $leadStatus
    ) {
        $this->google = $google;
        $this->gmail = $gmail;
        $this->ntlm = $ntlm;
        $this->interactionEmail = $service;
        $this->interactions = $interactions;
        $this->emailHistory = $emailHistory;
        $this->tokens = $tokens;
        $this->leadStatus = $leadStatus;
    }

    /**
     * Handle Messages From Webhook
     * 
     * @param MessageWebhookRequest $request
     * @return MessageWebhookResponse
     */
    public function message(MessageWebhookRequest $request): MessageWebhookResponse {
        // Get User
        $user = User::find($params['dealer_id']);
        $lead = Lead::findOrFail($leadId);
        $salesPerson = null;
        $interactionEmail = null;
        if(isset($params['sales_person_id'])) {
            $salesPerson = SalesPerson::find($params['sales_person_id']);
        }

        // Merge Attachments if Necessary
        if(isset($params['attachments'])) {
            $attachments = array_merge($attachments, $params['attachments']);
        } else { 
            $params['attachments'] = $attachments;
        }
        
        foreach($params['attachments'] as $key => $attachment) {
            if (!is_a($attachment, UploadedFile::class)) {
                unset($params['attachments'][$key]);
            }
        }

        // Get SMTP Config
        $smtpConfig = $this->getSmtpConfig();

        // Create Parsed Email
        $parsedEmail = $this->getParsedEmail($smtpConfig, $leadId, $params);

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($smtpConfig->getUsername(), $leadId);
        if(!empty($emailHistory->email_id)) {
            $parsedEmail->setEmailHistoryId($emailHistory->email_id);
            $parsedEmail->setMessageId($emailHistory->message_id);
        }

        // Send Email
        if($smtpConfig->isAuthTypeGmail()) {
            $finalEmail = $this->gmail->send($smtpConfig, $parsedEmail);
        } elseif($smtpConfig->isAuthTypeNtlm()) {
            $finalEmail = $this->ntlm->send($user->dealer_id, $smtpConfig, $parsedEmail);
        } else {
            $finalEmail = $this->interactionEmail->send($user->dealer_id, $smtpConfig, $parsedEmail);
            $interactionEmail = true;
        }

        // Save Lead Status
        $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => Lead::STATUS_MEDIUM,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);

        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $finalEmail, $salesPerson, $interactionEmail);
    }
}
