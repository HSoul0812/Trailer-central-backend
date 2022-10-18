<?php

namespace App\Repositories\CRM\User;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\Settings;
use App\Models\User\NewDealerUser;
use Illuminate\Support\Collection;

/**
 * Class SettingsRepository
 * 
 * @package App\Repositories\CRM\User
 */
class SettingsRepository implements SettingsRepositoryInterface
{
    /**
     * @param array $params
     * 
     * @return Settings
     */
    public function create($params): Settings
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return Settings
     */
    public function update($params): Settings
    {
        throw new NotImplementedException;
    }

    /**
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Get All CRM Settings
     * 
     * array $params
     * return Collection<Settings>
     */
    public function getAll($params)
    {
        // Initialize Query
        $query = Settings::where(Settings::getTableName() . '.user_id', '>', 0);

        // Find By User ID?
        if (isset($params['user_id'])) {
            $query = $query->where(Settings::getTableName() . '.user_id', $params['user_id']);
        }

        // Find By Dealer ID?
        if (isset($params['dealer_id'])) {
            $newDealerUser = NewDealerUser::findOrFail($params['dealer_id']);
            $query = $query->where('user_id', $newDealerUser->user_id);
        }

        // Return CRM Settings Collection
        return $query->get();
    }

    /**
     * Get All CRM Settings By Dealer
     * 
     * @param int $dealerId
     * @return Collection<Settings>
     */
    public function getByDealer(int $dealerId): Collection {
        // Settings
        $settings = $this->getAll(['dealer_id' => $dealerId]);

        // Get Formatted Settings
        $map = [];
        foreach($settings as $setting) {
            $map[$setting->key] = $setting->value;
        }

        // Return New Collection
        return new Collection($map);
    }
}
