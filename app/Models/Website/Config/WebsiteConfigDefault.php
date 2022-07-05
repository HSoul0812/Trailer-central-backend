<?php

namespace App\Models\Website\Config;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WebsiteConfigDefault
 * @package App\Models\Website\Config
 *
 * @property string $id
 * @property string $key
 * @property int $private
 * @property int $type 'enumerable', 'image', 'int', 'text', 'textarea', 'checkbox', 'enumerable_multiple'
 * @property string $label
 * @property string $note
 * @property string $grouping 'General', 'Home Page Display', 'Inventory Display', 'Contact Forms',
 *                            'Call to Action Pop-Up', 'Payment Calculator'
 * @property string $values
 * @property string $values_mapping
 * @property string $default_label
 * @property string $default_value
 * @property string $sort_order
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
