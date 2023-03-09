<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 * @package App\Models\Inventory
 *
 * @property int $id
 * @property string $name
 * @property string $label
 */
class Status extends Model
{
    protected $table = 'inventory_status';

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'status');
    }
}
