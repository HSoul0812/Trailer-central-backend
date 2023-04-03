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

    protected $fillable = [
        'type',
        'code',
        'name',
        'values'
    ];

    // ES indexed attributes
    const AXLES = 1;
    const CONSTRUCTION = 2;
    const PULL_TYPE = 3;
    const RAMPS = 4;
    const LIVING_QUARTERS = 5;
    const STALLS = 6;
    const CONFIGURATION = 7;
    const MIDTACK = 8;
    const ROOF_TYPE = 9;
    const NOSE_TYPE = 10;
    const COLOR = 11;
    const SLEEPING_CAPACITY = 12;
    const AIR_CONDITIONERS = 13;
    const FUEL_TYPE = 14;
    const IS_RENTAL = 15;
    const MILEAGE = 16;
    const SLIDEOUTS = 17;
    const MANGER = 18;
    const SHORTWALL_LENGTH = 19; // DW casted
    const NUMBER_BATTERIES = 20;
    const HORSE_POWER = 21;
    const TIRES = 22;
    const PASSENGERS = 23;
    const CONVERSION = 24;
    const CUSTOM_CONVERSION = 25;
    const WEEKLY_PRICE = 30;
    const DAILY_PRICE = 31;
    const FLOORPLAN = 32; // this field seems to be deprecated
    const DRY_WEIGHT = 39;
    const MONTHLY_PRICE = 66;
    const TILT = 73;

    // The following fields are not being considered by the ES worker to be pulled,
    // however they are defined in the index map, so we will index them again
    const CAB_TYPE = 26;
    const ENGINE_SIZE = 27;
    const TRANSMISSION = 28;
    const DRIVE_TRAIL = 29;
    const PROPULSION = 33;
    const DRAFT = 36;
    const TRANSOM = 37;
    const DEAD_RISE = 38;
    const WET_WEIGHT = 40;
    const TOTAL_WEIGHT_CAPACITY = 41;
    const SEATING_CAPACITY = 42;
    const HULL_TYPE = 43;
    const ENGINE_HOURS = 44;
    const INTERIOR_COLOR = 45;
    const HITCH_WEIGHT = 46;
    const CARGO_WEIGHT = 47;
    const FRESH_WATER_CAPACITY = 48;
    const GRAY_WATER_CAPACITY = 49;
    const BLACK_WATER_CAPACITY = 50;
    const FURNACE_BTU = 51;
    const AC_BTU = 52;
    const ELECTRICAL_SERVICE = 53;
    const AVAILABLE_BEDS = 54;
    const NUMBER_AWNINGS = 55;
    const AWNING_SIZE = 56;
    const AXLE_WEIGHT = 57;
    const ENGINE = 58;
    const FUEL_CAPACITY = 59;
    const SIDE_WALL_HEIGHT = 60;
    const EXTERNAL_LINK = 61;
    const SUBTITLE = 62;
    const OVERALL_LENGTH = 63;
    const MIN_WIDTH = 64;
    const MIN_HEIGHT = 65;

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
