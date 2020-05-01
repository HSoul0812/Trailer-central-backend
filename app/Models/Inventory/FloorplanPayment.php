<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class FloorplanPayment extends Model
{

    use Searchable;

    protected $table = 'inventory_floor_plan_payment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['inventory'] = (string)$this->inventory;
        $array['type'] = $this->type;
        $array['payment_type'] = $this->payment_type;
        $array['created_at'] = $this->created_at;
        $array['amount'] = $this->amount;

        return $array;
    }

    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory\Inventory', 'inventory_id', 'inventory_id');
    }

}
