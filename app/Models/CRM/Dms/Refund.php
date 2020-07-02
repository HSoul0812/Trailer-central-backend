<?php


namespace App\Models\CRM\Dms;


use App\Models\Pos\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class Refund
 *
 * refunds
 *
 * @package App\Models\CRM\Dms
 * @property RefundItem[] $items
 * @property Sale $sale the sale associated with this refund
 */
class Refund extends Model
{
    protected $table = "dealer_refunds";

    protected $casts = [
        'meta' => 'array', // field contains json data
    ];

    public function items()
    {
        return $this->hasMany(RefundItem::class, 'dealer_refunds_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'tb_primary_id', 'id');
    }

    public function getMeta($name) {
        return Arr::get($this->meta, $name);
    }

    public function setMeta($name, $value) {
        return Arr::set($this->meta, $name, $value);
    }

}
