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
        $query = Settings::where('website_id', $params['website_id']);
        
        if (isset($params['inventory_price'])) {
            $query->where(function($q) use ($params) {
                $q->where(function($q) use ($params) {
                    $q->where('operator', Settings::OPERATOR_LESS_THAN)
                            ->where('inventory_price', '>', $params['inventory_price']);
                })->orWhere(function($q) use ($params) {
                    $q->where('operator', Settings::OPERATOR_OVER)
                            ->where('inventory_price', '<', $params['inventory_price']);
                });
            });
        }
        
        if (isset($params['entity_type_id'])) {
            $query->where('entity_type_id', $params['entity_type_id']);
        }
        
        if (isset($params['inventory_condition'])) {
            $query->where('inventory_condition', $params['inventory_condition']);
        }
        
        if (isset($params['financing'])) {
            $query->where('financing', $params['financing']);
        }
        
        return $query->get();
    }

    public function update($params) {
        $settings = Settings::findOrFail($params['id']);
        $settings->fill($params);
        $settings->save();
        return $settings;
    }
    
}
