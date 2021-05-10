<?php

namespace App\Repositories\User;

use App\Models\User\Settings;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface SettingsRepositoryInterface extends Repository {
    /**
     * Find Setting By Dealer ID and Setting or By ID
     * 
     * @param array $params
     * @return null|Settings
     */
    public function find(array $params): ?Settings;

    /**
     * Create Or Update Multiple Settings
     * 
     * @param array $params
     * @return Collection<Settings>
     */
    public function createOrUpdate(array $params): Collection;
}
