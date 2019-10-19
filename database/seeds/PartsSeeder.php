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
        $i = 3000000;
               
        $vendors = [];
        $manufacturers = [];
        $brands = [];
        $types = [];
        $categories = [];
        $users = [];
        
        while(count($vendors) != 15) {
            $vendors[] = Vendor::all()->random();
        }
        
        while(count($manufacturers) != 15) {
            $manufacturers[] = Manufacturer::all()->random();
        }
        
        while(count($brands) != 15) {
            $brands[] = Brand::all()->random();
        }
        
        while(count($types) != 15) {
            $types[] = Type::all()->random();
        }
        
        while(count($categories) != 15) {
            $categories[] = Category::all()->random();
        }
        
        while(count($users) != 15) {
            $users[] = User::all()->random();
        }
        
        
        
        while ($i-- != 0) {            
            
            $rand = rand(0, 14);
            
            $partsRepository->create([
                "dealer_id" => $users[$rand]->dealer_id,
                "vendor_id" => $vendors[$rand]->id,
                "manufacturer_id" => $manufacturers[$rand]->id,
                "brand_id" => $brands[$rand]->id,
                "type_id" => $types[$rand]->id,
                "category_id" => $categories[$rand]->id,
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
