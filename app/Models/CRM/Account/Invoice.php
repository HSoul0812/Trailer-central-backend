<?php

namespace App\Models\CRM\Account;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\User\Customer;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Invoice
 * @package App\Models\CRM\Account
 *
 * @property string $doc_num
 *
 * @property UnitSale $unitSale
 * @property Payment[] $payments
 * @property InvoiceItem[] $items
 * @property Customer $customer
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

    /**
     * @return HasOne
     */
    public function unitSale(): HasOne
    {
        return $this->hasOne(UnitSale::class, 'id', 'unit_sale_id');
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }
}
