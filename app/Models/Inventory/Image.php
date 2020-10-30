<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Image
 * @package App\Models\Inventory
 */
class Image extends Model
{
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
}
