<?php


namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InventoryFile
 * @package App\Models\Inventory
 *
 * @property int $id
 * @property int $file_id,
 * @property int $inventory_id,
 * @property int $position,
 * @property bool $is_manual,
 *
 * @property File $file
 */
class InventoryFile extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_file';

    public $timestamps = false;

    protected $fillable = [
        'file_id',
        'inventory_id',
        'position',
        'is_manual',
    ];

    /**
     * @return BelongsTo
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class,'file_id', 'id');
    }
}
