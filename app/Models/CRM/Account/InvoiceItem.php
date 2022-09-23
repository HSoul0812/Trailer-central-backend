<?php


namespace App\Models\CRM\Account;


use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoiceItem
 *
 * @package App\Models\CRM\Account
 * @property Invoice $invoice the invoice this belongs to
 * @property Item $item the qb_item
 */
class InvoiceItem extends Model
{
    use TableAware;

    protected $table = "qb_invoice_items";

    public $timestamps = false;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function item()
    {
        return $this->hasOne(Item::class, 'id', 'item_id');
    }
    
    public function getItemPriceAttribute() : float
    {
        return (int)$this->qty * (float)$this->unit_price;
    }
}
