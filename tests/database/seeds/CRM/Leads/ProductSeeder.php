<?php

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\InventoryLead;
use App\Models\Inventory\Inventory;
use App\Traits\WithGetter;

/**
 * @property-read Inventory[] $inventories
 * @property-read array $inventoryLeads
 */
class ProductSeeder extends AbstractLeadsSeeder
{
    use WithGetter;

    /**
     * @var Inventory[]
     */
    private $inventories;

    /**
     * @var array
     */
    private $inventoryLeads;

    /**
     * @var bool
     */
    private $withProducts;

    /**
     * InventorySeeder constructor.
     */
    public function __construct($withProducts = true)
    {
        parent::__construct();
        $this->withProducts = $withProducts;
    }

    public function seed(): void
    {
        if (!$this->withProducts) {
            return;
        }

        $productsCount = rand(2, 5);

        for ($i = 0; $i < $productsCount; $i++) {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $this->dealer->getKey()
            ]);

            $this->inventoryLeads[] = factory(InventoryLead::class)->create([
                'inventory_id' => $inventory,
                'website_lead_id' => $this->lead->getKey(),
            ])->toArray();

            $this->inventories[] = $inventory;
        }
    }

    public function cleanUp(): void
    {
        parent::cleanUp();

        $dealerId = $this->dealer->getKey();

        foreach ($this->inventories as $inventory) {
            InventoryLead::where('inventory_id', $inventory->getKey())->delete();
        }

        Inventory::where('dealer_id', $dealerId)->delete();
    }
}
