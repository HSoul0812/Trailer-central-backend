<?php

namespace App\Services\CRM\Interactions\DTOs;

/**
 * Class EmailBuilderConfig
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class EmailBuilderConfig
{
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
     * @var string From Email to Use to Send Email Builder From
     */
    private $fromEmail;

    /**
     * @var string To Email to Use to Send Email Builder To
     */
    private $toEmail;

    /**
     * @var string To Name to Use to Send Email Builder To
     */
    private $toName;
}