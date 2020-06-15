<?php

namespace App\Models\Website\PaymentCalculator;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model {
    
    const CONDITION_USED = 0;
    const CONDITION_NEW = 1;
    
    const FINANCING = 'financing';
    const NO_FINANCING = 'no_financing';
    
    const OPERATOR_LESS_THAN = 'less_than';
    const OPERATOR_OVER = 'over';
    
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
        'inventory_price',
        'inventory_condition',
        'financing'
    ];
    
}
