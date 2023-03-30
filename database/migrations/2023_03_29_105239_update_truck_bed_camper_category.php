<?php
use Illuminate\Database\Migrations\Migration;
use App\Models\Parts\Category;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\CategoryImage;
use App\Models\Parts\Type;
class UpdateTruckBedCamperCategory extends Migration
{
    const NEW_CATEGORY = ['name' => 'Truck Bed Campers', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/truck-bed-campers', 'description' => 'Truck Bed Campers.'];
    const TYPE_NAME = 'Truck Beds';
    const CATEGORY_NEW_MAPPINGS = [
        self::TYPE_NAME => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment'],
            ['map_from' => 'Truck Bed Campers', 'map_to' => 'rv.truck_camper;truck_camper'],
        ],
    ];
    const OLD_CATEGORY_MAPPINGS = [
        self::TYPE_NAME => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment'],
        ],
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create a new category
        $type = Type::where('name', self::TYPE_NAME)->first();
        $new_category = Category::create([
            'name' => self::NEW_CATEGORY['name'],
            'description' => self::NEW_CATEGORY['description']
        ]);
        $new_image = CategoryImage::create([
            'image_url'   =>  self::NEW_CATEGORY['image_url'],
            'category_id' => $new_category->id,
        ]);
        $type->categories()->save($new_category);
        // End - New category
        foreach (self::CATEGORY_NEW_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                $category_mapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->first();
                if ($category_mapping) {
                    $category_mapping->update(['map_to' => $category['map_to']]);
                } else {
                    CategoryMappings::create([
                        'category_id' => $current_category->id,
                        'map_from' => $category['map_from'],
                        'map_to'   => $category['map_to'],
                        'type'     => 'Inventory'
                    ]);
                }
            }
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $type = Type::where('name', self::TYPE_NAME)->first();
        $new_category = Category::where('name', self::NEW_CATEGORY['name'])->first();
        $new_image = CategoryImage::where('category_id', $new_category->id)->first();
        $type->categories()->detach($new_category->id);
        $new_category->delete();
        $new_image->delete();
        foreach (self::OLD_CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->update(['map_to' => $category['map_to']]);
            }
        }
    }
}