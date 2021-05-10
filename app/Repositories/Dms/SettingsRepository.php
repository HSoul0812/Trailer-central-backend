<?php

namespace App\Repositories\Dms;

use App\Models\CRM\Dms\Settings;
use App\Repositories\RepositoryAbstract;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\DB;

class SettingsRepository extends RepositoryAbstract implements SettingsRepositoryInterface
{
    /**
     * Create Email History
     * 
     * @param array $params
     * @return EmailHistory
     */
    public function create($params) {
        // Initialize Settings
        $settings = new Settings();

        // Fill Settings
        $settings->fillWithMeta($params);

        // Save Settings After Filling With Meta
        $settings->save();

        // Return Settings
        return $settings;
    }

    /**
     * Delete DMS Settings
     * 
     * @param array $params
     * @throws NotImplementedException
     */
    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * Get DMS Settings
     * 
     * @param array $params
     * @throws NotImplementedException
     */
    public function get($params) {
        throw new NotImplementedException;
    }

    /**
     * Update DMS Settings
     * 
     * @param array $params
     * @return Settings
     */
    public function update($params) {
        $settings = Settings::findOrFail($params['id']);

        DB::transaction(function() use (&$settings, $params) {
            // Fill Settings Details
            $settings->fillWithMeta($params);

            // Save Settings After Filling With Meta
            $settings->save();
        });

        return $settings;
    }

    /**
     * Create or Update DMS Settings
     * 
     * @param array $params
     * @return Settings
     */
    public function createOrUpdate(array $params): Settings {
        // Get By Dealer ID
        $settings = $this->getByDealerId($params['dealer_id']);
        if(!empty($settings->id)) {
            // Update DMS Settings
            $params['id'] = $settings->id;
            return $this->update($params);
        }

        // Create DMS Settings
        return $this->create($params);
    }

    /**
     * Get DMS Settings By Dealer ID
     * 
     * @param int $dealerId
     * @return null|Settings
     */
    public function getByDealerId(int $dealerId): ?Settings
    {
        return Settings::where('dealer_id', $dealerId)->first();
    }
}
