<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $entity_type_id
 * @property string $name
 * @property string $title
 * @property string $title_lowercase
 * @property string $sort_order
 */
class EntityType extends Model {

    use TableAware;

    public const TABLE_NAME = 'eav_entity_type';

    const TYPE_TRAILER = 1;
    const TYPE_HOSE_TRAILER = 2;
    const TYPE_RV = 3;
    const TYPE_VEHICLE = 4;
    const TYPE_WATERCRAFT = 5;
    const TYPE_EQUIPMENT = 6;
    const TYPE_SEMI_TRAILER = 7;
    const TYPE_SPORTS_VEHICLE = 8;

    const ENTITY_TYPE_LABELS = [
        self::TYPE_TRAILER => 'Trailer',
        self::TYPE_HOSE_TRAILER => 'Horse Trailer',
        self::TYPE_RV => 'RV',
        self::TYPE_VEHICLE => 'Vehicle',
        self::TYPE_WATERCRAFT => 'Watercraft',
        self::TYPE_EQUIPMENT => 'Equipment',
        self::TYPE_SEMI_TRAILER => 'Semi-Trailer',
        self::TYPE_SPORTS_VEHICLE => 'Sports Vehicle',
    ];

    public const TRAILER_TRADER_TYPES = [
        self::TYPE_TRAILER => [
            'tow_dolly',
            'equipment',
            'flatbed',
            'landscape',
            'utility',
            'multisport',
            'cargo_enclosed',
            'motorcycle',
            'atv',
            'watercraft',
            'boat_trailer',
            'personal_watercraft',
            'snowmobile',
            'dump',
            'dump_bin',
            'rollster',
            'car_racing',
            'deckover',
            'stacker',
            'trailer.car_hauler',
            'car_hauler',
            'vending_concession',
            'bbq',
            'office',
            'fiber_splicing',
            'contractor',
            'restroom_shower',
            'other',
            'tank_trailer',
            'trailer_fuel',
            'ice-fish_house',
            'ice_shack',
            'Pressure_Washer',
            'refrigerated',
            'specialty',
            'pipe',
            'trash',
            'semi_tilt',
            'trailer_tilt',
        ],
        self::TYPE_HOSE_TRAILER => [
            'horse',
            'stock_stock-combo',
            'stock',
            'hay',
        ],
        self::TYPE_RV => [
            'toy',
            'tiny_house',
            'camping_rv',
            'tent-camper',
            'camper_popup',
            'destination_trailer',
            'expandable',
            'camper_aframe',
            'fish_house',
            'rv_other',
            'park_model',
            'camper_teardrop',
            'class_a',
            'offroad',
            'class_b',
            'class_bplus',
            'class_c',
            'fifth_wheel_campers',
        ],
        self::TYPE_VEHICLE => [
            'semi_flatbed',
            'semi_highboy',
            'semitruck_standard',
            'semi_belt',
            'semi_grain-hopper',
            'semi_hopper_trailers',
            'semi_reefer',
            'semi_horse',
            'semi_livestock',
            'semi_bulk',
            'semi_tanker',
            'semi_dump',
            'semitruck_tanker_truck',
            'semitruck_flatbed_truck',
            'semitruck_dump_truck',
            'semitruck_other',
            'semitruck_offroad',
            'semitruck_highway',
            'semitruck_heavy',
            'semi_btrain',
            'semi_dolley',
            'semi_livefloor',
            'semi_log',
            'semi_other',
            'semi_detach',
            'semi_double',
            'semi_lowboy',
            'semi_drop',
            'semi_container',
            'semi_curtainside',
            'semi_drop-van',
            'semi_dryvan',
        ],
        self::ENTITY_TYPE_WATERCRAFT => [
            'rv.truck_camper',
            'truck_camper',
            'bed_equipment',
            'truck_bodies',
            'truck_boxes',
            'dump_insert',
            'kuv_bodies',
            'service_bodies',
            'platform_bodies',
            'gooseneck_bodies',
            'saw_bodies',
            'truck_cap',
            'van_bodies',
        ],
    ];

    public const ENTITY_TYPE_BUILDING = 10;

    public const ENTITY_TYPE_RV = 3;
    public const ENTITY_TYPE_VEHICLE = 4;
    public const ENTITY_TYPE_WATERCRAFT = 5;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'entity_type_id';

    public $timestamps = false;

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
