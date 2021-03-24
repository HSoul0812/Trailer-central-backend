<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Feature
 * @package App\Models\Inventory
 */
class InventoryFeature extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_feature';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_feature_id';

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'feature_list_id',
        'value',
    ];

    /**
     * @return BelongsTo
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
