<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\TestCase;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Category;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\Showroom\Showroom;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Inventory::class, function (Faker $faker) {
    // Get Entity/Category
    $entityType = EntityType::inRandomOrder()->first();
    $category = Category::where('entity_type_id', $entityType->entity_type_id)->inRandomOrder()->first();

    // Get Showroom Model
    $mfg = Manufacturers::inRandomOrder()->first();
    $brand = Brand::where('manufacturer_id', $mfg->id)->inRandomOrder()->first();
    $showroom = Showroom::where('manufacturer', $mfg->name)->inRandomOrder()->first();

    // Select Random Values
    $conditions = ['new', 'used'];
    $condition = array_rand($conditions);

    // Get Prices
    $msrp = $faker->randomFloat(2, 2000, 9999);
    if(!empty($showroom->msrp)) {
        $msrp = $showroom->msrp;
    }
    $price = $faker->randomFloat(2, $msrp * 0.8, $msrp);

    // Get Created Date
    $createdAt = $faker->dateTimeThisMonth;

    // Return Overrides
    return [
        'entity_type_id' => $entityType->entity_type_id,
        'dealer_id' => TestCase::getTestDealerId(),
        'dealer_location_id' => TestCase::getTestDealerLocationRandom(),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
        'updated_at_auto' => $createdAt,
        'active' => 1,
        'title' => !empty($showroom->title) ? $showroom->title : $faker->sentence,
        'stock' => Str::random(10),
        'manufacturer' => $mfg->name,
        'brand' => !empty($showroom->brand) ? $showroom->brand : (!empty($brand->name) ? $brand->name : ''),
        'model' => !empty($showroom->model) ? $showroom->model : $faker->title,
        'description' => !empty($showroom->description) ? $showroom->description : $faker->realText,
        'status' => 1,
        'category' => !empty($showroom->type) ? $showroom->type : $category->legacy_category,
        'vin' => Str::random(18),
        'msrp' => $msrp,
        'price' => $price,
        'year' => $faker->year,
        'condition' => $conditions[$condition],
        'notes' => $faker->realText,
        'is_archived' => 0,
        'showroom_id' => !empty($showroom->id) ? $showroom->id : null
    ];
});