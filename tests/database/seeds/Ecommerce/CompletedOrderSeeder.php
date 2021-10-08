<?php

declare(strict_types=1);

namespace Tests\database\seeds\Ecommerce;

use App\Models\Parts\Textrail\Part;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read CompletedOrder $completedOrder
 */
class CompletedOrderSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Part
     */
    protected $part;

    /**
     * @var CompletedOrder
     */
    protected $completedOrder;

    public function seed(): void
    {
        $this->seedPart();


        $this->completedOrder = factory(CompletedOrder::class, 1)->create(); // 1 new completed order
    }

    public function seedPart(): void
    {
        $this->part = factory(Part::class, 1)->create([
            "manufacturer_id" => 66,
            "brand_id" => 25,
            "type_id" => 11,
            "category_id" => 8,
        ]);
    }

    public function cleanUp(): void
    {
        // Database clean up
        CompletedOrder::whereIn('id', $this->completedOrder->getKey())->delete();
        Part::whereIn('id', $this->part->getKey())->delete();
    }
}