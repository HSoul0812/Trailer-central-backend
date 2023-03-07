<?php

namespace App\Models\Inventory\Manufacturers;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class Manufacturers extends Model
{
    use TableAware;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manufacturers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo',
        'logo_highres',
        'description',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

}
