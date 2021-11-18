<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Email\SendEmailFailedException;
use App\Mail\InteractionEmail;
use App\Models\CRM\Email\Attachment;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\User\DTOs\EmailSettings;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

/**
 * Class InteractionEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class InteractionEmailService implements InteractionEmailServiceInterface
{
    use CustomerHelper, MailHelper;


    /**
     * @var UserRepositoryInterface
     */
    private $users;

    /**
     * @var SalesPeresonRepositoryInterface
     */
    private $salespeople;


    /**
     * InteractionEmailServiceconstructor.
     * 
     * @param UserRepositoryInterface $users
     * @param SalesPersonRepositoryInterface $salespeople
     */
    public function __construct(UserRepositoryInterface $users, SalesPersonRepositoryInterface $salespeople) {
        $this->users = $users;
        $this->salespeople = $salespeople;
    }

    /**
     * Get Email Config Settings
     * 
     * @param int $dealerId
     * @param null|int $salesPersonId
     * @return EmailSettings
     */
    public function config(int $dealerId, ?int $salesPersonId = null): EmailSettings {
        // Get User Data
        $user = $this->users->get(['dealer_id' => $dealerId]);
        if(!empty($salesPersonId)) {
            $salesPerson = $this->salespeople->get(['sales_person_id' => $salesPersonId]);
        }

        // Get Default Email + Reply-To
        if(empty($salesPerson->id)) {
            return new EmailSettings([
                'dealer_id' => $dealerId,
                'type' => 'dealer',
                'method' => 'smtp',
                'config' => EmailSettings::CONFIG_DEFAULT,
                'perms' => 'admin',
                'from_email' => config('mail.from.address'),
                'from_name' => $user->name,
                'reply_email' => $user->email,
                'reply_name' => $user->name
            ]);
        }


        // Get SMTP Config
        $smtpConfig = SmtpConfig::fillFromSalesPerson($salesPerson);

        // Set Access Token on SMTP Config
        if($smtpConfig->isAuthConfigOauth()) {
            $smtpConfig->setAccessToken($this->refreshToken($smtpConfig->accessToken));
            $smtpConfig->calcAuthConfig();
        }

        // SMTP Valid?
        $smtpValid = $salesPerson->smtp_validate->success;
        if(!$smtpValid) {
            $smtpValid = ($smtpConfig->getAuthMode() === 'oauth');
        }

        // Get Sales Person Settings
        return new EmailSettings([
            'dealer_id' => $dealerId,
            'sales_person_id' => $salesPersonId,
            'type' => 'sales_person',
            'method' => $smtpConfig->isAuthConfigOauth() ? EmailSettings::METHOD_OAUTH : EmailSettings::METHOD_DEFAULT,
            'config' => $smtpValid ? $smtpConfig->getAuthConfig() : EmailSettings::CONFIG_DEFAULT,
            'perms' => $salesPerson->perms,
            'from_email' => $smtpValid ? $smtpConfig->getUsername() : config('mail.from.address'),
            'from_name' => $smtpConfig->getFromName(),
            'reply_email' => !$smtpValid ? $smtpConfig->getUsername() : null,
            'reply_name' => !$smtpValid ? $smtpConfig->getFromName() : null
        ]);
    }

    /**
     * Send Email With Params
     * 
     * @param EmailSettings $emailConfig
     * @param null|SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     * @return ParsedEmail
     */
    public function send(EmailSettings $emailConfig, ?SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Get Unique Message ID
        if(empty($parsedEmail->getMessageId())) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
            $parsedEmail->setMessageId(sprintf('<%s>', $messageId));
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $parsedEmail->getMessageId()));
        }

        // Try/Send Email!
        try {
            // Get From Email
            $fromEmail = ($smtpConfig !== null) ? $smtpConfig->getUsername() : config('mail.from.address');
            Log::info('Send from ' . $fromEmail . ' to: ' .
                        $parsedEmail->getToName() . ' <' . $parsedEmail->getToEmail() . '>');

            // Create Interaction Email
            $interactionEmail = new InteractionEmail([
                'date' => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'subject' => $parsedEmail->getSubject(),
                'body' => $parsedEmail->getBody(),
                'attach' => $parsedEmail->getAllAttachments(),
                'id' => $messageId
            ]);

            // Send Email
            if($emailConfig->config !== 'default' && $smtpConfig !== null) {
                $this->sendCustomEmail($smtpConfig, [
                    'email' => $parsedEmail->getToEmail(),
                    'name' => $parsedEmail->getToName()
                ], $interactionEmail);
            } else {
                $this->sendDefaultEmail($emailConfig, [
                    'email' => $parsedEmail->getToEmail(),
                    'name' => $parsedEmail->getToName()
                ], $interactionEmail);
            }
        } catch(\Exception $ex) {
            throw new SendEmailFailedException($ex->getMessage());
        }

        // Store Attachments
        if($parsedEmail->hasAttachments()) {
            $parsedEmail->setAttachments($this->storeAttachments($emailConfig->dealerId, $parsedEmail));
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }

    /**
     * Store Uploaded Attachments
     * 
     * @param int $dealerId
     * @param ParsedEmail $parsedEmail
     * @return Collection<Attachment>
     */
    public function storeAttachments(int $dealerId, ParsedEmail $parsedEmail): Collection {
        // Calculate Directory
        $messageDir = str_replace(">", "", str_replace("<", "", $parsedEmail->getMessageId()));

        // Valid Attachment Size?!
        if($parsedEmail->validateAttachmentsSize()) {
            // Loop Attachments
            $attachments = new Collection();
            foreach ($parsedEmail->getAllAttachments() as $file) {
                // Generate Path
                $filePath = 'crm/' . $dealerId . '/' . $messageDir . '/attachments/' . $file->getFileName();

                // Save File to S3
                Storage::disk('ses')->put($filePath, $file->getContents());

                // Set File Name/Path
                $file->setFilePath(Attachment::AWS_PREFIX . '/' . $filePath);
                $file->setFileName(time() . $file->getFileName());

                // Add File
                $attachments->push($file);
            }

            // Return Attachment Objects
            return $attachments;
        }

        // Return All Attachments
        return $parsedEmail->getAllAttachments();
    }
}
