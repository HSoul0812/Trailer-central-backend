<?php

namespace App\Services\User;

use App\Models\User\NewDealerUser;
use App\Models\User\User;

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
     * @param string $sourceId
     * @return bool
     */
    public function activateCdk(int $dealerId, string $sourceId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCdk(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDealer(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDealerClassifieds(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDealerClassifieds(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDms(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDms(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateECommerce(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateECommerce(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateMarketing(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMarketing(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateMobile(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMobile(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateScheduler(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateScheduler(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateELeads(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateELeads(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateAuction123(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateAuction123(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateAutoConx(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateAutoConx(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateCarBase(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCarBase(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDP360(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDP360(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateTrailerUSA(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateTrailerUSA(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateParts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateParts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateQuoteManager(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateQuoteManager(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateUserAccounts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateUserAccounts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function isAllowedParts(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @param string $status
     * @return bool
     */
    public function changeStatus(int $dealerId, string $status): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateGoogleFeed(int $dealerId): bool;

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateGoogleFeed(int $dealerId): bool;
}
