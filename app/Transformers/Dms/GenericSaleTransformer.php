<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\GenericSaleInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class GenericSaleTransformer
 *
 * Transformer for generic sales
 *
 * @package App\Transformers\Dms
 */
class GenericSaleTransformer extends TransformerAbstract
{
    public function transform(GenericSaleInterface $sale)
    {
        return [
            'salesPerson' => $sale->salesPerson(),
            'customer' => $sale->customer(),
            'dealerCost' => $sale->dealerCost(),
            'discount' => $sale->discount(),
            'subtotal' => $sale->subtotal(),
            'taxTotal' => $sale->taxTotal(),
            'createdAt' => $sale->createdAt(),
        ];
    }
}
