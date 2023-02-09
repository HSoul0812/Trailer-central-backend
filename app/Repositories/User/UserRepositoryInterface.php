<?php

namespace App\Repositories\User;

use App\Models\User\DealerClapp;
use App\Repositories\Repository;
use App\Models\User\User;
use App\Repositories\TransactionalRepository;

interface UserRepositoryInterface extends Repository, TransactionalRepository {

    /**
     * @param string $email
     * @return App\Models\User\User
     */
    public function getByEmail(string $email) : User;
    /**
     *
     *
     * @param string $email
     * @param string $password unencrypted password
     */
    public function findUserByEmailAndPassword($email, $password);

    /**
     * Returns dealers who have the dms active
     * @return Collection
     */
    public function getDmsActiveUsers();

    /**
     * Get CRM Active Users
     *
     * @param array $params
     * @return Collection of NewDealerUser
     */
    public function getCrmActiveUsers($params);

    public function setAdminPasswd($dealerId, $passwd);

    /**
     * Updates dealer auto import settings
     *
     * @param int $dealerId
     * @param string $defaultDescription
     * @param bool $useDescriptionInFeed
     * @param int $autoImportHide
     * @param string $importConfig
     * @param bool $autoMsrp
     * @param float $autoMsrpPercent
     * @return User
     */
    public function updateAutoImportSettings(int $dealerId, string $defaultDescription, bool $useDescriptionInFeed, int $autoImportHide, string $importConfig, bool $autoMsrp, float $autoMsrpPercent) : User;

    /**
     *
     * @param int $dealerId
     * @param array $params
     * @return array fields and values that were changed
     */
    public function updateOverlaySettings(int $dealerId, array $params) : array;

    /**
     * Check admin password
     *
     * @param int $dealerId
     * @param string $password
     */
    public function checkAdminPassword(int $dealerId, string $password): bool;

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateDealerClassifieds(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateDealerClassifieds(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateDms(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateDms(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return mixed
     */
    public function deactivateDealer(int $dealerId): User;

    /**
     * @param int $dealerId
     * @param string $sourceId
     * @return User
     */
    public function activateCdk(int $dealerId, string $sourceId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateCdk(int $dealerId): User;

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
     * @return DealerClapp
     */
    public function activateMarketing(int $dealerId): DealerClapp;

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
     * @return User
     */
    public function activateQuoteManager(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateQuoteManager(int $dealerId): User;

    /**
     * @param int $dealerId
     * @param string $status
     * @return User
     */
    public function changeStatus(int $dealerId, string $status): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateGoogleFeed(int $dealerId): User;

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateGoogleFeed(int $dealerId): User;
}
