<?php

namespace App\Models\Website\Config;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WebsiteConfigDefault
 * @package App\Models\Website\Config
 */
class WebsiteConfigDefault extends Model
{
    const CONFIG_INCLUDE_ARCHIVING_INVENTORY = 'inventory/include_archived_inventory';

    protected $table = 'website_config_default';


    /**
     * Get JSON-Decoded Values Map
     *
     * @return array{mixed}|null
     */
    public function getValuesMapAttribute(): ?array {
        return json_decode($this->values_mapping, true);
    }
}
