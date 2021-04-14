<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryImage
 * @package App\Models\Inventory
 *
 * @property int $image_id,
 * @property int $inventory_id,
 * @property bool $is_default,
 * @property bool $is_secondary,
 * @property int $position,
 * @property string $showroom_image,
 * @property bool $was_manually_added,
 * @property bool $is_stock,
 */
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
