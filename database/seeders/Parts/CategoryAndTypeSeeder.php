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
        'Equipment Trailers' => ['Cargo Trailers (Enclosed)', 'Flatbed Trailers', 'Car Haulers', 'Tilt Trailers', 'Utility Trailers', 'Equipment Trailers', 'Dump Trailers', 'Car / Racing Trailers', 'Snowmobile', 'ATV Trailers', 'Watercraft Trailers', 'Concession Trailers (Vending / Concession)', 'Tow Dollys', 'Fiber Optic Trailers', 'Motorcycle Trailers', 'Other Trailers', 'Cycle Trailers'],
        'Horse & Livestock'  => ['Horse Trailers', 'Livestock Trailers', 'Stock Trailers'],
        'Travel Trailers'    => ['Travel Trailers', 'Fifth Wheels', 'Toy Haulers', 'Camper Trailers'],
        'Semi Trailers'      => ['Day Cab Trucks', 'Sleeper Cab Trucks', 'Dump Trucks', 'Lowboy Trailers', 'Drop Deck Trailers', 'Dry Van Trailers', 'Flatbed Semi Trailers', 'Grain Trailers', 'Reefer Trailers', 'Semi Stock Trailers', 'Tank Trailers', 'Other Trucks'],
        'Truck Beds'         => ['Truck Beds'],
    ];

    private const PLACEHOLDER_IMG_URL = 'https://crm-trailercentral-dev.s3.amazonaws.com/placeholder.png';

    private const UNIQUE_PLACEHOLDER_IMAGES = [
      'Equipment Trailers' => ['https://s3.amazonaws.com/crm-trailercentral-dev/cargo-trailers-enclosed.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/flatbed-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/car-haulers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/tilt-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/utility-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/equipment-trailers.png',
      'https://s3.amazonaws.com/crm-trailercentral-dev/dump-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/car-racing.jpg', 'https://s3.amazonaws.com/crm-trailercentral-dev/snowmobile.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/atv-trailer.jpg', 'https://s3.amazonaws.com/crm-trailercentral-dev/watercraft-trailer.jpeg', 'https://s3.amazonaws.com/crm-trailercentral-dev/concession-trailers.jpeg', 'https://s3.amazonaws.com/crm-trailercentral-dev/tow-dolly.jpg',
      'https://s3.amazonaws.com/crm-trailercentral-dev/fiber-trailer.jpeg', 'https://s3.amazonaws.com/crm-trailercentral-dev/motorcycle-trailer.jpeg', 'https://s3.amazonaws.com/crm-trailercentral-dev/other-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/cycle-trailers.png', ],
      'Horse & Livestock' => ['https://s3.amazonaws.com/crm-trailercentral-dev/horse-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/livestock-trailers.jpeg', 'https://s3.amazonaws.com/crm-trailercentral-dev/stock-trailers.png'],
      'Travel Trailers'   => ['https://s3.amazonaws.com/crm-trailercentral-dev/travel-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/fifth-wheels-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/toy-haulers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/camper-trailers.png'],
      'Semi Trailers'     => ['https://s3.amazonaws.com/crm-trailercentral-dev/day-cab-trucks.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/sleeper-cab-trucks.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/dump-trucks.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/low-boy-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/drop-deck-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/dry-van-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/flatbed-semi-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/grain-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/reefer-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/semi-stock-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/tank-trailers.png', 'https://s3.amazonaws.com/crm-trailercentral-dev/other-trucks.png'],
      'Truck Beds'        => ['https://s3.amazonaws.com/crm-trailercentral-dev/truck-beds-trailers.png'],
    ];

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
                  'name'        => $category,
                  'description' => $category . ' is the best trailer in the world.',
                ]);

                $new_image = CategoryImage::create([
                  'image_url'   => self::UNIQUE_PLACEHOLDER_IMAGES[$type][$key],
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
