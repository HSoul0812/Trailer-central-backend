<?php

namespace App\Models\Inventory;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AttributeValue
 * @package App\Models\Inventory
 *
 * @property int $attribute_id
 * @property int $inventory_id
 * @property string $value
 *
 * @property Attribute $attribute
 * @property Inventory $inventory
 */
class AttributeValue extends Model
{
    use CompositePrimaryKeys;

    public const TABLE_NAME = 'eav_attribute_value';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    protected $primaryKey = ['attribute_id', 'inventory_id'];

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'inventory_id',
        'value',
    ];

    /**
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'attribute_id');
    }

    /**
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
