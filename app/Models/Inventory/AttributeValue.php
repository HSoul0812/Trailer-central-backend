<?php


namespace App\Models\Inventory;

use App\Traits\Models\CompositePrimaryKeys;
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

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'attribute_id');
    }
}
