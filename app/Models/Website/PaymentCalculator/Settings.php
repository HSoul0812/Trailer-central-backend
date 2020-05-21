<?php

namespace App\Models\Website\PaymentCalculator;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model {
    
    const CONDITION_USED = 0;
    const CONDITION_NEW = 1;
    
    protected $table = 'website_payment_calculator_settings';
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'entity_type_id',
        'inventory_condition',
        'months',
        'apr',
        'down',
        'operator',
        'inventory_price'
    ];
    
}
