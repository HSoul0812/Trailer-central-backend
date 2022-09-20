<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\Inventory\Inventory;
use App\Models\Parts\Vendor;
use App\Models\User\DealerLocation;
use App\Models\Traits\TableAware;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public const TABLE_NAME = 'qb_bills';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'dealer_location_id',
        'vendor_id',
        'doc_num',
        'total',
        'received_date',
        'due_date',
        'memo',
        'packing_list_no',
        'status',
        'qb_id',
    ];

    protected $dates = [
        'received_date',
        'due_date',
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

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
}
