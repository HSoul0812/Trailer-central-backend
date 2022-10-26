<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class EntityType extends Model {

    use TableAware;

    public const TABLE_NAME = 'eav_entity_type';

    public const ENTITY_TYPE_RV = 3;
    public const ENTITY_TYPE_VEHICLE = 4;
    public const ENTITY_TYPE_WATERCRAFT = 5;
    public const ENTITY_TYPE_BUILDING = 10;

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
