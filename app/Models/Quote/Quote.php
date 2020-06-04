<?php

namespace App\Models\Quote;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{ 
    protected $table = 'dms_unit_sale';

    const UPDATED_AT = null;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer\Customer', 'buyer_id');
    }

}
