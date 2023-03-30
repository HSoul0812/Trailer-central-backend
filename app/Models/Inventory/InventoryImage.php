<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InventoryImage
 * @package App\Models\Inventory
 *
 * @property int $image_id
 * @property int $inventory_id
 * @property bool $is_default
 * @property int $is_secondary
 * @property int $position
 * @property string $showroom_image
 * @property bool $was_manually_added
 * @property bool $is_stock
 * @property \DateTimeInterface $overlay_updated_at
 *
 * @property Image $image
 * @property Inventory $inventory
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
        'overlay_updated_at'
    ];

    protected $casts = ['overlay_updated_at' => 'date'];

    /**
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id', 'image_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    public function isDefault(): bool
    {
        return (bool)$this->is_default;
    }

    public function isSecondary(): bool
    {
        return (bool)$this->is_secondary;
    }

    public function originalFilenameRegardingInventoryOverlayConfig(?int $typeOfOverlay): string
    {
        // @todo fix the way it determines it is the primary image
        if ($typeOfOverlay == Inventory::OVERLAY_ENABLED_ALL) {
            return $this->image->originalFilename();
        } elseif ($typeOfOverlay == Inventory::OVERLAY_ENABLED_PRIMARY && ($this->position == 1 || $this->is_default == 1)) {
            return $this->image->originalFilename();
        }

        return $this->image->filename;
    }

    /**
     * This requieres the images are sorted by `InventoryHelper::imageSorter`
     */
    public function shouldRestoreOriginalImage(?int $typeOfOverlay, int $index): bool
    {
        return !($typeOfOverlay == Inventory::OVERLAY_ENABLED_ALL || (
                $typeOfOverlay == Inventory::OVERLAY_ENABLED_PRIMARY &&
                ($this->position == 1 || $this->is_default == 1 || ($this->position === null && $index === 0))
            ));
    }
}
