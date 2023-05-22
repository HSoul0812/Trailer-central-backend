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
        self::TYPE_TRAILER,
        self::TYPE_HOSE_TRAILER,
        self::TYPE_RV,
        self::TYPE_VEHICLE,
        self::ENTITY_TYPE_WATERCRAFT,
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
