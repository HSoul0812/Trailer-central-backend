<?php

declare(strict_types=1);

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;
use Illuminate\Database\Query\Builder;
use App\Models\Inventory\Image;

class InventorySeeder extends Seeder
{
    use WithGetter;

    private $fixedUser;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var User
     */
    private $faker;

    /**
     * @var InventoryHistory
     */
    private $transactions = [];
    
    /**
     * @var int
     */
    private $costlessUnitsQuantity = 0;
    
    /**
     * @var int
     */
    private $unitsWithCostQuantity = 0;
    
    /**
     * @var int
     */
    private $inventoryWithImagesQuantity = 0;    
        
    /**
     * @var array<Inventory>
     */
    private $inventoryWithImages = [];    
    
    /**
     * @var array<Inventory>
     */
    private $inventoryWithCost = [];
    
    /**
     * @var array<Inventory>
     */
    private $inventoryWithNoCost = [];
    
    /**
     * @var App\Models\Inventory\Image
     */
    private $inventoryDefaultImage;

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->faker = Faker::create();
        $this->dealer = factory(User::class)->create();
        $this->inventoryDefaultImage = Image::create([
            'filename' => 'dummy'
        ]);
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        
        for($i = 0; $i < $this->costlessUnitsQuantity; $i++) {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $dealerId,
                'true_cost' => 0
            ]);
            
            $this->inventoryWithNoCost[] = [
                'inventory' => $inventory,
                'image_quantity' => 0
            ];
        }
        
        for($i = 0; $i < $this->unitsWithCostQuantity; $i++) {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $dealerId,
                'true_cost' => 1000
            ]);
            
            $this->inventoryWithCost[] = [
                'inventory' => $inventory,
                'image_quantity' => 0
            ];
        }
        
        for($i = 0; $i < $this->inventoryWithImagesQuantity; $i++) {
            $inventory = factory(Inventory::class)->create([
                'dealer_id' => $dealerId
            ]);
            
            $imagesNumber = 0;
            foreach (range(0, $this->faker->numberBetween(10, 20)) as $number) {
                InventoryImage::create([
                    'inventory_id' => $inventory->inventory_id,
                    'image_id' => $this->inventoryDefaultImage->image_id
                ]);   
                $imagesNumber++;
            }
            
            $this->inventoryWithImages[] = [
                'inventory' => $inventory,
                'image_quantity' => $imagesNumber
            ];
        }
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        InventoryImage::where('image_id', $this->inventoryDefaultImage->image_id)->delete();
        Inventory::where('dealer_id', $dealerId)->delete();    
        $this->inventoryDefaultImage->delete();
        User::destroy($dealerId);
    }
    
    public function setNumberOfCostlessUnits(int $costLessUnitsQuantity) : void
    {
        $this->costlessUnitsQuantity = $costLessUnitsQuantity;
    }
    
    public function setNumberOfUnitsWithCost(int $unitsWithCostQuantity) : void
    {
        $this->unitsWithCostQuantity = $unitsWithCostQuantity;
    }
    
    public function setNumberOfUnitsWithImages(int $unitsWithImagesQuantity) : void
    {
        $this->inventoryWithImagesQuantity = $unitsWithImagesQuantity;
    }
    
}
