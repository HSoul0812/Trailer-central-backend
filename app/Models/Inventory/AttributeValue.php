<?php

namespace App\Models\Inventory;

use App\Models\Traits\Inventory\CompositePrimaryKeys;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttributeValue
 * @package App\Models\Inventory
 */
class AttributeValue extends Model
{
    use CompositePrimaryKeys;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eav_attribute_value';

    protected $primaryKey = ['attribute_id', 'inventory_id'];

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'inventory_id',
        'value',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'attribute_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
