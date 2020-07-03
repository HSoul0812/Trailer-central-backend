<?php

namespace App\Models\CRM\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class PaymentMethod extends Model
{ 
    protected $table = 'qb_payment_methods';

    public $timestamps = false;
    
}
