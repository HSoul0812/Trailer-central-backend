<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 *
 * @property Image $image
 */
class InventoryImage extends Model {
    use TableAware;

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

    /**
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id', 'image_id');
    }
}
