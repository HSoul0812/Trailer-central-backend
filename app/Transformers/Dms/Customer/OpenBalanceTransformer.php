<?php

namespace App\Transformers\Dms\Customer;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\Customer;
use App\Transformers\Dms\QuoteTransformer;

class OpenBalanceTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'quotes'
    ];
    
    public function transform(Customer $customer)
    {           
        return [
            'id' => $customer->id,
            'name' => trim($customer->first_name)." ".trim($customer->last_name)
        ];
    }
    
    /**
     * Include Values
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeQuotes(Customer $customer)
    {
        return $this->collection($customer->openQuotes, new QuoteTransformer);
    }
} 