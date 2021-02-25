<?php

namespace App\Repositories\User;

use App\Models\User\Settings;
use App\Repositories\User\SettingsRepositoryInterface;
use App\Exceptions\NotImplementedException;

class SettingsRepository implements SettingsRepositoryInterface {
    
    public function create($params) {
        return Settings::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Settings::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Settings::where('dealer_id', $params['dealer_id']);

        if (isset($params['setting'])) {
            $query->where('setting', $params['setting']);
        }

        return $query->get();
    }

    public function update($params) {
        var_dump($params);
        $settings = Settings::findOrFail($params['id']);

        DB::transaction(function() use (&$settings, $params) {
            // Fill Settings Details
            $settings->fill($params)->save();
        });

        return $settings;
    }

    /**
     * Find Setting By Dealer ID and Setting or By ID
     * 
     * @param array $params
     * @return Settings
     */
    public function find($params) {
        // Dealer ID and Setting Exists?
        if(isset($params['dealer_id']) && isset($params['setting'])) {
            return Settings::where('dealer_id', $params['dealer_id'])
                           ->where('setting', $params['setting'])->first();
        }

        // Return Normal
        return Settings::find($params['id']);
    }

    /**
     * Create Or Update Multiple Settings
     * 
     * @param array $params
     * @return Collection<DealerLocation>
     */
    public function createOrUpdate($params)
    {
        // Initialize Settings
        $settings = [];

        // Loop Settings
        if(isset($params['settings'])) {
            foreach($params['settings'] as $set) {
                // Find Setting
                $setting = $this->find(['dealer_id' => $params['dealer_id'], 'setting' => $set['setting']]);

                // Create Settings Array
                $updates = [
                    'dealer_id' => $params['dealer_id'],
                    'setting' => $set['setting'],
                    'setting_value' => $set['value']
                ];

                // Update Existing
                if(!empty($setting->id)) {
                    $updates['id'] = $setting->id;
                    var_dump($updates);
                    $settings[] = $this->update($updates);
                    continue;
                }

                // Create New
                $settings[] = $this->create($updates);
            }
        }

        // Return Collection
        return collect($settings);
    }
}