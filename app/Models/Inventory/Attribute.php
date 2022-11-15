<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Attribute
 * @package App\Models\Inventory
 *
 * @property int $attribute_id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $values
 * @property string $extra_values
 * @property string $description
 * @property string $default_value
 * @property string $aliases
 */
class Attribute extends Model
{
    use TableAware;

    private const TYPE_SELECT = 'select';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eav_attribute';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'attribute_id';

    public $timestamps = false;

    /**
     * @return HasManyThrough
     */
    public function inventory(): HasManyThrough
    {
        return $this->hasManyThrough(Inventory::class, 'eav_attribute_value', 'inventory_id', 'attribute_id');
    }

    /**
     * @return HasMany
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id', 'attribute_id');
    }

    /**
     * @return HasMany
     */
    public function entityTypeAttributes(): HasMany
    {
        return $this->hasMany(EntityTypeAttribute::class, 'attribute_id', 'attribute_id');
    }

    /**
     * @return array
     */
    public function getValuesArray(): array
    {
        $values = explode(',', $this->values);

        $array = [];
        foreach ($values as $value) {
            $value = explode(':', $value);
            if (isset($value[1]) && isset($value[0])) {
                $array[$value[0]] = $value[1];
            }
        }

        return $array;
    }

    public function isSelect(): bool
    {
        return $this->type === self::TYPE_SELECT;
    }
}
