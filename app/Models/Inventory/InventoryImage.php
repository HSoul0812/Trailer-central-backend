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
 * @property int $is_secondary,
 * @property int $position,
 * @property string $showroom_image,
 * @property bool $was_manually_added,
 * @property bool $is_stock,
 *
 * @property Image $image
 */
class InventoryImage extends Model
{
    use TableAware;

    /** @var int to make the sorting consistent across ES worker, Legacy API and New API */
    public const LAST_IMAGE_POSITION = 100;

    /** @var int to make the sorting consistent across ES worker, Legacy API and New API */
    public const FIRST_IMAGE_POSITION = -1;

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

    public function isDefault(): bool
    {
        return (bool)$this->is_default;
    }

    public function isSecondary(): bool
    {
        return (bool)$this->is_secondary;
    }
}
