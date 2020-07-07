<?php


namespace App\Models\CRM\Dms;


use App\Models\CRM\Quickbooks\Item;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RefundItems
 *
 * Individual items in refunds
 *
 * @package App\Models\CRM\Dms
 * @property Item $item the related qb_item
 */
class RefundItem extends Model
{
    protected $table = "dealer_refunds_items";

    public function item()
    {
        return $this->hasOne(Item::class);
    }
}
