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

    protected $fillable = [
        'inventory_id',
        'image_id'
    ];
}
