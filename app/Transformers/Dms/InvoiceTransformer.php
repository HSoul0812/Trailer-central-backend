<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Account\Invoice;
use League\Fractal\TransformerAbstract;

class InvoiceTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'items'
    ];

    public function transform(Invoice $invoice)
    {
        return [
            'id' => (int)$invoice->id,
            // 'dealer_id' => $invoice->dealer_id,
            // 'customer_id' => $invoice->customer_id,
            // 'unit_sale_id' => $invoice->unit_sale_id,
            // 'sales_term_id' => $invoice->sales_term_id,
            // 'repair_order_id' => $invoice->repair_order_id,
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $invoice->due_date,
            'doc_num' => $invoice->doc_num,
            'tax_rate' => $invoice->tax_rate,
            'format' => $invoice->format,
            'total_tax' => (float)$invoice->total_tax,
            'total' => (float)$invoice->total,
            'memo' => $invoice->memo,
            'shipping' => $invoice->shipping,
            // 'qb_id' => $invoice->qb_id,
        ];
    }

    public function includeItems(Invoice $invoice)
    {
        return $this->collection($invoice->items, new InvoiceItemTransformer());
    }

}
