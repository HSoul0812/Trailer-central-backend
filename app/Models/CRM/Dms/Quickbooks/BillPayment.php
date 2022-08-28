<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Constants\Date;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BillPayment
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property int $id
 * @property int $bill_id,
 * @property int $dealer_id,
 * @property string $doc_num,
 * @property double $amount
 * @property string $payment_type,
 * @property string $date,
 * @property int $account_id,
 * @property string $memo,
 * @property int $qb_id,
 */
class BillPayment extends Model
{
    public const TABLE_NAME = 'qb_bill_payment';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [
        'bill_id',
        'dealer_id',
        'doc_num',
        'amount',
        'payment_type',
        'date',
        'account_id',
        'memo',
        'qb_id',
    ];

    public function approvals(): HasMany
    {
        return $this
            ->hasMany(QuickbookApproval::class, 'tb_primary_id', 'id')
            ->where('tb_name', $this->table);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function getTransactionDateAttribute(): Carbon
    {
        return $this->date ? Carbon::createFromFormat(Date::FORMAT_Y_M_D, $this->date) : null;
    }
}
