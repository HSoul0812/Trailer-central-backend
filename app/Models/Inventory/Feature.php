<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Feature
 * @package App\Models\Inventory
 */
class Feature extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_feature';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_feature_id';

    /**
     * @return Inventory
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
