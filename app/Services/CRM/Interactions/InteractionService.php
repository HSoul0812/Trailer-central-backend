<?php

namespace App\Services\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class InteractionEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class InteractionService implements InteractionServiceInterface
{
    /**
     * InteractionsRepository constructor.
     * 
     * @param EmailHistoryRepositoryInterface
     */
    public function __construct(
        GoogleServiceInterface $google,
        GmailServiceInterface $gmail,
        InteractionEmailServiceInterface $service,
        InteractionsRepositoryInterface $interactions,
        EmailHistoryRepositoryInterface $emailHistory,
        TokenRepositoryInterface $tokens
    ) {
        $this->google = $google;
        $this->gmail = $gmail;
        $this->interactionEmail = $service;
        $this->interactions = $interactions;
        $this->emailHistory = $emailHistory;
        $this->tokens = $tokens;
    }

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @param array $attachments
     * @return Interaction || error
     */
    public function email($leadId, $params, $attachments = array()) {
        // Find Lead/Sales Person
        $lead = Lead::findOrFail($leadId);
        $user = Auth::user();
        $accessToken = null;
        if(!empty($user->sales_person)) {
            // Set From Name/Email
            $params['from_email'] = $user->sales_person->smtp_email;
            $params['from_name'] = $user->sales_person->full_name ?? '';

            // Get Sales Person Auth
            $accessToken = $this->tokens->getRelation([
                'token_type' => 'google',
                'relation_type' => 'sales_person',
                'relation_id' => $user->sales_person->id
            ]);
            if(empty($accessToken->id)) {
                // Set Sales Person Config
                $this->interactionEmail->setSalesPersonSmtpConfig($user->sales_person);
            }
        } else {
            $params['from_email'] = $user->email;
            $params['from_name'] = $user->name ?? '';

            // Are We a Dealer User?!
            if(!empty($user->user) && empty($params['from_name'])) {
                $params['from_name'] = $user->user->name ?? '';
            }
        }

        // Get Draft if Exists
        $emailHistory = $this->emailHistory->findEmailDraft($params['from_email'], $lead->identifier);
        if(!empty($emailHistory->message_id)) {
            $params['id']             = $emailHistory->email_id;
            $params['interaction_id'] = $emailHistory->interaction_id;
            $params['message_id']     = $emailHistory->message_id;
        }

        // Set Lead Details
        $params['to_email'] = $lead->email_address;
        $params['to_name']  = $lead->full_name;

        // Append Attachments
        if(!isset($params['attachments'])) {
            $params['attachments'] = array();
        }
        $params['attachments'] = array_merge($params['attachments'], $attachments);

        // Send Email
        if(!empty($accessToken->id)) {
            // Validate/Refresh Token
            $accessToken = $this->refreshToken($accessToken);

            // Send Email
            $email = $this->gmail->send($accessToken, $params);
        } else {
            $email = $this->interactionEmail->send($lead->dealer_id, $params);
        }

        // Save Email
        return $this->saveEmail($leadId, $user->newDealerUser->user_id, $email);
    }


    /**
     * Save Email From Send Email
     * 
     * @param type $leadId
     * @param type $userId
     * @param type $params
     * @return Interaction
     */
    private function saveEmail($leadId, $userId, $params) {
        // Initialize Transaction
        DB::transaction(function() use (&$params, $leadId, $userId) {
            // Create or Update
            $interaction = $this->interactions->createOrUpdate([
                'id'                => $params['interaction_id'] ?? 0,
                'lead_id'           => $leadId,
                'user_id'           => $userId,
                'interaction_type'  => "EMAIL",
                'interaction_notes' => "E-Mail Sent: {$params['subject']}",
                'interaction_time'  => Carbon::now()->toDateTimeString(),
            ]);

            // Set Interaction ID!
            $params['interaction_id'] = $interaction->interaction_id;

            // Insert Email
            $params['date_sent'] = 1;
            $this->emailHistory->createOrUpdate($params);
        });

        // Return Interaction
        return $this->interactions->get([
            'id' => $params['interaction_id']
        ]);
    }

    /**
     * Check If Token is Expired, Refresh if it Is
     * 
     * @param AccessToken $accessToken
     * @return AccessToken
     */
    private function refreshToken($accessToken) {
        // Validate Token
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Return Access Token
        return $accessToken;
    }
}
