<?php

namespace App\Models\Inventory\Floorplan;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $table = 'inventory_floor_plan_payment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory\Inventory', 'inventory_id', 'inventory_id');
    }

}
