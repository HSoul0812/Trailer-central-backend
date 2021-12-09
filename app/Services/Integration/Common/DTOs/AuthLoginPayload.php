<?php

namespace App\Services\Integration\Common\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class LoginUrlToken
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class AuthLoginPayload
{
    use WithConstructor, WithGetter;

    /**
     * @var string Get Token Type
     */
    private $tokenType;

    /**
     * @var string Get Relation Type
     */
    private $relationType;

    /**
     * @var int Get Relation ID
     */
    private $relationId = 0;

    /**
     * @var string Get Redirect URI
     */
    private $redirectUri = '';

    /**
     * @var array Get Scopes
     */
    private $scopes = [];
}