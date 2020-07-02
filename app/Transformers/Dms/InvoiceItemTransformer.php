<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Account\InvoiceItem;
use App\Transformers\Quickbooks\ItemTransformer;
use League\Fractal\TransformerAbstract;

class InvoiceItemTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'item'
    ];

    public function transform(InvoiceItem $item)
    {
        return [
            'id' => (int)$item->id,
            //'invoice_id' => $item->invoice_id,
            //'item_id' => $item->item_id,
            'description' => $item->description,
            'qty' => (int)$item->qty,
            'unit_price' => (float)$item->unit_price,
            'is_taxable' => (bool)$item->is_taxable,
        ];
    }

    public function includeItem(InvoiceItem $item)
    {
        return $this->item($item->item, new ItemTransformer());
    }
}
