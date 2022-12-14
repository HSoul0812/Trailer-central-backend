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
 *                            'Call to Action Pop-Up', 'Payment Calculator', 'Showroom Setup'
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

    public $timestamps = false;

    public $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
      'private', 'type', 'label', 'note', 'grouping', 'values', 'values_mapping',
        'default_label', 'default_value', 'sort_order'
    ];

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
     * @return mixed
     */
    public function getValueAccordingWebsite(int $websiteId)
    {
        if(!$this->exists){
            throw new \RuntimeException('`WebsiteConfigDefault::getValueAccordingWebsite` There is not a loaded active record');
        }

        $value = $this->getCurrentValueRepository()->getValueOfConfig($websiteId, $this->key);
        $currentValue = $value ? $value->value : $this->default_value;

        // when it is checkbox type, it should always return a boolean value
        return $this->isCheckBoxType() ? (bool)$currentValue : $currentValue;
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
