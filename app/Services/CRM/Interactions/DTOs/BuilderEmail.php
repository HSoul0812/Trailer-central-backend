<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Traits\MailHelper;

/**
 * Class BuilderEmail
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class BuilderEmail
{
    use MailHelper;


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
     * Add Lead Details
     * 
     * @param int $leadId
     * @param string $email
     * @param string $name
     * @return void
     */
    public function addLeadConfig(int $leadId, string $email, string $name): void
    {
        $this->leadId = $leadId;
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Get Filled Template
     * 
     * @param int $emailId
     * @return string
     */
    public function getFilledTemplate(int $emailId): string
    {
        // Get Template
        $filled = $this->template;

        // Fill Variables
        foreach(self::BUILDER_TEMPLATE_VARIABLES as $var => $replace) {
            $filled = str_replace('{' . $var . '}', $this->{$replace}, $filled);
        }

        // Check for Verlafix
        if(strpos($filled, "[unsubscribe_link]") !== FALSE) {
            $filled = str_replace(self::UNSUBSCRIBE_LINK_VAR, self::UNSUBSCRIBE_LINK . $emailId, $filled);
        }

        // Return Updated Template
        return $filled;
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
        // Get Unique Message ID
        $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());

        // Return ParsedEmail
        return new ParsedEmail([
            'email_history_id' => $emailId,
            'message_id' => sprintf('<%s>', $messageId),
            'lead_id' => $this->leadId,
            'to' => $this->toEmail,
            'to_name' => $this->toName,
            'from_email' => $this->fromEmail,
            'subject' => $this->subject,
            'body' => $this->getFilledTemplate($emailId),
            'is_html' => true
        ]);
    }
}