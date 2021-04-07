<?php

namespace App\Services\User;

interface PasswordResetServiceInterface {
    
    /**
     * @param string $email
     * @return bool
     */
    public function initReset(string $email) : bool;
    
    /**
     * @param string $code
     * @param string $password
     * @return bool
     */
    public function finishReset(string $code, string $password) : bool;
}
