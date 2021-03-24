<?php


namespace App\Transformers\Dms;


use App\Models\CRM\Dms\TaxCalculator;
use League\Fractal\TransformerAbstract;

class TaxCalculatorTransformer extends TransformerAbstract
{

    public function transform(TaxCalculator $taxCalculator)
    {
        return [
            'id' => $taxCalculator->id,
            'dealer_id' => $taxCalculator->dealer_id,
            'title' => $taxCalculator->title,
            'description' => $taxCalculator->description,
            'code' => $taxCalculator->code,
        ];
    }

}
