<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Traits\MailHelper;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class BuilderEmail
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class BuilderEmail
{
    use MailHelper, WithConstructor, WithGetter;


    /**
     * @const string
     */
    const TYPE_BLAST = 'blast';

    /**
     * @const string
     */
    const TYPE_CAMPAIGN = 'campaign';

    /**
     * @const string
     */
    const TYPE_TEMPLATE = 'template';


    /**
     * @var array List of Template Replacement Variables
     */
    const BUILDER_TEMPLATE_VARIABLES = [
        '{lead_name}' => 'toName',
        '{title_of_unit_of_interest}' => 'titleUnitInterest'
    ];


    /**
     * @var string Unsubscribe Link Variable
     */
    const UNSUBSCRIBE_LINK_VAR = '[unsubscribe_link]';
    
    /**
     * @var string Unsubscribe Link Path
     */
    const UNSUBSCRIBE_LINK_PATH = '/emailtracker/unsubscribe/';

    /**
     * @var string Text of the Unsubscribe Link
     */
    const UNSUBSCRIBE_TEXT = 'To unsubscribe from this mailing list click here.';


    /**
     * @var int ID of Type
     */
    private $id; // blast id | campaign id | template id

    /**
     * @var string Type of Email Builder Config
     */
    private $type = 'template'; // blast | campaign | template

    /**
     * @var string Name of Template / Campaign / Template
     */
    private $name;

    /**
     * @var string Subject of Email to Send
     */
    private $subject;

    /**
     * @var string Template HTML From Email Template
     */
    private $template;

    /**
     * @var string ID of Origin Template Used
     */
    private $templateId;

    /**
     * @var string Message-ID Generated to Send With Email
     */
    private $messageId;


    /**
     * @var int ID of Dealer
     */
    private $dealerId;

    /**
     * @var int ID of User
     */
    private $userId;

    /**
     * @var int ID of Sales Person (if applicable)
     */
    private $salesPersonId;

    /**
     * @var string From Email to Use to Send Email Builder From
     */
    private $fromEmail;

    /**
     * @var SmtpConfig SMTP Config to Send Email From
     */
    private $smtpConfig;


    /**
     * @var int Lead ID To Email/Name Comes From
     */
    private $leadId;

    /**
     * @var string To Email to Use to Send Email Builder To
     */
    private $toEmail;

    /**
     * @var string To Name to Use to Send Email Builder To
     */
    private $toName;

    /**
     * @var int Email ID of Sent Email
     */
    private $emailId;

    /**
     * @var string Title of Primary Unit of Interest
     */
    private $titleUnitInterest;


    /**
     * Set Lead Details
     * 
     * @param Lead $lead
     * @return void
     */
    public function setLeadConfig(Lead $lead): void
    {
        // Insert Lead Details
        $this->leadId = $lead->identifier;
        $this->toEmail = $lead->email_address;
        $this->toName = $lead->full_name;

        // Get Title of Unit of Interest
        $this->titleUnitInterest = $lead->inventory_title;

        // Get Unique Message ID
        $this->messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
    }

    /**
     * Set To Email
     * 
     * @param string $toEmail
     * @return void
     */
    public function setToEmail(string $toEmail): void
    {
        // Set To Email
        $this->toEmail = $toEmail;
    }

    /**
     * Set Email ID
     * 
     * @param int $emailId
     * @return void
     */
    public function setEmailId(int $emailId): void
    {
        // Set Email ID
        $this->emailId = $emailId;
    }


    /**
     * Return Auth Configuration Type
     * 
     * @return string $this->smtpConfig->authType || SmtpConfig::AUTH_SMTP
     */
    public function getAuthConfig(): string
    {
        if(!empty($this->smtpConfig)) {
            return $this->smtpConfig->getAuthConfig();
        }
        return SmtpConfig::AUTH_SMTP;
    }

    /**
     * Is Auth Type Gmail?
     * 
     * @return bool $this->smtpConfig->getAuthType() === SmtpConfig::AUTH_GMAIL || false
     */
    public function isAuthTypeGmail(): bool
    {
        if(!empty($this->smtpConfig)) {
            return $this->smtpConfig->isAuthTypeGmail();
        }
        return false;
    }

    /**
     * Is Auth Type NTLM?
     * 
     * @return bool $this->smtpConfig->getAuthType() === SmtpConfig::AUTH_NTLM || false
     */
    public function isAuthTypeNtlm(): bool
    {
        if(!empty($this->smtpConfig)) {
            return $this->smtpConfig->isAuthTypeNtlm();
        }
        return false;
    }


    /**
     * Get To Email Details
     * 
     * @return array{email: string, ?name: string}
     */
    public function getToEmail() {
        // Initialize To Array
        $to = ['email' => trim($this->toEmail)];

        // Append Name
        if($this->toName) {
            $to['name'] = trim($this->toName);
        }

        // Return To
        return $to;
    }

    /**
     * Get Type Name
     * 
     * @return string Email Blast | Email Campaign | Email Template
     */
    public function getTypeName(): string
    {
        return 'Email ' . ucfirst($this->type);
    }

    /**
     * Return Message ID or Generate New One
     * 
     * @return string
     */
    public function getMessageId() {
        // No Message ID Exists?
        if(empty($this->messageId)) {
            // Get Unique Message ID
            $this->messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
        }

        // Return Message ID
        return $this->messageId;
    }

    /**
     * Get Filled Template
     * 
     * @param null|int $emailId
     * @return string
     */
    public function getFilledTemplate(?int $emailId = null): string
    {
        // Get Template
        $filled = $this->template;

        // Fill Variables
        foreach(self::BUILDER_TEMPLATE_VARIABLES as $var => $replace) {
            $filled = str_replace('{' . $var . '}', $this->{$replace}, $filled);
        }

        // Append Unsubscribe?
        if($emailId !== null) {
            // Check for Verlafix
            if(strpos($filled, self::UNSUBSCRIBE_LINK_VAR) !== FALSE) {
                $filled = str_replace(self::UNSUBSCRIBE_LINK_VAR, trim(config('app.crm_url'), '/') . self::UNSUBSCRIBE_LINK_PATH . $emailId, $filled);
            } else {
                $filled .= $this->getUnsubscribeHtml($emailId);
            }
        }

        // Return Updated Template
        return $filled;
    }

    /**
     * Get Unsubscribe HTML + Text
     * 
     * @param int $emailId
     * @return string
     */
    public function getUnsubscribeHtml(int $emailId): string
    {
        return '<br /><br />
                <p>
                    <a href="' . trim(config('app.crm_url'), '/') . self::UNSUBSCRIBE_LINK_PATH . $emailId . '">' .
                        self::UNSUBSCRIBE_TEXT . '
                    </a>
                </p>';
    }


    /**
     * Get Email History Params Fill Array
     * 
     * @param null|int $interactionId
     * @return array{lead_id: null|int,
     *               interaction_id: null|int
     *               message_id: string,
     *               to_email: string,
     *               to_name: string,
     *               from_email: string,
     *               subject: string,
     *               body: string,
     *               use_html: bool}
     */
    public function getEmailHistoryParams(?int $interactionId = 0): array
    {
        // Return Email History Fill Params
        return [
            'lead_id' => !empty($this->leadId) ? $this->leadId : 0,
            'interaction_id' => !empty($interactionId) ? $interactionId : 0,
            'message_id' => $this->getMessageId(),
            'to_email' => !empty($this->toEmail) ? $this->toEmail : '',
            'to_name' => !empty($this->toName) ? $this->toName : '',
            'from_email' => $this->fromEmail,
            'subject' => $this->subject,
            'body' => $this->getFilledTemplate(),
            'use_html' => true
        ];
    }

    /**
     * Get Log Params
     * 
     * @return array{lead: int,
     *               type: string,
     *               $this->type: int}
     */
    public function getLogParams(): array
    {
        return [
            'lead' => $this->leadId,
            'type' => $this->type,
            $this->type => $this->id,
            'email' => $this->emailId
        ];
    }

    /**
     * Get Parsed Email From EmailBuilder Config
     * 
     * @param int $emailId
     * @return ParsedEmail
     */
    public function getParsedEmail(int $emailId = 0): ParsedEmail
    {
        // Return ParsedEmail
        return new ParsedEmail([
            'email_history_id' => $emailId,
            'message_id' => sprintf('<%s>', $this->getMessageId()),
            'lead_id' => $this->leadId ?? 0,
            'to' => $this->toEmail,
            'to_name' => $this->toName ?? '',
            'from' => $this->fromEmail,
            'subject' => $this->subject,
            'body' => $this->getFilledTemplate($emailId),
            'is_html' => true
        ]);
    }
}