<?php

namespace App\Repositories\User;

use App\Models\User\DealerUser;
use App\Repositories\Repository;
use App\Models\User\User;
use App\Models\User\DealerPasswordReset;

interface DealerPasswordResetRepositoryInterface extends Repository {

    /**
     * Initiates a password reset request
     *
     * @param App\Models\User $dealer
     * @return App\Models\User\DealerPasswordReset
     */
    public function initiatePasswordReset(User $dealer) : DealerPasswordReset;

    /**
     * Completes a password reset request
     *
     * @param string $code
     * @param string $password
     * @return bool
     */
    public function completePasswordReset(string $code, string $password) : bool;

    /**
     *
     * @param string $code
     * @return App\Models\User\DealerPasswordReset
     */
    public function getByCode(string $code) : DealerPasswordReset;

    /**
     *
     * @param User $dealer
     * @param string $password
     * @return void
     */
    public function updateDealerPassword(User $dealer, string $password) : void;

    /**
     * 
     * @param DealerUser $user
     * @param string $password
     * @return void
     */
    public function updateDealerUserPassword(DealerUser $user, string $password) : void;
}
