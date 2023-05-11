<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Parts;

use App\Models\Parts\Category;
use App\Models\Parts\CategoryImage;
use App\Models\Parts\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryAndTypeSeeder extends Seeder
{
    private const TYPES_CATEGORIES = [
        'Equipment Trailers' => [
          ['name' => 'Cargo (Enclosed)', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed', 'description' => 'Transport goods, livestock, or other items from one location to another in a covered trailer.'],
          ['name' => 'Flatbed', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/flatbed', 'description' => 'A flat trailer with no roof or sides that typically transports heavy, oversized, or wide goods including machinery, supplies, or equipment.'],
          ['name' => 'Car Haulers / racing', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/car-haulers', 'description' => 'A type of trailer used to transport vehicles.'],
          ['name' => 'Tow Dollys', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/two-dollys', 'description' => 'An auxiliary axle assembly equipped with a tow bar that is used to tow a motor vehicle behind another motor vehicle.'],
          ['name' => 'Tilt', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/tilt', 'description' => 'A flat trailer that tilts allowing users to easily load and unload.'],
          ['name' => 'Motorcycle / Cycle', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/motorcycle-cycle', 'description' => 'Trailers used to transport motorcycles or one to be pulled by a motorcycle in order to carry additional gear.'],
          ['name' => 'ATV', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/atv', 'description' => 'Designed to haul ATVs, side by sides, small utility vehicles, and dirt bikes.'],
          ['name' => 'Watercraft', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/watercraft', 'description' => 'Used for hauling or transporting power boats, sailboats, wave runners, jet skis, or other type of vehicle that is used in or on water.'],
          ['name' => 'Snowmobile', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/snowmobile', 'description' => 'Trailers used to transport snowmobiles. They can be open, hybrid, or enclosed.'],
          ['name' => 'Utility', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/utility', 'description' => 'Trailers used to carry equipment and that can generally be used to handle several functions.'],
          ['name' => 'Dump', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/dump-trailer', 'description' => 'An open-box bed used to transport materials such as dirt or gravel for construction purposes.'],
          ['name' => 'Vending / Concession', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/vending-concession', 'description' => 'Stalls, stand-alone kiosks, or stands that are used to sell beverages and foods.'],
          ['name' => 'Office / Fiber Optic', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/office-fiberoptic', 'description' => 'A mobile office or workshop usually with necessary amenities to carry activities.'],
          ['name' => 'Other', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/other-trailers', 'description' => 'A variety of specialised trailers and trailer components.'],
        ],
        'Horse & Livestock' => [
          ['name' => 'Horse Trailers', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/horse-trailers', 'description' => 'Trailers ranging in size that are used to transport horses.'],
          ['name' => 'Livestock Trailers', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/livestock-trailers', 'description' => 'Trailers used to transport live animals, including cattle, sheep, goats, horses, rabbits, and more.'],
          ['name' => 'Stock / Stock Combo', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/stock-combo', 'description' => 'Trailers combine the rugged aspects of a livestock trailer with the conveniences of a horse trailer.'],
        ],
        'Travel Trailers' => [
          ['name' => 'Travel', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/travel', 'description' => 'A vehicle designed to be towed and used as living quarters for travel or recreation.'],
          ['name' => 'Fifth Wheels', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/fifth-wheels', 'description' => 'A fifth wheel is a large RV that tows in the bed of pickup truck. '],
          ['name' => 'Toy Haulers', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/toy-haulers', 'description' => 'A type of RV that features a garage in the rear with a large ramp-door for access.'],
          ['name' => 'Camper / RV', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/camper-rv', 'description' => 'A trailer designed to be drawn by a motor vehicle used for temporary living or sleeping accommodations'],
        ],
        'Semi Trailers' => [
          ['name' => 'Low Boy / Drop Deck', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/low-boy', 'description' => 'A flat bed platform semi-trailer with no roof, sides and doors, and it has two deck levels. The lower deck allows for hauling taller loads than a regular straight floor flatbed.'],
          ['name' => 'Dry Van', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/dry-van', 'description' => 'A semi-trailer that\'s fully enclosed to protect shipments from outside elements.'],
          ['name' => 'Flatbed', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/flatbed-semi', 'description' => 'A semi-trailer with no roof or sides that typically transports heavy, oversized, or wide goods including machinery, supplies, or equipment.'],
          ['name' => 'Grain', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/grain', 'description' => 'A semi trailer that is used to haul bulk commodity products, such as grain, for agricultural purposes.'],
          ['name' => 'Reefer', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/reefer', 'description' => 'A refrigerated trailer that is attached to a semi-truck in order to transport perishables and other temperature-sensitive goods.'],
          ['name' => 'Livestock', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/livestock-semi', 'description' => 'Semi-trailers used to transport live animals, including cattle, sheep, goats, horses, rabbits, and more.'],
          ['name' => 'Tank / Bulk', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/tank-bulk', 'description' => 'Semi-trailers equipped with a tank body that is used to transport gases or liquids such as oil, gasoline, or milk, in bulk.'],
          ['name' => 'Dump', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/dump-semi', 'description' => 'Open-box bed used to transport materials such as dirt or gravel for construction purposes.'],
          ['name' => 'Other', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/other-semi', 'description' => 'A variety of specialised semi-trailers and trailer components.'],
          ['name' => 'Day Cab Trucks', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/day-cab-trucks', 'description' => 'A truck with no sleeper cabin on its back and includes only a single compartment that is located over the engine portion of the truck.'],
          ['name' => 'Dump Trucks', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/dump-trucks-semi', 'description' => 'A truck with a body that tilts or opens at the back for unloading.'],
          ['name' => 'Sleeper Cap Trucks', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/sleeper-cap-trucks', 'description' => 'A compartment attached behind the cabin of a tractor unit used for rest or sleeping.'],
          ['name' => 'Other Trucks', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/other-trucks-semi', 'description' => 'A list of other available truck inventory.'],
        ],
        'Truck Beds' => [
          ['name' => 'Truck Beds', 'image_url' => 'https://s3.amazonaws.com/crm-trailercentral-dev/truck-beds', 'description' => 'A variety of specialised and flatbed floors for trucks.'],
        ],
    ];

    private const PLACEHOLDER_IMG_URL = 'https://crm-trailercentral-dev.s3.amazonaws.com/placeholder.png';

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanTables();

        foreach (self::TYPES_CATEGORIES as $type => $categories) {
            $new_type = Type::create([
              'name' => $type,
          ]);

            foreach ($categories as $key => $category) {
                $new_category = Category::create([
                  'name' => $category['name'],
                  'description' => $category['description'],
                ]);

                $new_image = CategoryImage::create([
                  'image_url' => $category['image_url'],
                  'category_id' => $new_category->id,
                ]);

                $new_type->categories()->save($new_category);
            }
        }
    }

    private function cleanTables(): void
    {
        Type::truncate();
        Category::truncate();
        CategoryImage::truncate();
        DB::table('part_category_part_type')->truncate();
    }
}
