<?php

namespace App\Services\CRM\User\DTOs;

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
     * @const Default Method Type
     */
    const METHOD_DEFAULT = 'smtp';

    /**
     * @const OAuth Method Type
     */
    const METHOD_OAUTH = 'oauth';

    /**
     * @const Default Config Type
     */
    const CONFIG_DEFAULT = 'default';


    /**
     * @var int Dealer ID of Current User
     */
    private $dealerId;

    /**
     * @var int Sales Person ID of Current User
     */
    private $salesPersonId = null;

    /**
     * @var string Type of Email Config Settings
     */
    private $type = 'dealer'; // dealer | sales_person

    /**
     * @var string Method Used for Email Settings
     */
    private $method; // smtp | oauth

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


    /**
     * Get Reply Array
     * 
     * @return null|array{name: string,
     *                    email: string}
     */
    public function getReply(): ?array {
        // Return Null Instead
        if($this->replyEmail === null) {
            return null;
        }

        // Return Array
        return [
            'email' => $this->replyEmail,
            'name' => $this->replyName
        ];
    }
}