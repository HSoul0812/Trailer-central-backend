<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Attribute
 * @package App\Models\Inventory
 */
class Attribute extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eav_attribute';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'attribute_id';

    public function inventory()
    {
        return $this->hasManyThrough(Inventory::class, 'eav_attribute_value', 'inventory_id', 'attribute_id');
    }

    /**
     * @return AttributeValue[]
     */
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id', 'attribute_id');
    }

    /**
     * @return EntityTypeAttribute[]
     */
    public function entityTypeAttributes()
    {
        return $this->hasMany(EntityTypeAttribute::class, 'attribute_id', 'attribute_id');
    }
}
