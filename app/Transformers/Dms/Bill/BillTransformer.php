<?php
namespace App\Transformers\Dms\Bill;

use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\Quickbooks\BillCategory;
use App\Models\CRM\Dms\Quickbooks\BillItem;
use App\Models\CRM\Dms\UnitSale;
use App\Repositories\Dms\UnitSaleRepositoryInterface;
use League\Fractal\TransformerAbstract;

class BillTransformer extends TransformerAbstract
{
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
            'received_date' => $bill->received_date,
            'total' => $bill->total,
            'due_date' => $bill->due_date,
            'packing_list_no' => $bill->packing_list_no,
            'qb_id' => $bill->qb_id,
            'items' => $this->formatItems($bill),
            'categories' => $this->formatCategories($bill),
            'payments' => $bill->payments,
            'remaining_balance' => $this->calculateRemaining($bill)
        ];
    }

    private function formatItems(Bill $bill): array
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

        return $items;
    }

    /**
     * @param Bill $bill
     * @return float|int
     */
    private function calculateRemaining(Bill $bill)
    {
        $balance = (float) $bill->total;

        foreach ($bill->payments as $payment)
        {
            $balance -= $payment->amount;
        }

        return $balance <= 0 ? 0 : $balance;
    }

    /**
     * @param Bill $bill
     */
    private function formatCategories(Bill $bill): array
    {
        $categories = [];
        /** @var BillCategory $category */
        foreach ($bill->categories as $category)
        {
            $categories[] = [
                'account_name' => $category->account ? $category->account->name : '',
                'account_id' => $category->account_id,
                'description' => $category->description,
                'amount' => $category->amount,
                'bill_id' => $category->bill_id,
                'id' => $category->id
            ];
        }

        return $categories;
    }
}