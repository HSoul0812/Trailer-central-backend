<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryUpdate
 * @package App\Models\Inventory
 */
class InventoryUpdate extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_update';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_update_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'dealer_id',
        'stock',
        'location_id',
        'action',
        'specific_action',
        'time_entered',
        'processed',
    ];

    protected $casts = [
        'inventory_id' => 'integer',
        'dealer_id' => 'integer',
        'stock' => 'string',
        'location_id' => 'integer',
        'action' => 'string',
        'specific_action' => 'string',
        'time_entered' => 'integer',
        'processed' => 'boolean',
    ];
}
