<?php

use App\Models\Parts\Category;
use App\Models\Parts\CategoryImage;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class ReplaceCategories extends Migration
{
    public const CATEGORIES_REPLACED = ['Day Cab Trucks', 'Sleeper Cap Trucks'];
    public const TYPE_NAME = 'Semi Trailers';
    public const NEW_CATEGORY = ['name' => 'Standard Trucks', 'img' => 'https://s3.amazonaws.com/crm-trailercentral-dev/sleeper-cap-trucks', 'description' => 'Both day cab trucks and sleeper cab trucks.'];

    /**
     * Run the migrations.
     */
    public function up()
    {
        $type = Type::where('name', self::TYPE_NAME)->first();

        foreach (self::CATEGORIES_REPLACED as $category) {
            $category = Category::where('name', $category)->first();
            $type->categories()->detach($category->id);
        }

        $new_category = Category::create([
          'name' => self::NEW_CATEGORY['name'],
          'description' => self::NEW_CATEGORY['description'],
        ]);

        $new_image = CategoryImage::create([
          'image_url' => self::NEW_CATEGORY['img'],
          'category_id' => $new_category->id,
        ]);

        $type->categories()->save($new_category);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $type = Type::where('name', self::TYPE_NAME)->first();

        foreach (self::CATEGORIES_REPLACED as $category) {
            $category = Category::where('name', $category)->first();
            $type->categories()->attach($category->id);
        }

        $new_category = Category::where('name', self::NEW_CATEGORY['name'])->first();
        $new_image = CategoryImage::where('category_id', $new_category->id)->first();

        $type->categories()->detach($new_category->id);
        $new_category->delete();
        $new_image->delete();
    }
}
