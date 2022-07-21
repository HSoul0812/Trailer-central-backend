<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Bill
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property int $id
 * @property int $dealer_id,
 * @property double $total,
 * @property int $vendor_id,
 * @property string $status,
 * @property string $doc_num,
 * @property \DateTimeInterface $received_date,
 * @property \DateTimeInterface $due_date,
 * @property string $memo,
 * @property int $qb_id
 */
class Bill extends Model
{
    use TableAware;

    const STATUS_DUE = 'due';
    const STATUS_PAID = 'paid';

    protected $table = 'qb_bills';

    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'total',
        'vendor_id',
        'status',
        'doc_num',
        'received_date',
        'due_date',
        'memo',
        'dealer_location_id'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(BillCategory::class, 'bill_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class, 'bill_id', 'id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'bill_id', 'id');
    }
    
    public function approvals(): HasMany
    {
        return $this
            ->hasMany(QuickbookApproval::class, 'tb_primary_id', 'id')
            ->where('tb_name', $this->table);
    }
}
