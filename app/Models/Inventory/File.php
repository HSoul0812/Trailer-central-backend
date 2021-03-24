<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class File
 * @package App\Models\Inventory
 *
 * @property int $id
 * @property string $title,
 * @property string $path,
 * @property string $type,
 * @property int $size,
 * @property \DateTimeInterface $created_at,
 * @property \DateTimeInterface $updated_at,
 * @property bool $is_active
 */
class File extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'path',
        'type',
        'size',
        'is_active',
    ];

    public function inventoryFiles()
    {
        return $this->hasMany(InventoryFile::class);
    }
}
