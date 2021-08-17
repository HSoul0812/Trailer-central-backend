<?php

namespace App\Services\Integration\Common\DTOs;

use App\Services\Integration\Common\DTOs\CommonToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class EmailToken
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class EmailToken extends CommonToken
{
    use WithConstructor, WithGetter;

    /**
     * @var string First Name From Profile
     */
    private $firstName;

    /**
     * @var string Last Name From Profile
     */
    private $lastName;

    /**
     * @var string Email Address Approved For
     */
    private $emailAddress;

    /**
     * @var Collection<ImapMailbox>
     */
    private $folders;


    /**
     * Return Email Address
     * 
     * @return string $this->emailAddress
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * Set Email Address
     * 
     * @param string $emailAddress
     * @return void
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }


    /**
     * Convert to Array for SalesPerson + AccessToken
     * 
     * @param null|int $id
     * @param null|string $tokenType
     * @param null|string $relationType
     * @param null|int $relationId
     * @param null|string $state
     * @return array{access_token: string,
     *               refresh_token: string,
     *               id_token: string,
     *               expires_in: int,
     *               expires_at: string,
     *               issued_at: string,
     *               scopes: array,
     *               first_name: string,
     *               last_name: string,
     *               email_address: string,
     *               ?id: null|int,
     *               ?token_type: null|string,
     *               ?relation_type: null|string,
     *               ?relation_id: null|int,
     *               ?state: null|string}
     */
    public function toArray(?int $id = null, ?string $tokenType = null, ?string $relationType = null,
            ?int $relationId = null, ?string $state = null): array {
        // Initialize Array
        $array = parent::toArray($id, $tokenType, $relationType, $relationId, $state);

        // Return Merged Array With Name and Email
        return array_merge($array, [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email_address' => $this->emailAddress,
        ]);
    }


    /**
     * Get Default Folders Using $this->folders
     * 
     * @return array<array{name: string}>
     */
    public function getDefaultFolders(): array
    {
        // Loop Folders
        $mailboxes = [];
        if($this->folders) {
            foreach($this->folders as $folder) {
                if(preg_match(ImapMailbox::DEFAULT_FOLDER_REGEX, $folder->name)) {
                    $mailboxes[] = ['name' => $folder->name];
                }
            }
        }

        // Return Folders
        return $mailboxes;
    }
}