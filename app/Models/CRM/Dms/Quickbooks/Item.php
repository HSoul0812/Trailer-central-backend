<?php


namespace App\Models\CRM\Dms\Quickbooks;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 * @package App\Models\CRM\DMs\Quickbooks
 * @todo get a better description
 */
class Item extends Model
{
    protected $table = 'qb_items';

    public $timestamps = false;

    const ITEM_TYPES = [
        'TRAILER' => 'trailer',
        'PART' => 'part',
        'LABOR' => 'labor',
        'ADD_ON' => 'add_on',
        'DISCOUNT' => 'discount',
        'TAX' => 'tax',
        'DEPOSIT_DOWN_PAYMENT' => 'deposit_down_payment',
        'INCOME_DOWN_PAYMENT' => 'income_down_payment',
        'TRADE_IN' => 'trade_in',
        'UNDEFINED' => 'undefined',
        'TRADE_IN_PAYOFF' => 'trade_in_payoff'
    ];
}
