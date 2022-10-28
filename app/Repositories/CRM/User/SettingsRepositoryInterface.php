<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\Settings;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * Interface SettingsRepositoryInterface
 * 
 * @package App\Repositories\CRM\User
 */
interface SettingsRepositoryInterface extends Repository {
    /**
     * Get All CRM Settings By Dealer
     * 
     * @param int $dealerId
     * @return Collection<Settings>
     */
    public function getByDealer(int $dealerId): Collection;
}
