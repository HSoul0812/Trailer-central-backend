<?php

namespace App\Transformers\Dms\Customer;

use App\Transformers\Dms\CustomerTransformer;
use App\Models\CRM\User\Customer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\Website\TrackingTransformer;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use Illuminate\Database\Eloquent\Collection;

class CustomerDetailTransformer extends CustomerTransformer 
{
    protected $defaultIncludes = [
        'unitsPurchased',
        'unitsViewed',
        'interactions'
    ];
    
    public function transform($customer) {
        $transformedData  = parent::transform($customer);
        return $transformedData;
    }
    
    public function includeUnitsPurchased(Customer $customer)
    {
        return $this->collection($customer->ownedUnits, new InventoryTransformer());
    }
    
    public function includeUnitsViewed(Customer $customer)
    {
        if (empty($customer->lead) || empty($customer->lead->websiteTracking)) {
            return $this->collection(new Collection(), new TrackingTransformer());
        } 
        
        return $this->collection($customer->lead->websiteTracking, new TrackingTransformer());
    }
    
    public function includeInteractions(Customer $customer)
    {
        if ($customer->lead) {
            return $this->collection($customer->lead->getAllInteractions(), new InteractionTransformer());
        }
        
        return $this->collection(new Collection(), new TrackingTransformer());
    }
}
