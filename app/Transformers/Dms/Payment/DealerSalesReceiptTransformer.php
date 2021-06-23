<?php

namespace App\Transformers\Dms\Payment;

use App\Models\CRM\Dms\Payment\DealerSalesReceipt;
use League\Fractal\TransformerAbstract;

/**
 * Class DealerSalesReceiptTransformer
 * @package App\Transformers\Dms\Payment
 */
class DealerSalesReceiptTransformer extends TransformerAbstract
{
    /**
     * @param DealerSalesReceipt $receipt
     * @return array
     */
    public function transform(DealerSalesReceipt $receipt): array
    {
        return [
            'id' => $receipt->id,
            'dealer_id' => $receipt->dealer_id,
            'tb_name' => $receipt->tb_name,
            'tb_primary_id' => $receipt->tb_primary_id,
            'receipt_path' => $receipt->receipt_path,
        ];
    }
}
