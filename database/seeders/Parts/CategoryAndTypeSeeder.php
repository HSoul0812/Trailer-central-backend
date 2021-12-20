<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Parts;

use App\Models\Parts\Category;
use App\Models\Parts\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryAndTypeSeeder extends Seeder
{
    private const TYPES_CATEGORIES = [
        'Horse & Livestock' => ['Horse Trailers', 'Livestock Trailers', 'Stock Trailers'],
        'Travel Trailers'   => ['Travel Trailers', 'Fifth Wheels', 'Toy Haulers', 'Camper Trailers'],
        'Semi Trailers'     => ['Day Cab Trucks', 'Sleeper Cab Trucks', 'Dump Trucks', 'Lowboy Trailers', 'Drop Deck Trailers', 'Dry Van Trailers', 'Flatbed Semi Trailers', 'Grain Trailers', 'Reefer Trailers', 'Semi Stock Trailers', 'Tank Trailers', 'Other Trucks'],
        'Truck Beds'        => ['Truck Beds'],
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

            foreach ($categories as $category) {
                $new_category = Category::create([
                'name' => $category,
            ]);

                $new_type->categories()->save($new_category);
            }
        }
    }

    private function cleanTables(): void
    {
        Type::truncate();
        Category::truncate();
        DB::table('part_category_part_type')->truncate();
    }
}
