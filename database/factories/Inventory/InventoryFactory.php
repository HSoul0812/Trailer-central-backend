<?php

/** @var Factory $factory */

use App\Models\Inventory\Inventory;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Category;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\Showroom\Showroom;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Inventory::class, static function (Faker $faker, array $attributes): array {
    // Get Dealer ID
    $dealer_id = $attributes['dealer_id'] ?? factory(User::class)->create()->getKey();

    // Get Dealer Location ID
    $dealer_location_id = $attributes['dealer_location_id'] ?? factory(DealerLocation::class)->create([
        'dealer_id' => $dealer_id
    ])->getKey();

    // Get Entity/Category
    $entityType = EntityType::where('entity_type_id', '<>', 2)->inRandomOrder()->first();
    $inventoryCategory = Category::where('entity_type_id', $entityType->entity_type_id)->inRandomOrder()->first();

    $category = $attributes['category'] ?? optional($inventoryCategory)->legacy_category ?? '';

    // Get Manufacturer
    $mfg = $attributes['manufacturer'] ?? Manufacturers::inRandomOrder()->first();
    if(!empty($attributes['manufacturer']) || !empty($mfg->id)) {
        $mfg_id = $mfg->id ?? Manufacturers::where('name', $attributes['manufacturer'])->first()->id;
    } else {
        $mfg = factory(Manufacturers::class)->create();
        $mfg_id = $mfg->getKey();
    }

    // Get Showroom Model
    $brand = $attributes['brand'] ?? Brand::where('manufacturer_id', $mfg_id)->inRandomOrder()->first();
    $brandName = is_string($brand) ? $brand : $brand->name ?? '';
    $showroom = Showroom::where('manufacturer', is_string($mfg) ? $mfg : $mfg->name)->inRandomOrder()->first();

    // Get Prices
    $msrp = $faker->randomFloat(2, 2000, 9999);
    if(!empty($showroom->msrp)) {
        $msrp = $showroom->msrp;
    }
    $price = $faker->randomFloat(2, $msrp * 0.8, $msrp);

    // Get Created Date
    $createdAt = $faker->dateTimeThisMonth;

    $overrides = [
        'entity_type_id' => $entityType->entity_type_id,
        'dealer_id' => $dealer_id,
        'dealer_location_id' => $dealer_location_id,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'updated_at_auto' => $createdAt,
        'active' => 1,
        'title' => !empty($showroom->title) ? $showroom->title : $faker->sentence,
        'stock' => Str::random(10),
        'manufacturer' => is_string($mfg) ? $mfg : $mfg->name,
        'brand' => $showroom->brand ??  $brandName ?? '',
        'model' => $showroom->model ?? $faker->words(2, true),
        //'description' => !empty($showroom->description) ? $showroom->description : $faker->realText,
        'description' => $faker->realText(),
        'status' => 1,
        'category' => !empty($showroom->type) ? $showroom->type : $category,
        'vin' => $attributes['vin'] ?? Str::random(17),
        'geolocation' => DB::raw('POINT(' . $faker->longitude() . ', ' . $faker->latitude() . ')'),
        'msrp' => $msrp,
        'price' => $price,
        'cost_of_unit' => $price / 2,
        'year' => $faker->year,
        'condition' => $faker->randomElement(['new', 'used']),
        'notes' => $faker->realText(),
        'is_archived' => 0,
        'showroom_id' => !empty($showroom->id) ? $showroom->id : null
    ];

    if (isset($attributes['inventory_id'])) {
        $overrides['inventory_id'] = $attributes['inventory_id'];
    }

    // Return Overrides
    return $overrides;
});
