<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Models\CRM\Leads\Lead;
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
     * @var array List of Template Replacement Variables
     */
    const BUILDER_TEMPLATE_VARIABLES = [
        '{lead_name}' => 'toName',
        '{title_of_unit_of_interest}' => 'titleUnitInterest'
    ];

    /**
     * @var string Unsubscribe Link Base URL
     */
    const UNSUBSCRIBE_LINK = 'https://crm.trailercentral.com/emailtracker/unsubscribe/';


    /**
     * @var int ID of Type
     */
    private $id; // blast id | campaign id | template id

    /**
     * @var string Type of Email Builder Config
     */
    private $type = 'template'; // blast | campaign | template

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

        // Check for Verlafix
        if(strpos($filled, "[unsubscribe_link]") !== FALSE && $emailId !== null) {
            $filled = str_replace(self::UNSUBSCRIBE_LINK_VAR, self::UNSUBSCRIBE_LINK . $emailId, $filled);
        }

        // Return Updated Template
        return $filled;
    }


    /**
     * Get Email History Params Fill Array
     * 
     * @param int $interactionId
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
            'to_email' => $this->toEmail,
            'to_name' => $this->toName,
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
            $this->type => $this->id
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
            'lead_id' => $this->leadId,
            'to' => $this->toEmail,
            'to_name' => $this->toName,
            'from' => $this->fromEmail,
            'subject' => $this->subject,
            'body' => $this->getFilledTemplate($emailId),
            'is_html' => true
        ]);
    }
}