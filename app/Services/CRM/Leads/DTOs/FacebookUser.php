<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class InquiryLead
 * 
 * @package App\Services\CRM\Leads\DTOs
 */
class FacebookUser
{
    use WithConstructor, WithGetter;

    /**
     * @var int User ID for Facebook User
     */
    private $userId;

    /**
     * @var int Name of Facebook User
     */
    private $name;

    /**
     * @var int Email Address for Facebook User
     */
    private $email;


    /**
     * Get Inquiry Type
     * 
     * @return string
     */
    public function getFirstName(): string {
        // Split Name
        $name = explode(" ", $this->name);

        // Get First Name
        return array_shift($name);
    }

    /**
     * Get Last Name
     * 
     * @return string
     */
    public function getLastName(): string {
        // Split Name
        $name = explode(" ", $this->name);

        // Remove First Name
        array_shift($name);

        // Get First Name
        return implode(" ", $name);
    }


    /**
     * Return User Params
     * 
     * @return array{user_id: int,
     *               name: string,
     *               email: string}
     */
    public function getParams(): array {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'email' => $this->email
        ];
    }
}