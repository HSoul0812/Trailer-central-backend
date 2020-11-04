<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryImage extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_image';

    public $timestamps = false;

    protected $fillable = [
        'image_id',
        'inventory_id',
        'is_default',
        'is_secondary',
        'position',
        'showroom_image',
        'was_manually_added',
        'is_stock',
    ];
}
