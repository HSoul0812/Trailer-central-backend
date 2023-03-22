<?php

namespace App\Models\Inventory;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Image
 * @package App\Models\Inventory
 *
 * @property int $image_id
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property string $filename
 * @property string $filename_noverlay the filename of the original image, so far only used for inventory images
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
        'filename_noverlay',
        'hash',
        'program',
    ];

    public function inventoryImages()
    {
        return $this->hasMany(InventoryImage::class, 'image_id', 'image_id');
    }

    public function originalFilename(): string
    {
        return $this->filename_noverlay ? $this->filename_noverlay : $this->filename;
    }
}
