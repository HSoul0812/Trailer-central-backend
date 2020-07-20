<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Account\Payment;
use App\Transformers\Dms\RefundTransformer;
use League\Fractal\TransformerAbstract;

class PaymentTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'invoice',
        'refunds',
    ];

    public function transform(Payment $payment)
    {
        return [
            'id' => (int)$payment->id,
            //'dealer_id' => $payment->dealer_id,
            //'invoice_id' => $payment->invoice_id,
            //'deposit_acc_id' => $payment->deposit_acc_id,
            'payment_method_id' => $payment->payment_method_id,
            'financing_company_id' => $payment->financing_company_id,
            'register_id' => $payment->register_id,
            'doc_num' => $payment->doc_num,
            'amount' => (float)$payment->amount,
            'check_num' => $payment->check_num,
            'check_name' => $payment->check_name,
            'date' => $payment->date,
            'memo' => $payment->memo,
            // 'related_payment_intent' => $payment->related_payment_intent,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
            // 'qb_id' => $payment->qb_id,
        ];
    }

    public function includeInvoice(Payment $payment)
    {
        return $this->item($payment->invoice, new InvoiceTransformer());
    }

    public function includeRefunds(Payment $payment)
    {
        return $this->collection($payment->refunds, new RefundTransformer());
    }
}
