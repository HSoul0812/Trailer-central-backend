<?php

namespace App\Models\Marketing\Craigslist;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clapp_balance';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'balance'
    ];
    

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'last_updated';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
