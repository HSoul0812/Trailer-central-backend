<?php

namespace App\Services\Auth;

use Hybridauth\Hybridauth;

class AuthService implements AuthServiceInterface
{
    private Hybridauth $hybridauth;

    /**
     * @throws \Hybridauth\Exception\InvalidArgumentException
     */
    public function __construct() {
        $this->hybridauth = new Hybridauth([]);
    }
}
