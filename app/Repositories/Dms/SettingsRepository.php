<?php


namespace App\Repositories\Dms;


use App\Models\CRM\Dms\Settings;
use App\Repositories\RepositoryAbstract;

class SettingsRepository extends RepositoryAbstract implements SettingsRepositoryInterface
{
    public function getByDealerId($dealerId)
    {
        return Settings::where('dealer_id', $dealerId)->get()->first();
    }
}
