<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BillCategory
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property int $id
 * @property int $bill_id,
 * @property int $account_id,
 * @property string $description,
 * @property double $amount
 */
class BillCategory extends Model
{
    protected $table = 'qb_bill_categories';

    public $timestamps = false;

    protected $fillable = [
        'bill_id',
        'account_id',
        'description',
        'amount',
    ];
}
