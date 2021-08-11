<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

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
}
