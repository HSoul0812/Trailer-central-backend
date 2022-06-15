<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;
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
    protected $table = 'qb_bill_payment';

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
}
