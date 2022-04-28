<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Parts\Category;
use App\Models\Parts\Type;
use App\Models\Parts\CategoryImage;

class RemoveDumpTrucksCategory extends Migration
{
    const CATEGORY = [
      'name' => 'Dump Trucks',
      'image' => 'https://s3.amazonaws.com/crm-trailercentral-dev/dump-trucks-semi',
      'type_name' => 'Semi Trailers',
      'description' => 'A truck with a body that tilts or opens at the back for unloading.'
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $type = Type::where('name', self::CATEGORY['type_name'])->first();
      
      $type->categories()->where('name', self::CATEGORY['name'])->first()->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      $type = Type::where('name', self::CATEGORY['type_name'])->first();

      $category = Category::create([
        'name' => self::CATEGORY['name'],
        'description' => self::CATEGORY['description'],
      ]);
      
      $new_image = CategoryImage::create([
        'image_url'   => self::CATEGORY['image'],
        'category_id' => $category->id,
      ]);
      
      $type->categories()->save($category);
    }
}
