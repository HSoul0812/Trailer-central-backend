<?php

namespace App\Models\Inventory\Manufacturers;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'manufacturer_brands';

    protected $primaryKey = 'brand_id';

    public $timestamps = false;

}
