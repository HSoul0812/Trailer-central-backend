<?php

namespace App\Models\CRM\Account;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Dms\Quickbooks\PaymentMethod;
use App\Models\CRM\Dms\Payment\DealerSalesReceipt;

/**
 * Class Payment
 * @package App\Models\CRM\Account
 * @property Invoice $invoice The invoice that this payment is for
 * @property PaymentMethod $paymentMethod
 * @property Refund[] $refunds
 */
class Payment extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_payment';

    protected $guarded = [
        'related_payment_intent'
    ];

    protected $casts = [
        'amount' => 'float',
        'total_refund' => 'float',
    ];

    protected $appends = [
        'total_refund',
    ];

    public $timestamps = false;

    // qb_payment has qb_payment.invoice_id but qb_invoices does not have qb_invoices.payment_id so i'm not sure if this is correct
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id', 'invoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'tb_primary_id');
    }

    public function getCalculatedAmountAttribute()
    {
        $refundedAmount = Refund::where('tb_name', 'qb_payment')
            ->where('tb_primary_id', $this->id)
            ->sum('amount');

        return $this->amount - $refundedAmount;
    }

    public function getReceiptsAttribute()
    {
        return DealerSalesReceipt::where('tb_name', 'qb_payment')->where('tb_primary_id', $this->id)->get();
    }

    public function getTotalRefundAttribute()
    {
        return (float) $this->refunds()
            ->where('tb_primary_id', $this->id)
            ->sum('amount');
    }
}
