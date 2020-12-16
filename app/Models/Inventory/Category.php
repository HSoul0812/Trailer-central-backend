<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $label
 */
class Category extends Model {

    use TableAware;

    public const TABLE_NAME = 'inventory_category';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'legacy_category',
        'entity_type_id',
        'category',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_category_id';

    public $timestamps = false;

    public function entityType()
    {
        return $this->hasOne(EntityType::class, 'entity_type_id', 'entity_type_id');
    }

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
