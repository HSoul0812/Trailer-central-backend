<?php

namespace Tests\database\seeds\User;

use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationMileageFee;
use App\Traits\WithGetter;
use Tests\database\seeds\Inventory\CategorySeeder;
use Tests\database\seeds\Seeder;

/**
 * Class DealerLocationMileageFeeSeeder
 * @package Tests\database\seeds\User
 *
 * @property-read Collection<DealerLocationMileageFee> $mileageFees
 */
class DealerLocationMileageFeeSeeder extends Seeder
{
    use WithGetter;

    const DEFAULT_DEALER_ID = 1001;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Collection<DealerLocationMileageFee>
     */
    private $mileageFees;

    /**
     * @var CategorySeeder
     */
    private $inventoryCategorySeeder;

    /**
     * @var DealerLocation
     */
    private $location;

    public function __construct(array $params)
    {
        $this->params = $params;
        $this->inventoryCategorySeeder = new CategorySeeder([]);
        $this->mileageFees = collect([]);

        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => self::DEFAULT_DEALER_ID
        ]);;
    }

    public function dealerLocation()
    {
        return $this->location;
    }

    public function seed($count = 1): void
    {
        $this->inventoryCategorySeeder->seed($count);

        $this->inventoryCategorySeeder->data()->map(function ($category) {
            $this->mileageFees->push(factory(DealerLocationMileageFee::class)->create([
                'inventory_category_id' => $category->inventory_category_id,
                'dealer_location_id' => $this->location->dealer_location_id,
                'fee_per_mile' => rand(1, 999)
            ] + $this->params));
        });
    }

    public function data()
    {
        return $this->mileageFees;
    }

    public function cleanUp(): void
    {
        DealerLocationMileageFee::destroy($this->mileageFees->map(function ($fee) {
            return $fee->id;
        }));

        $this->inventoryCategorySeeder->cleanUp();
    }
}
