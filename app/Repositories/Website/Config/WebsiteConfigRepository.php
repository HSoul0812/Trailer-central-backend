<?php

namespace App\Repositories\Website\Config;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;

class WebsiteConfigRepository implements WebsiteConfigRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    /**
     * Get All Website Config
     * 
     * @param array $params
     * @return Collection<WebsiteConfig>
     */
    public function getAll($params) {
        $query = WebsiteConfig::select('*');
                
        if (isset($params['website_id'])) {
            $query = $query->where('website_id', $params['website_id']);
        }
                
        if (isset($params['key'])) {
            $query = $query->where('key', $params['key']);
        }
        
        if (isset($params['value'])) {
            $query = $query->where('value', $params['value']);
        }
        
        return $query->get();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Value of Key For Website or Default
     * 
     * @param int $websiteId
     * @param string $key
     * @return array{key: value} or array{json_decode(values_mapping)}
     */
    public function getValueOrDefault(int $websiteId, string $key): array {
        // Get Config
        $config = WebsiteConfig::where('website_id', $websiteId)->where('key', $key)->first();
        $default = WebsiteConfigDefault::where('key', $key)->first();

        // Return Values Mapping
        if(!empty($config)) {
            // Get Values Mapping Array for Config
            $value = $config->value('value');
            var_dump($value);
            return $this->getValuesMapping($default->values_map, $value, $key);
        }

        // Get Values Mapping for Default
        return $this->getValuesMapping($default->values_map, $default->default_value, $key);
    }


    /**
     * Get Values Mapping
     * 
     * @param array $values
     * @param string $value
     * @param string $key
     * @return array{key: value} or array{json_decode(values_mapping)}
     */
    private function getValuesMapping($values, $value, $key): array {
        // Check Values Map
        if(!empty($values[$value])) {
            return $values[$value];
        }

        // Return Standard Map Instead
        return [$key => $value];
    }
}
