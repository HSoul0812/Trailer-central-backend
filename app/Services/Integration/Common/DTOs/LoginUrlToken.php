<?php

namespace App\Services\Integration\Common\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class LoginUrlToken
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class LoginUrlToken
{
    use WithConstructor, WithGetter;

    /**
     * @var string Get Login URL
     */
    private $loginUrl;

    /**
     * @var string Get Auth State
     */
    private $authState;
}