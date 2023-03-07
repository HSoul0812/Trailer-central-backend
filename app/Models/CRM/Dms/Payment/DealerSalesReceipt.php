<?php

namespace App\Models\CRM\Dms\Payment;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DealerSalesReceipt
 * @package App\Models\CRM\Dms\Payment
 *
 * @property int $id
 * @property int $dealer_id
 * @property string $tb_name
 * @property int $tb_primary_id
 * @property string $receipt_path
 */
class DealerSalesReceipt extends Model
{
    use TableAware;

    protected $table = 'dealer_sales_receipt';
}
