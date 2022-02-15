<?php

namespace App\Services\CRM\User\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class AuthType
 * 
 * @package App\Services\CRM\User\DTOs
 */
class AuthType
{
    use WithConstructor, WithGetter;

    /**
     * @var string Value of Auth Type Select
     */
    private $index;

    /**
     * @var string Label of Auth Type Select
     */
    private $label;

    /**
     * @var string Basic Method to Handle for Auth Type (oauth|smtp)
     */
    private $method;

    /**
     * @var array Array of Supported SMTP Types
     */
    private $auth;
}