<?php

namespace App\Transformers\Dms\Bill;

use App\Constants\Date;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\Quickbooks\BillItem;
use App\Repositories\Dms\UnitSaleRepositoryInterface;
use League\Fractal\TransformerAbstract;

class BillTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'categories',
        'payments',
        'items'
    ];

    protected $defaultIncludes = [
        'categories',
        'payments',
        'items'
    ];

    /** @var UnitSaleRepositoryInterface */
    private $unitSaleRepository;

    /**
     * BillTransformer constructor.
     * @param UnitSaleRepositoryInterface $unitSaleRepository
     */
    public function __construct(UnitSaleRepositoryInterface $unitSaleRepository)
    {
        $this->unitSaleRepository = $unitSaleRepository;
    }

    public function transform(Bill $bill)
    {
        return [
            'id' => $bill->id,
            'status' => $bill->status,
            'vendor_id' => $bill->vendor_id,
            'dealer_id' => $bill->dealer_id,
            'dealer_location_id' => $bill->dealer_location_id,
            'doc_num' => $bill->doc_num,
            'received_date' => $bill->received_date ? $bill->received_date->format(Date::FORMAT_Y_M_D) : null,
            'total' => $bill->total,
            'due_date' => $bill->due_date ? $bill->due_date->format(Date::FORMAT_Y_M_D) : null,
            'packing_list_no' => $bill->packing_list_no,
            'qb_id' => $bill->qb_id,
            'remaining_balance' => $this->calculateRemaining($bill)
        ];
    }

    public function includeItems(Bill $bill)
    {
        $items = [];
        /** @var BillItem $billItem */
        foreach ($bill->items as $billItem) {
            $info = [
                "id" => $billItem->id,
                "item_id" => $billItem->item_id,
                "description" => $billItem->description,
                "qty" => $billItem->qty,
                "unit_price" => $billItem->unit_price,
            ];

            if ($billItem->item && $billItem->item->category) {
                $info = array_merge($info, [
                    "item_name" => $billItem->item->name,
                    'item_category' => $billItem->item->category->name,
                    'item_category_id' => $billItem->item->item_category_id,
                ]);
            } else {
                $info = array_merge($info, [
                    "item_name" => '',
                    'item_category' => '',
                    'item_category_id' => null,
                ]);
            }

            $items[] = $info;
        }

        return $this->primitive($items);
    }

    /**
     * @param Bill $bill
     * @return float|int
     */
    private function calculateRemaining(Bill $bill)
    {
        $balance = (float) $bill->total;

        foreach ($bill->payments as $payment) {
            $balance -= $payment->amount;
        }

        return $balance <= 0 ? 0 : $balance;
    }

    /**
     * @param Bill $bill
     */
    public function includeCategories(Bill $bill)
    {
        return $this->collection($bill->categories, new BillCategoryTransformer());
    }

    public function includePayments(Bill $bill)
    {
        return $this->primitive($bill->payments);
    }
}
