<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Image
 * @package App\Models\Inventory
 *
 * @property int $image_id
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property string $filename image URL which is shown in dealer websites
 * @property string $filename_with_overlay
 * @property string $filename_without_overlay image URL shown in TrailerTrade
 * @property string $filename_noverlay @deprecated this column is deprecated
 * @property string $hash
 * @property string $program
 */
class Image extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'image_id';

    public $timestamps = true;

    protected $fillable = [
        'filename',
        'filename_with_overlay',
        'filename_without_overlay',
        'filename_noverlay',
        'hash',
        'program',
    ];

    public function inventoryImages(): HasMany
    {
        return $this->hasMany(InventoryImage::class, 'image_id', 'image_id');
    }
}
