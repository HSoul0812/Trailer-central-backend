<?php

namespace App\Services\CRM\User\DTOs;

use App\Models\CRM\User\EmailFolder;
use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\User\DTOs\AuthType;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;

/**
 * Class SalesPersonConfig
 * 
 * @package App\Services\CRM\User\DTOs
 */
class SalesPersonConfig
{
    use WithGetter;

    /**
     * @var array Key => Map of SMTP Types Select Dropdown
     */
    private $smtpTypes;

    /**
     * @var Collection<AuthType> Map of Auth Types Select
     */
    private $authTypes;

    /**
     * @var Collection<ImapMailbox> Imap Mailboxes of Default Folders
     */
    private $folders;


    /**
     * Construct With SalesPerson Config Data
     */
    public function __construct() {
        // Fill SMTP Types
        $this->smtpTypes = SalesPerson::CUSTOM_AUTH;

        // Fill Auth Types
        $this->authTypes = $this->fillAuthTypes();

        // Fill Default Folders
        $this->folders = $this->fillDefaultFolders();
    }


    /**
     * Fill Auth Types Into $this->authTypes
     * 
     * @return Collection<AuthType>
     */
    private function fillAuthTypes(): Collection
    {
        // Loop Auth Types
        $authTypes = [];
        foreach(SalesPerson::AUTH_TYPES as $type => $label) {
            // Get Method
            $method = SalesPerson::AUTH_TYPE_METHODS[$type];

            // Get Auth Types
            $auth = [];
            if($type === SalesPerson::AUTH_METHOD_NTLM) {
                $auth = SalesPerson::NTLM_AUTH;
            } elseif($type === SalesPerson::AUTH_METHOD_CUSTOM) {
                $auth = SalesPerson::CUSTOM_AUTH;
            }

            // Append Auth Types
            $authTypes[] = new AuthType([
                'index' => $type,
                'label' => $label,
                'method' => $method,
                'auth' => $auth
            ]);
        }

        // Return Auth Types
        return collect($authTypes);
    }

    /**
     * Fill Default Folders $this->folders
     * 
     * @return Collection<ImapMailbox>
     */
    private function fillDefaultFolders(): Collection
    {
        // Loop Default Folders
        $folders = [];
        foreach(EmailFolder::getDefaultFolders() as $folder) {
            // Append Imap Mailbox
            $folders[] = new ImapMailbox([
                'full' => $folder->name,
                'name' => $folder->name,
                'delimiter' => ImapMailbox::DELIMITER
            ]);
        }

        // Return Folders
        return collect($folders);
    }
}