<?php

namespace App\Repositories\User;

use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository {
    /**
     * Find Dealer Location By Various Options
     * 
     * @param array $params
     * @return Collection<Settings>
     */
    public function createOrUpdate($params);
}
