<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class EmailSettings
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class EmailSettings
{
    use WithConstructor, WithGetter;


    /**
     * @const Default Config Type
     */
    const CONFIG_DEFAULT = 'default';


    /**
     * @var string Type of Email Config Settings
     */
    private $type = 'dealer'; // dealer | sales_person

    /**
     * @var string From Email to Use to Send Email From
     */
    private $config; // default | smtp | gmail | office | ntlm

    /**
     * @var string Permissions of Current User
     */
    private $perms; // admin | user

    /**
     * @var string From Email to Use to Send Email From
     */
    private $fromEmail;

    /**
     * @var string From Name to Use to Send Email From
     */
    private $fromName;

    /**
     * @var string Reply-To Email to Use to Send Email From
     */
    private $replyEmail;

    /**
     * @var string Reply-To Name to Use to Send Email From
     */
    private $replyName;
}