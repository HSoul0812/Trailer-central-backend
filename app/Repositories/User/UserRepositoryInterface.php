<?php

namespace App\Repositories\User;

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
     * @param bool $overlayEnabled
     * @param bool $overlay_default
     * @param string $overlay_logo_position
     * @param int $overlay_logo_width
     * @param int $overlay_logo_height
     * @param string $overlay_upper
     * @param string $overlay_upper_bg
     * @param int $overlay_upper_alpha
     * @param string $overlay_upper_text
     * @param int $overlay_upper_size
     * @param int $overlay_upper_margin
     * @param string $overlay_lower
     * @param string $overlay_lower_bg
     * @param int $overlay_lower_alpha
     * @param string $overlay_lower_text
     * @param int $overlay_lower_size
     * @param int $overlay_lower_margin
     * @param string $overlay_logo_src
     * @return User
     */
    public function updateOverlaySettings(int $dealerId,
                                          int $overlayEnabled = null,
                                          bool $overlay_default = null,
                                          string $overlay_logo_position = null,
                                          string $overlay_logo_width = null,
                                          string $overlay_logo_height = null,
                                          string $overlay_upper = null,
                                          string $overlay_upper_bg = null,
                                          int $overlay_upper_alpha = null,
                                          string $overlay_upper_text = null,
                                          int $overlay_upper_size = null,
                                          int $overlay_upper_margin = null,
                                          string $overlay_lower = null,
                                          string $overlay_lower_bg = null,
                                          int $overlay_lower_alpha = null,
                                          string $overlay_lower_text = null,
                                          int $overlay_lower_size = null,
                                          int $overlay_lower_margin = null,
                                          string $overlay_logo_src = null) : User;

    /**
     * Check admin password
     *
     * @param int $dealerId
     * @param string $password
     */
    public function checkAdminPassword(int $dealerId, string $password): bool;

    /**
     * @param int $dealerId
     * @return mixed
     */
    public function deactivateDealer(int $dealerId): User;
}
