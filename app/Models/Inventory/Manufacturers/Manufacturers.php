<?php

namespace App\Models\Inventory\Manufacturers;

use Illuminate\Database\Eloquent\Model;

class Manufacturers extends Model
{
    protected $table = 'manufacturers';

    protected $primaryKey = 'id';

    public $timestamps = false;

}
