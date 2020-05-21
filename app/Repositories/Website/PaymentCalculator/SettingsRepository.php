<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Models\Website\PaymentCalculator\Settings;

class SettingsRepository implements SettingsRepositoryInterface {
    
    public function create($params) {
        return Settings::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Settings::where('website_id', $params['website_id'])->firstOrFail();
    }

    public function getAll($params) {
        return Settings::where('website_id', $params['website_id'])->get();
    }

    public function update($params) {
        $settings = Settings::findOrFail($params['id']);
        $settings->fill($params);
        $settings->save();
        return $settings;
    }
    
}
