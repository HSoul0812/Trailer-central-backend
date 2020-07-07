<?php

namespace App\Transformers\CRM\User;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\SalesPerson;

class SalesPersonTransformer extends TransformerAbstract 
{
    public function transform(SalesPerson $salesPerson)
    {
	return [
             'id' => $salesPerson->id,
             'name' => $salesPerson->full_name
        ];
    }
}
