<?php

use App\Models\Parts\Category;
use App\Models\Parts\CategoryImage;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class RemoveLivestockCategory extends Migration
{
    public const CATEGORY = [
        'name' => 'Livestock Trailers',
        'image' => 'https://s3.amazonaws.com/crm-trailercentral-dev/livestock-trailers',
        'type_name' => 'Horse & Livestock',
        'mappings' => 'equip_livestock',
        'description' => 'Trailers used to transport live animals, including cattle, sheep, goats, horses, rabbits, and more.',
      ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        $type = Type::where('name', self::CATEGORY['type_name'])->first();

        $type->categories()->where('name', self::CATEGORY['name'])->first()->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $type = Type::where('name', self::CATEGORY['type_name'])->first();

        $category = Category::create([
          'name' => self::CATEGORY['name'],
          'description' => self::CATEGORY['description'],
        ]);

        $new_image = CategoryImage::create([
          'image_url' => self::CATEGORY['image'],
          'category_id' => $category->id,
        ]);

        CategoryMappings::create([
          'category_id' => $category->id,
          'map_from' => self::CATEGORY['name'],
          'map_to' => self::CATEGORY['mappings'],
          'type' => 'Inventory',
        ]);

        $type->categories()->save($category);
    }
}
