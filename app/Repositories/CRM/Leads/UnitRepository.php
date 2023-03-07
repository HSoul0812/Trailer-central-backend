<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Collection;

/**
 * Class UnitRepository
 * @package App\Repositories\CRM\Leads
 */
class UnitRepository extends RepositoryAbstract implements UnitRepositoryInterface
{
    private const AVAILABLE_INCLUDE = [
        'inventory',
    ];

    /**
     * @param $params
     * @return InventoryLead
     */
    public function create($params): InventoryLead
    {
        // Create Inventory Lead
        return InventoryLead::create($params);
    }

    /**
     * @param $params
     * @return bool
     */
    public function delete($params): bool
    {
        // Delete Inventory Lead
        return InventoryLead::where('website_lead_id', $params['website_lead_id'])->delete();
    }

    /**
     * @param $params
     * @return InventoryLead
     */
    public function get($params): InventoryLead
    {
        return InventoryLead::where('website_lead_id', $params['website_lead_id'])
                            ->where('inventory_id', $params['inventory_id'])->first();
    }

    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        $query = InventoryLead::query();

        if (isset($params['website_lead_id'])) {
            $query->where('website_lead_id', $params['website_lead_id']);
        }

        if (isset($params['include']) && is_string($params['include'])) {
            foreach (array_intersect(self::AVAILABLE_INCLUDE, explode(',', $params['include'])) as $include) {
                $query = $query->with($include);
            }
        }

        return $query->get();
    }

    /**
     * Get Inventory IDs of selected lead
     * 
     * @param int $leadId
     * @return array
     */
    public function getUnitIds(int $leadId): array
    {
        $unitIds = InventoryLead::select('inventory_id')->where('website_lead_id', $leadId)
            ->pluck('inventory_id')->toArray();

        $inventoryId = Lead::find($leadId)->inventory_id;

        if (!empty($inventoryId)) {
            $unitIds[] = $inventoryId;
        }

        return $unitIds;
    }
}
