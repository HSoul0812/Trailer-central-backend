<?php

namespace App\Services\User;

/**
 * Interface DealerOptionsServiceInterface
 * @package App\Services\User
 */
interface DealerOptionsServiceInterface
{
    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateCrm(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCrm(int $dealerId): bool;
}
