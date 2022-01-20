<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\CRM\Account\InvoiceItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BillItem
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property int $id
 * @property int $bill_id,
 * @property int $item_id,
 * @property string $description,
 * @property int $qty,
 * @property double $unit_price
 */
class BillItem extends Model
{
    protected $table = 'qb_bill_items';

    public $timestamps = false;

    protected $fillable = [
        'bill_id',
        'item_id',
        'description',
        'qty',
        'unit_price',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function invoice_item(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'item_id', 'item_id');
    }
}
