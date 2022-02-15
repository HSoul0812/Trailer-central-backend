<?php


namespace App\Repositories\Dms;


use App\Models\CRM\Dms\Settings;
use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository
{
    /**
     * Create or Update DMS Settings
     * 
     * @param array $params
     * @return Settings
     */
    public function createOrUpdate(array $params): Settings;

    /**
     * Get DMS Settings By Dealer ID
     * 
     * @param int $dealerId
     * @return null|Settings
     */
    public function getByDealerId(int $dealerId): ?Settings;
}
