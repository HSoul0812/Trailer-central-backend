<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Category;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;

class PartsUpdateTest extends TestCase
{
    
    private $vendor;
    private $manufacturer;
    private $category;
    private $type;
    private $brand;
    
    public function __construct() {
        parent::__construct();   
    }
    
    /**
     * Test creating a part with all fields populated
     *
     * @return void
     */
    public function testUpdateAllFields()
    {            
        $this->initializeTestData();
        $data = $this->createPartTestData();
        
        $vendor = Vendor::latest()->first();
        $manufacturer = Manufacturer::latest()->first();
        $brand = Brand::latest()->first();
        $type = Type::latest()->first();
        $category = Category::latest()->first();
        
        $updateData = [
            "dealer_id" => 1002,
            "vendor_id" => $vendor->id,
            "manufacturer_id" => $manufacturer->id,
            "brand_id" => $brand->id,
            "type_id" => $type->id,
            "category_id" => $category->id,
            "subcategory" => "Testff",
            "sku" => "asdasdsad",
            "price" => 3,
            "dealer_cost" => 11,
            "msrp" => 21,
            "weight" => 22,
            "weight_rating" => 45 ,
            "description" => "zxczxc",
            "qty" => 4,
            "show_on_website" => 0,
            "is_vehicle_specific" => 1,
            "title" => "ASDASD",
        ];
        
        $this->json('POST', '/api/parts/'.$data['part']->id, $updateData) 
            ->seeJson([
                "dealer_id" => 1002,
                "vendor_id" => $vendor->toArray(),
                "manufacturer_id" => $manufacturer->toArray(),
                "brand_id" => $brand->toArray(),
                "type_id" => $type->toArray(),
                "category_id" => $category->toArray(),
                "subcategory" => "Testff",
                "sku" => "asdasdsad",
                "price" => 3,
                "dealer_cost" => 11,
                "msrp" => 21,
                "weight" => 22,
                "weight_rating" => 45 ,
                "description" => "zxczxc",
                "qty" => 4,
                "show_on_website" => false, // transformed values
                "is_vehicle_specific" => true, // transformed values
                "title" => "ASDASD"
            ]);             
    }
    
    public function testUpdateImages()
    {
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $partsRepository = new PartRepository();
        
        $data['data']['images'] = [            
            "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
            "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
            "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
            "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
            "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg"
        ];
        
        $data['data']['id'] = $data['part']->id;
        $part = $partsRepository->update($data['data']);
        
        $this->assertEquals($part->images->count(), 5);
    }
    
    private function initializeTestData() {
        $this->vendor = Vendor::first();
        $this->manufacturer = Manufacturer::first();
        $this->category = Category::first();
        $this->type = Type::first();
        $this->brand = Brand::first();
    }
    
    private function createPartTestData() {        
        $originalData = [
            "dealer_id" => 1001,
            "vendor_id" => $this->vendor->id,
            "manufacturer_id" => $this->manufacturer->id,
            "brand_id" => $this->brand->id,
            "type_id" => $this->type->id,
            "category_id" => $this->category->id,
            "subcategory" => "Test",
            "sku" => "12345",
            "price" => 13,
            "dealer_cost" => 16,
            "msrp" => 25,
            "weight" => 24,
            "weight_rating" => 55 ,
            "description" => "asdasdasd",
            "qty" => 3,
            "show_on_website" => 1,
            "is_vehicle_specific" => 0,
            "title" => "ddddd",
            "images" => [
                "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg"
            ]
        ];  
        
        $partsRepository = new PartRepository();
        $part = $partsRepository->create($originalData);
        
        return [
            'data' => $originalData,
            'part' => $part
        ];
    }
}