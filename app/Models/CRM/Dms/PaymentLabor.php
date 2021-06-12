<?php
declare(strict_types=1);

namespace App\Models\CRM\Dms;

use App\Models\CRM\Account\Payment;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UnitSaleLabor
 * @package App\Models\CRM\Dms
 *
 * @property int $id
 * @property int $payment_id
 * @property int $quantity
 * @property double $unit_price
 * @property double $dealer_cost
 * @property int $labor_code
 * @property string $status
 * @property string $cause
 * @property double $actual_hours
 * @property double $paid_hours
 * @property double $billed_hours
 * @property string $technician
 * @property string $notes
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class PaymentLabor extends Model
{
    use TableAware;

    protected $table = 'qb_payment_labors';

    /**
     * @return BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
