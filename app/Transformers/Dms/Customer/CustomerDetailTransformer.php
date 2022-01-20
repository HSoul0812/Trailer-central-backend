<?php

namespace App\Transformers\Dms\Customer;

use App\Transformers\Dms\CustomerTransformer;
use App\Models\CRM\User\Customer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\Website\TrackingTransformer;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use Illuminate\Database\Eloquent\Collection;
use App\Transformers\CRM\Leads\LeadTransformer;

class CustomerDetailTransformer extends CustomerTransformer
{
    protected $defaultIncludes = [
        'unitsPurchased',
        'unitsViewed',
        'interactions',
        'lead'
    ];

    public function transform($customer) {
        $transformedData  = parent::transform($customer);
        return $transformedData;
    }

    public function includeLead(Customer $customer)
    {
        if ($customer->lead) {
            return $this->item($customer->lead, new LeadTransformer());
        }

        return $this->null();
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
            return $this->collection($customer->lead->getAllInteractions(), resolve(InteractionTransformer::class));
        }

        return $this->collection(new Collection(), new TrackingTransformer());
    }
}
