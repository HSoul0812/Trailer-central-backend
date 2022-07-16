<?php

namespace App\Repositories\Website\Config;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Get All Website Config Call to Action
     *
     * @param int $websiteId
     * @return \Illuminate\Database\Eloquent\Collection|array<WebsiteConfig>
     */
    public function getAllCallToAction(int $websiteId) : collection
    {
      $query = WebsiteConfig::select('*');

      $query = $query->where('website_id', $websiteId);

      $query->where('key', 'LIKE', '%'.WebsiteConfig::CALL_TO_ACTION.'%');

      return $query->get();
    }

    /**
     * Create or Update on bulk
     *
     * @param array $websiteId
     * @return array<WebsiteConfig>
     */
    public function createOrUpdate(int $websiteId, array $request) : array
    {
      $webisteConfigs = [];

      foreach ($request as $websiteConfigDataKey => $websiteConfigDataValue) {

        $websiteConfig = WebsiteConfig::updateOrCreate([
            'website_id'=> $websiteId,
            'key'=> $websiteConfigDataKey
        ],[
          'website_id' => $websiteId,
          'key' => $websiteConfigDataKey,
          'value' => $websiteConfigDataValue
        ]);

        $webisteConfigs[] = $websiteConfig;

      }
      return $webisteConfigs;
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
     * @return null|WebsiteConfig
     */
    public function getValueOfConfig(int $websiteId, string $key): ?WebsiteConfig {
        return WebsiteConfig::select('value')->where('website_id', $websiteId)->where('key', $key)->first();
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

    /**
     * @deprecated This should be removed due we have an extra website config API which should handle any non-regular
     *             website variable e.g. call to action and showroom, both of them are regular website variables, so they
     *             should be ALWAYS handled by regular website variables API
     *
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function createOrUpdateShowroomConfig(array $params): array
    {
        try {
            $this->beginTransaction();

            $includeShowRoom = isset($params['include_showroom']) ? 1 : 0;

            if (isset($params['showroom_dealers'])) {
                $dealersArr = array();

                if (is_array($params['showroom_dealers'])) {
                    foreach ($params['showroom_dealers'] as $dealer) {
                        $dealersArr[] = $dealer;
                    }
                } else {
                    $dealersArr[0] = $params['showroom_dealers'];
                }

                $dealers = serialize($dealersArr);
                DB::statement("UPDATE dealer SET `showroom` = '" . $includeShowRoom . "', `showroom_dealers` = '" . $dealers . "' WHERE dealer_id = " . $params['dealer_id']);
            }

            if (isset($params['use_series'])) {
                $checkValue = $this->getValueOfConfig($params['websiteId'], WebsiteConfig::SHOWROOM_USE_SERIES);

                if (!$checkValue) {
                    DB::statement("INSERT INTO website_config (website_id, `key`, `value`) VALUES (" . $params['websiteId'] . ", '" . WebsiteConfig::SHOWROOM_USE_SERIES . "', '" . $params['use_series'] . "')");
                } else {
                    DB::statement("UPDATE website_config SET `value` = " . $params['use_series'] . " WHERE website_id = " . $params['websiteId'] . " AND `key` = '".WebsiteConfig::SHOWROOM_USE_SERIES."'");
                }
            }

            if ($includeShowRoom) {
                // just clear the showroom page to be safe
                DB::statement("DELETE FROM website_entity WHERE website_id='" . $params['websiteId'] . "' AND entity_type = 9");

                // if including showroom, just add the webpage entity
                if (isset($_POST["include_showroom"])) {
                    DB::statement("INSERT INTO website_entity (entity_type, website_id, parent, title, url_path, date_created, date_modified, sort_order, in_nav, is_active, template) VALUES (9, ".$params['websiteId'].", 0, 'Showroom', 'showroom', now(), now(), 99, 1, 1, '1column')");
                }
            }

            $this->commitTransaction();

            return $params;
        } catch (\Exception $exception) {
            $this->rollbackTransaction();
            throw $exception;
        }
    }
}
