<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Repositories\CRM\Leads\UnitRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UnitRepository implements UnitRepositoryInterface {

    public function create($params): InventoryLead {
        // Create Inventory Lead
        return InventoryLead::create($params);
    }

    public function delete($params) {
        // Delete Inventory Lead
        return InventoryLead::where('website_lead_id', $params['website_lead_id'])->delete();
    }

    public function get($params): InventoryLead {
        return InventoryLead::where('website_lead_id', $params['website_lead_id'])
                            ->where('inventory_id', $params['inventory_id'])->first();
    }

    public function getAll($params): Collection {
        // Return Lead Sources
        return InventoryLead::where('website_lead_id', $params['website_lead_id'])->get();
    }

    public function update($params): InventoryLead {
        $inventoryLead = $this->get($params);

        DB::transaction(function() use (&$inventoryLead, $params) {
            // Update Lead Status
            $inventoryLead->fill($params)->save();
        });

        // Return Full Lead Source Details
        return $inventoryLead;
    }
}
