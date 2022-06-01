<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use App\Traits\Models\CompositePrimaryKeys;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EntityTypeAttribute
 * @package App\Models\Inventory
 */
class EntityTypeAttribute extends Model
{
    use CompositePrimaryKeys, TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eav_entity_type_attribute';

    protected $primaryKey = ['attribute_id', 'entity_type_id'];

    public $timestamps = false;

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id', 'attribute_id');
    }
}
