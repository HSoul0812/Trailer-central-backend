<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Clapp
 * @package App\Models\Inventory
 */
class Clapp extends Model
{
    /**
     * @var string
     */
    protected $table = 'inventory_clapp';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_clapp_id';

    /**
     * @return Inventory
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
