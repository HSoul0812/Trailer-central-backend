<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Type;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Vendor;
use App\Models\User\User;

class PartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $partsRepository = new PartRepository();
        $i = 1;
        
        while ($i-- != 0) {            
            echo "CREATING PART NUMBER $i ".PHP_EOL;
            
            $vendor = Vendor::all()->random();
            $manufacturer = Manufacturer::all()->random();
            $brand = Brand::all()->random();
            $type = Type::all()->random();
            $category = Category::all()->random();
            $user = User::all()->random();
            
            $part = $partsRepository->create([
                "dealer_id" => $user->dealer_id,
                "vendor_id" => $vendor->id,
                "manufacturer_id" => $manufacturer->id,
                "brand_id" => $brand->id,
                "type_id" => $type->id,
                "category_id" => $category->id,
                "subcategory" => "Test",
                "sku" => "12345",
                "price" => 13,
                "dealer_cost" => 16,
                "msrp" => 25,
                "weight" => 24,
                "weight_rating" => "55 lb" ,
                "description" => "asdasdasd",
                "qty" => 3,
                "show_on_website" => 1,
                "is_vehicle_specific" => 0,
                "title" => "ddddd",
                'video_embed_code' => 'zxczxczc',
                'created_at' => '2022-10-10'
            ]); 
            
        }
                   
    }
}
