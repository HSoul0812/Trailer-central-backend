<?php


namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryFile
 * @package App\Models\Inventory
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
}
