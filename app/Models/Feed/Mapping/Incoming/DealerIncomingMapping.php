<?php

namespace App\Models\Feed\Mapping\Incoming;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DealerIncomingMapping
 * @package App\Models\Feed\Mapping\Incoming
 *
 * @property int $id
 * @property string $map_from
 * @property string $map_to
 * @property string $type
 * @property string $integration_name
 * @property int $dealer_id
 */
class DealerIncomingMapping extends Model {

    protected $table = 'dealer_incoming_mappings';

    public $timestamps = false;

    const MAKE = 'manufacturer';
    const CATEGORY = 'category';
    const ENTITY_TYPE = 'entity_type';
    const CONDITION = 'condition';
    const STATUS = 'status';
    const PULL_TYPE = 'pull_type';
    const NOSE_TYPE = 'nose_type';
    const CONSTRUCTION = 'construction';
    const FUEL_TYPE = 'fuel_type';
    const COLOR = 'color';
    const BRAND = 'brand';
    const MANUFACTURER_BRAND = 'manufacturer_brand';
    const LOCATION = 'dealer_location';
    const TRANSMISSION = 'transmission';
    const DRIVE_TRAIL = 'drive_trail';
    const ENGINE_SIZE = 'engine_size';
    const FIELDS = 'fields';
    const DEFAULT_VALUES = 'default_values';
    const DOORS = 'doors';
    const BODY = 'body';
    const TRANSMISSION_SPEED = 'transmission_speed';
    const SERIES = 'series';
    const CITY_MPG = 'city_mpg';
    const HIGHWAY_MPG = 'highway_mpg';
    const PROPULSION = 'propulsion';

    public static $types = [
        self::MAKE => 'Manufacturer',
        self::MANUFACTURER_BRAND => 'Manufacturer And Brand',
        self::CATEGORY => 'Category',
        self::ENTITY_TYPE => 'Entity Type',
        self::CONDITION => 'Condition',
        self::STATUS => 'Status',
        self::PULL_TYPE => 'Pull Type',
        self::NOSE_TYPE => 'Nose Type',
        self::CONSTRUCTION => 'Construction',
        self::FUEL_TYPE => 'Fuel Type',
        self::COLOR => 'Color',
        self::BRAND => 'Brand',
        self::LOCATION => 'Dealer Location',
        self::TRANSMISSION => 'Transmission',
        self::DRIVE_TRAIL => 'Drive Trail',
        self::ENGINE_SIZE => 'Engine Size',
        self::FIELDS => 'Fields',
        self::DEFAULT_VALUES => 'Default Values',
        self::DOORS => 'Doors',
        self::BODY => 'Body',
        self::TRANSMISSION_SPEED => 'Transmission Speed',
        self::SERIES => 'Series',
        self::CITY_MPG => 'City MPG',
        self::HIGHWAY_MPG => 'Highway MPG',
        self::PROPULSION => 'Propulsion',
    ];

    const PJ_INTEGRATION_NAME = 'pj';
    const UTC_INTEGRATION_NAME = 'utc';

    const INTEGRATION_NAMES = [
        self::PJ_INTEGRATION_NAME,
        self::UTC_INTEGRATION_NAME
    ];

    protected $fillable = [
        'dealer_id',
        'map_from',
        'map_to',
        'type',
        'integration_name'
    ];

    public function dealers()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
}
