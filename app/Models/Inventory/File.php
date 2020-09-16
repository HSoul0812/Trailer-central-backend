<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class File
 * @package App\Models\Inventory
 */
class File extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file';

    public function inventoryFiles()
    {
        return $this->hasMany(InventoryFile::class);
    }
}
