<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Transformers\Inventory\InventoryTransformer;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Class ProductTransformer
 * @package App\Transformers\CRM\Leads
 */
class ProductTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'inventory',
    ];

    /**
     * @var InventoryTransformer
     */
    private $inventoryTransformer;

    public function __construct(InventoryTransformer $inventoryTransformer)
    {
        $this->inventoryTransformer = $inventoryTransformer;
    }

    /**
     * Transform Full Lead!
     *
     * @param InventoryLead $inventoryLead
     *
     * @return array
     */
    public function transform(InventoryLead $inventoryLead): array
    {
        return  [
            'id' => $inventoryLead->id,
            'website_lead_id' => $inventoryLead->website_lead_id,
            'inventory_id' => $inventoryLead->inventory_id,
        ];
    }

    /**
     * @param InventoryLead $inventoryLead
     * @return Item
     */
    public function includeInventory(InventoryLead $inventoryLead): ?Item
    {
        if (empty($inventoryLead->inventory)) {
            return null;
        }

        return $this->item($inventoryLead->inventory, $this->inventoryTransformer);
    }
}
