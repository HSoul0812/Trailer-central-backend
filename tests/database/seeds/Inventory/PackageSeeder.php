<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\Packages\Package;
use App\Models\Inventory\Packages\PackageInventory;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class PackageSeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read Package $package
 * @property-read PackageInventory $packageInventory
 */
class PackageSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var PackageInventory
     */
    private $packageInventory;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function seed(): void
    {
        $this->package = factory(Package::class)->create([
            'dealer_id' => $this->params['dealer_id'],
            'visible_with_main_item' => $this->params['visible_with_main_item'] ?? true
        ]);

        $this->packageInventory = factory(PackageInventory::class)->create([
            'package_id' => $this->package->id,
            'inventory_id' => $this->params['inventory_id'],
            'is_main_item' => $this->params['is_main_item'] ?? true
        ]);
    }

    public function cleanUp(): void
    {
        PackageInventory::destroy($this->packageInventory->id);
        Package::destroy($this->package->id);
    }
}
