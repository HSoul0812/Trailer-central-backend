<?php

namespace App\Services\User;

use Exception;

/**
 * Interface DealerOptionsServiceInterface
 *
 * @package App\Services\User
 */
interface DealerOptionsServiceInterface
{
    /**
     * Activate/Deactivate Dealer subscriptions
     * @param int $dealerId
     * @param object $fields
     * @return bool
     * @throws Exception
     */
    public function manageDealerSubscription(int $dealerId, object $fields): bool;

    /**
     * Activate/Deactivate Dealer hidden integrations
     * @param int $dealerId
     * @param int $integrationId
     * @param bool $active
     * @return bool
     */
    public function manageHiddenIntegration(int $dealerId, int $integrationId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer CDK
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageCdk(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer CRM
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageCrm(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer E-Commerce
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageEcommerce(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer Marketing
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageMarketing(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer Mobile site
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageMobile(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer Parts
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageParts(int $dealerId, bool $active): bool;

    /**
     * Activate/Deactivate Dealer user accounts
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function manageUserAccounts(int $dealerId, bool $active): bool;

    /**
     * @param int $dealerId
     * @return bool
     * @throws Exception
     */
    public function deactivateDealer(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function isAllowedParts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @param string $status
     * @return bool
     * @throws Exception
     */
    public function changeStatus(int $dealerId, string $status): bool;
}
