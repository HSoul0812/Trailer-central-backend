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

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateECommerce(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateUserAccounts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateECommerce(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateUserAccounts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDealer(int $dealerId): bool;

}
