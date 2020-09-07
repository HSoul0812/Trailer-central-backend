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

    const ITEM_TYPES = [
        'TRAILER' => 'trailer',
        'PART' => 'part',
        'LABOR' => 'labor',
        'ADD_ON' => 'add_on',
        'DISCOUNT' => 'discount',
        'TAX' => 'tax',
        'DOWN_PAYMENT' => 'down_payment',
        'UNDEFINED' => 'undefined'
    ];
}
