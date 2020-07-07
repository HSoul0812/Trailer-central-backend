<?php


namespace App\Models\Pos;


use App\Models\CRM\Quickbooks\Item;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SaleProducts
 *
 * Products for a POS sale
 *
 * @package App\Models\Pos
 * @property Sale $sale
 * @property Item $item
 */
class SaleProduct extends Model
{
    protected $table = "crm_pos_sale_products";

    /**
     * This sale this product belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    /**
     * The associated qb_items
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function item()
    {
        return $this->hasOne(Item::class, 'id', 'item_id');
    }
}
