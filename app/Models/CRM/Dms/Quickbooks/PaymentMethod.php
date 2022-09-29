<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class PaymentMethod extends Model
{
    use TableAware;

    const PAYMENT_METHOD_PO = 'po';
    const PAYMENT_METHOD_TRADE_IN = 'trade_in';
    const PAYMENT_METHOD_FINANCING = 'financing';
    const PAYMENT_METHOD_EFT = 'eft';
    const PAYMENT_METHOD_CHECK = 'check';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_CASH = 'cash';
    
    protected $table = 'qb_payment_methods';

    public $timestamps = false;
    
    protected $fillable = [
        'is_visible',
        'is_default',
        'type',
        'dealer_id',
        'qb_id',
        'name'
    ];
}
