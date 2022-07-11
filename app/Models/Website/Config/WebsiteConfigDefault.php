<?php

namespace App\Models\Website\Config;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use App;

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
    const CHECKBOX_TYPE = 'checkbox';

    protected $table = 'website_config_default';

    /** @var WebsiteConfigRepositoryInterface */
    private $currentValueRepository;

    /**
     * Get JSON-Decoded Values Map
     *
     * @return array{mixed}|null
     */
    public function getValuesMapAttribute(): ?array {
        return json_decode($this->values_mapping, true);
    }

    public function isCheckBoxType(): bool
    {
        return $this->type === self::CHECKBOX_TYPE;
    }

    /**
     * @param int $websiteId
     * @param WebsiteConfigDefault $config
     * @return mixed
     */
    public function getValueAccordingRulesAndWebsite(int $websiteId, self $config)
    {
        $value = $this->getCurrentValueRepository()->getValueOfConfig($websiteId, $config->key);
        $currentValue = $value ? $value->value : $config->default_value;

        return $config->isCheckBoxType() ? (bool)$currentValue : $currentValue;
    }

    protected function getCurrentValueRepository(): WebsiteConfigRepositoryInterface
    {
        if ($this->currentValueRepository) {
            return $this->currentValueRepository;
        }

        $this->currentValueRepository = App::make(WebsiteConfigRepositoryInterface::class);

        return $this->currentValueRepository;
    }
}
