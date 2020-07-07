<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Leads\Lead;
use App\Transformers\Inventory\InventoryTransformer;
use Illuminate\Database\Eloquent\Collection;

class LeadTransformer extends TransformerAbstract {
    
    protected $inventoryTransformer;
    
    public function __construct()
    {
        $this->inventoryTransformer = new InventoryTransformer();
    }
    
    public function transform(Lead $lead)
    {   
        
	 $transformedLead =  [
             'id' => $lead->identifier,
             'name' => $lead->full_name,
             'inventory_interested_in' => $this->transformInventory($lead->inventory),
             'interactions' => $lead->interactions,
             'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
             'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
             'created_at' => $lead->date_submitted,
             'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
             'email' => $lead->email_address,
             'preferred_contact' => $lead->preferred_contact
         ];
         
         if (!empty($lead->pretty_phone_number)) {
             $transformedLead['phone'] = $lead->pretty_phone_number;
         }
                           
         return $transformedLead;
    }
    
    private function transformInventory(Collection $inventory)
    {
        $ret = [];
        foreach($inventory as $inv) {
            $ret[] = $this->inventoryTransformer->transform($inv);
        }
        return $ret;
    }
    
}
