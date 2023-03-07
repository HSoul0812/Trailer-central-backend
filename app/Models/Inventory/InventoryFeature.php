<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Feature
 * @package App\Models\Inventory
 *
 * @property int $inventory_feature_id
 * @property int $inventory_id
 * @property int $feature_list_id
 * @property string $value
 *
 * @property Inventory $inventory
 * @property InventoryFeatureList $featureList
 */
class InventoryFeature extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_feature';

    const FLOORPLAN = 10;
    const STALL_TACK = 9;
    const LQ = 8;
    const DOORS_WINDOWS_RAMPS = 7;

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
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * @return BelongsTo
     */
    public function featureList(): BelongsTo
    {
        return $this->belongsTo(InventoryFeatureList::class, 'feature_list_id', 'feature_list_id');
    }
}
