<?php


namespace App\Models\CRM\Account;


use App\Models\CRM\Dms\Refund;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Quickbooks\PaymentMethod;

/**
 * Class Payment
 * @package App\Models\CRM\Account
 * @property Invoice $invoice The invoice that this payment is for
 * @property PaymentMethod $paymentMethod
 * @property Refund[] $refunds
 */
class Payment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qb_payment';

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
}
