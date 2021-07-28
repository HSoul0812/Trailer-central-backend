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
     * @var string Email Address Approved For
     */
    private $emailAddress;


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
}