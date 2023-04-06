<?php

namespace App\Services\User;

use App\Models\User\UserAuthenticatable;

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

    public function updatePassword(UserAuthenticatable $user, string $password, string $current_password) : bool;
}
