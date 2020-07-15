<?php

namespace App\Transformers\CRM\User;

use App\Transformers\Dms\GenericSaleTransformer;
use App\Transformers\Pos\SaleTransformer;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\SalesPerson;

class SalesPersonTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'posSales',
        'allSales'
    ];

    public function transform(SalesPerson $salesPerson)
    {
	    return [
             'id' => $salesPerson->id,
             'name' => $salesPerson->full_name
        ];
    }

    public function includePosSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->posSales, new SaleTransformer());
    }

    public function includeAllSales(SalesPerson $salesPerson)
    {
        return $this->collection($salesPerson->allSales(), new GenericSaleTransformer());
    }
}
