<?php

namespace App\Repositories\User;

use App\Models\User\User;
use App\Models\User\DealerUser;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\TransactionalRepository;

/**
 * interface UserRepositoryInterface
 *
 * @package App\Repositories\User
 */
interface UserRepositoryInterface extends Repository, TransactionalRepository {

    /**
     * @param string $email
     * @return User
     */
    public function getByEmail(string $email) : User;

    /**
     * @param string $email
     * @param string $password
     * @return User|DealerUser
     *
     * @throws ModelNotFoundException when a dealer or user-belonging-to-a-dealer is not found
     */
    public function findUserByEmailAndPassword(string $email, string $password);

    /**
     * Returns dealers who have the dms active
     * @return Collection
     */
    public function getDmsActiveUsers(): Collection;

    /**
     * Get CRM Active Users
     *
     * @param array $params
     * @return Collection of NewDealerUser
     */
    public function getCrmActiveUsers(array $params): Collection;

    /**
     * @param $dealerId
     * @param $passwd
     * @return mixed
     */
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
     *      Use sha1 encryption algorithm to compare admin password
     * @param int $dealerId
     * @param string $password
     */
    public function checkAdminPassword(int $dealerId, string $password): bool;

    /**
     * @param int $dealerId
     * @return mixed
     */
    public function deactivateDealer(int $dealerId): User;

    /**
     * @param int $dealerId
     * @param string $status
     * @return User
     */
    public function changeStatus(int $dealerId, string $status): User;

    /**
     * @param string $name
     * @return Collection
     */
    public function getByName(string $name): Collection;
}
