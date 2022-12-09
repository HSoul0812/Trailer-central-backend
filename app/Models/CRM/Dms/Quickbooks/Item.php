<?php


namespace App\Models\CRM\Dms\Quickbooks;


use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Item
 * @package App\Models\CRM\DMs\Quickbooks
 * @todo get a better description
 */
class Item extends Model
{
    use TableAware;

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

    const NAMES = [
        'INVENTORY_DISCOUNT' => 'Inventory Discount',
        'LABOR_DISCOUNT' => 'Labor Discount',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id', 'id');
    }
}
