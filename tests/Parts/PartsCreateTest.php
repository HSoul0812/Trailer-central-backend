<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Category;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;

class PartsCreateTest extends TestCase
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
    public function testCreatePartAllFields()
    {            
        $this->initializeTestData();
        $data = $this->createPartTestData();
        
        $this->json('PUT', '/api/parts', $data) 
            ->seeJson([
                'dealer_id' => 1001,
                'vendor_id' => $this->vendor->toArray(),
                'manufacturer_id' => $this->manufacturer->toArray(),
                'brand_id' => $this->brand->toArray(),
                'type_id' => $this->type->toArray(),
                'category_id' => $this->category->toArray(),
                'subcategory' => "Test",
                'sku' => "12345",
                'price' => 13,
                'dealer_cost' => 16,
                'msrp' => 25,
                'weight' => 24,
                'weight_rating' => 55,
                'description' => 'asdasdasd',
                'qty' => 3,
                'show_on_website' => true, // transformed value
                'is_vehicle_specific' => false, // transformed value
                'title' => 'ddddd'
            ]);             
    }
    
     public function testCreatePartMissingRequiredFields()
    {            
        $this->initializeTestData();
        $data = $this->createPartTestData();
        
        unset($data['brand_id']);
        unset($data['type_id']);
        unset($data['category_id']);
        unset($data['sku']);
        unset($data['subcategory']);
        
        $this->json('PUT', '/api/parts', $data) 
            ->seeJson([
                'brand_id' => [
                    'The brand id field is required.'
                ],
                'type_id' => [
                    'The type id field is required.'
                ],
                'category_id' => [
                    'The category id field is required.'
                ],
                'sku' => [
                    'The sku field is required.'
                ],
                'subcategory' => [
                    'The subcategory field is required.'
                ]
            ]);             
    }
    
    public function testCreatePartImageUpload()
    {
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $partsRepository = new PartRepository();
        $part = $partsRepository->create($data);
        $imageCount = count($data['images']);
        
        $this->assertEquals($part->images->count(), $imageCount);
    }
    
    private function initializeTestData() {
        $this->vendor = Vendor::first();
        $this->manufacturer = Manufacturer::first();
        $this->category = Category::first();
        $this->type = Type::first();
        $this->brand = Brand::first();
    }
    
    private function createPartTestData() {        
        return [
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
    }
}