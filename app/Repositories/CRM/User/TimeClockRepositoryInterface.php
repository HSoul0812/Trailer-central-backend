<?php

namespace App\Repositories\CRM\User;

use App\Repositories\Repository;

interface TimeClockRepositoryInterface extends Repository
{
    /**
     * Checks if the user/employee has the clock ticking currently.
     *
     * @param int $userId
     * @return bool
     */
    public function isClockTicking(int $userId): bool;

    /**
     * Starts the clock for given user/employee
     *
     * @param int $userId
     * @return bool
     */
    public function markPunchIn(int $userId): bool;

    /**
     * Stops the clock for given user/employee
     *
     * @param int $userId
     * @return bool
     */
    public function markPunchOut(int $userId): bool;
}
