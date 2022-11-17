<?php

namespace Tests\database\seeds\Marketing\Facebook;

use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Marketing\Facebook\Error;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\User\DealerLocation;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use Faker\Generator;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\DB;
use Tests\database\seeds\Seeder;

class MarketplaceSeeder extends Seeder
{

    /**
     * @var \App\Models\Marketing\Facebook\Marketplace[]
     */
    public $createdMarketplaces = [];

    /**
     * @var \App\Models\Inventory\Image[]
     */
    public $createdImages = [];

    /**
     * @var int[]
     */
    public $usedImagesIds = [];

    /**
     * @var \App\Models\Inventory\Inventory[]
     */
    public $createdInventories = [];

    /**
     * @var int[]
     */
    public $usedInventoriesIds = [];

    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \App\Models\User\DealerLocation
     */
    public $dealerLocation;

    /**
     */
    public function __construct()
    {
        $this->faker = app(Generator::class);
        $this->dealer = factory(User::class)->create();
        $this->user = factory(NewUser::class)->create();
        $this->newDealerUser = factory(NewDealerUser::class)->create(['id' => $this->dealer->getKey(), 'user_id' => $this->user->getKey()]);
        $this->dealerLocation = factory(DealerLocation::class)->create([
            'latitude' => 11,
            'longitude' => 11,
            'dealer_id' => $this->dealer->dealer_id,
        ]);
    }

    public function seed(): void
    {
        $seeds = [
            [
                'action' => 'create'
            ],
            [
                'action' => 'create'
            ],
            [
                'action' => 'create'
            ],
            [
                'action' => 'create'
            ],
        ];

        collect($seeds)->each(function (array $seed): void {
            if (isset($seed['action']) && $seed['action'] === 'create') {
                $marketplace = factory(Marketplace::class)->create([
                    'dealer_id' => $this->dealer->getKey(),
                ]);

                $this->createdMarketplaces[] = $marketplace;
            }
        });

        for ($i = 0; $i < 100; $i++) {
            $this->createdImages[] = Image::create([
                'filename' => 'filename',
                'filename_noverlay' => 'filename_noverlay',
                'hash' => 'hash',
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            $this->createdInventories[] = Inventory::create([
                'entity_type_id' => 1,
                'dealer_id' => $this->dealer->getKey(),
                'dealer_location_id' => $this->dealerLocation->dealer_location_id,
                'title' => $this->faker->sentence(4),
                'dealer_identifier' => $this->dealer->getKey(),
                'latitude' => 10,
                'longitude' => 10,
                'geolocation' => new Point(0, 0),
                'stock' => $this->faker->slug(2)
            ]);
        }
    }

    public function takeImage(): ?Image
    {
        $unusedImages = array_filter($this->createdImages, function ($image) {
            return !in_array($image->id, $this->usedImagesIds);
        });

        if (count($unusedImages) <= 1) {
            return null;
        }

        $unusedImage = $unusedImages[0];
        $this->usedImagesIds[] = $unusedImage->id;

        return $unusedImage;
    }

    public function takeInventory(): ?Inventory
    {
        $unusedInventories = array_filter($this->createdInventories, function ($inventory) {
            return !in_array($inventory->getKey(), $this->usedInventoriesIds);
        });

        if (count($unusedInventories) <= 1) {
            return null;
        }

        $unusedInventory = $unusedInventories[0];
        $this->usedInventoriesIds[] = $unusedInventory->getKey();

        return $unusedInventory;
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        $newUserId = $this->user->getKey();

        Marketplace::where('dealer_id', $dealerId)->delete();
        NewUser::destroy($newUserId);
        User::destroy($dealerId);

        foreach ($this->createdImages as $createdImage) {
            Image::destroy($createdImage->image_id);
        }

        foreach ($this->createdMarketplaces as $marketplace) {
            Error::where('marketplace_id', $marketplace->id)->delete();
            Listings::where('marketplace_id', $marketplace->id)->delete();
            Marketplace::destroy($marketplace->id);
        }

        Inventory::where('dealer_id', $dealerId)->delete();
    }
}
