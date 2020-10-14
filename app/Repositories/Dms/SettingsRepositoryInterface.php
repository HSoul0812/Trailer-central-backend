<?php


namespace App\Repositories\Dms;


use App\Models\CRM\Dms\Settings;
use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository
{
    /** @return Settings */
    public function getByDealerId($dealerId);
}
