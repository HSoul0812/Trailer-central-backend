<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{ 
    protected $table = 'qb_vendors';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'business_email',
        'business_phone',
        'ein',
        'street',
        'city',
        'state',
        'zip_code',
        'country',
        'contact_name',
        'contact_phone',
        'contact_email',
        'terms',
        'account_no',
        'notes',
        'ap_account',
        'active',
        'created_at',
        'updated_at',
        'qb_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
