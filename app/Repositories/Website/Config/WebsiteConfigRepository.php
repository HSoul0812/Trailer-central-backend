<?php

namespace App\Repositories\Website\Config;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;
use Illuminate\Support\Facades\DB;

class WebsiteConfigRepository implements WebsiteConfigRepositoryInterface {

      /**
      * WebsiteConfigRepository constructor.
      *
      * @param WebsiteConfig $model
      */
     public function __construct(WebsiteConfig $websiteConfig)
     {
         $this->websiteConfig = $websiteConfig;
     }

    public function create($params) : WebsiteConfig
    {
      $this->websiteConfig->fill($params)->save();

      return $this->websiteConfig;
    }

    public function delete($params) {
      $websiteConfig = $this->websiteConfig->findOrFail($params['id']);

      return (bool)$websiteConfig->delete();
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

        if (isset($params['value_gt'])) {
            $query = $query->where('value', '>', $params['value_gt']);
        }

        if (isset($params['with'])) {
            $query->with($params['with']);
        }

        return $query->get();
    }

    public function update($params)
    {
      $websiteConfig = $this->websiteConfig->find($params['id']);

      DB::transaction(function() use (&$websiteConfig, $params) {
          $websiteConfig->fill($params)->save();
      });

      return $websiteConfig;
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

        // Get Values Mapping Array for Config
        if(!empty($config)) {
            return $this->getValuesMapping($default->values_map, $config->value, $key);
        }

        // Get Values Mapping for Default
        return $this->getValuesMapping($default->values_map, $default->default_value, $key);
    }


    /**
     * @param int $websiteId
     * @param string $key
     * @param string $value
     * @return WebsiteConfig
     */
    public function setValue(int $websiteId, string $key, string $value): WebsiteConfig {
        return WebsiteConfig::updateOrCreate(
            ['website_id' => $websiteId, 'key' => $key],
            ['value' => $value]
        );
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
