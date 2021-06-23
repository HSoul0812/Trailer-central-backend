<?php

namespace App\Models\CRM\Account;

use App\Models\CRM\Dms\UnitSale;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * @package App\Models\CRM\Account
 *
 * @property string $doc_num
 *
 * @property UnitSale $unitSale
 * @property Payment[] $payments
 * @property InvoiceItem[] $items
 */
class Invoice extends Model implements Filterable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_invoices';

    public $timestamps = false;

    public function unitSale()
    {
        return $this->hasOne(UnitSale::class, 'id', 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }
}
