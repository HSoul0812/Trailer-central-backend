<?php

namespace App\Transformers\Dms;

use App\Models\CRM\Dms\Refund;
use App\Transformers\Dms\Payment\DealerSalesReceiptTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Class RefundTransformer
 * @package App\Transformers\Dms
 */
class RefundTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'items',
        'invoice',
        'receipt',
        'customer',
        'unitSale',
    ];

    /**
     * @param Refund $refund
     * @return array
     */
    public function transform(Refund $refund): array
    {
        return [
            'id' => (int)$refund->id,
            'tb_name' => $refund->tb_name,
            'tb_primary_id' => (int)$refund->tb_primary_id,
            'amount' => (float)$refund->amount,
            'created_at' => $refund->created_at,
            'updated_at' => $refund->updated_at,
        ];
    }

    /**
     * @param Refund $refund
     * @return Collection
     */
    public function includeItems(Refund $refund): Collection
    {
        return $this->collection($refund->items, new RefundItemTransformer());
    }

    /**
     * @param Refund $refund
     * @return Item|null
     */
    public function includeInvoice(Refund $refund): ?Item
    {
        if (!$refund->invoice) {
            return null;
        }

        return $this->item($refund->invoice, new InvoiceTransformer());
    }

    /**
     * @param Refund $refund
     * @return Item|null
     */
    public function includeReceipt(Refund $refund): ?Item
    {
        if (!$refund->receipt) {
            return null;
        }

        return $this->item($refund->receipt, new DealerSalesReceiptTransformer());
    }

    /**
     * @param Refund $refund
     * @return Item|null
     */
    public function includeCustomer(Refund $refund): ?Item
    {
        if (optional($refund->invoice)->customer) {
            return $this->item($refund->invoice->customer, new CustomerTransformer());
        }

        if (optional($refund->unitSale)->customer) {
            return $this->item($refund->unitSale->customer, new CustomerTransformer());
        }

        return null;
    }

    public function includeUnitSale(Refund $refund): ?Item
    {
        if (!$refund->unitSale) {
            return null;
        }

        return $this->item($refund->unitSale, new QuoteTransformer());;
    }
}
