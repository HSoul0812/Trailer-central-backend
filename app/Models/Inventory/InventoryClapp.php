<?php

namespace App\Models\Inventory;

use App\Models\Marketing\Craigslist\Queue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Clapp
 * @package App\Models\Inventory
 *
 * @property int $inventory_id,
 * @property int $inventory_clapp_id,
 * @property string $field,
 * @property string $value,
 */
class InventoryClapp extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_clapp';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_clapp_id';

    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'field',
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
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'inventory_id', 'inventory_id');
    }
}
