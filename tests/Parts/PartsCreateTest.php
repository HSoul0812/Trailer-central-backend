<?php

namespace Tests\Parts;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Category;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;
use App\Models\User\AuthToken;
use App\Models\Parts\BinQuantity;
use Tests\TestCase;

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
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testCreatePartAllFields()
    {            
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $authToken = AuthToken::where('user_id', 1001)->first();
        
        $this->json('PUT', '/api/parts', $data, ['access-token' => $authToken->access_token]) 
            ->seeJson([
                'dealer_id' => 1001,
                'vendor' => $this->vendor->toArray(),
                'brand' => $this->brand->toArray(),
                'type' => $this->type->toArray(),
                'category' => $this->category->toArray(),
                'subcategory' => "Test",
                'sku' => "12345",
                'price' => 13,
                'dealer_cost' => 16,
                'msrp' => 25,
                'weight' => 24,
                'weight_rating' => "55 lb",
                'description' => 'asdasdasd',
                'qty' => 3,
                'show_on_website' => true, // transformed value
                'is_vehicle_specific' => false, // transformed value
                'title' => 'ddddd',
                'alternative_part_number' => null
            ]);             
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
     public function testCreatePartMissingRequiredFields()
    {            
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $authToken = AuthToken::where('user_id', 1001)->first();
        
        unset($data['brand_id']);
        unset($data['type_id']);
        unset($data['category_id']);
        unset($data['sku']);
        unset($data['subcategory']);
        
        $this->json('PUT', '/api/parts', $data, ['access-token' => $authToken->access_token]) 
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

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     * @throws \Exception
     */
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
            "brand_id" => $this->brand->id,
            "type_id" => $this->type->id,
            "category_id" => $this->category->id,
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
            'alternative_part_number' => 'test alternative part number',
            "images" => [
                [
                    'url' => "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                    'position' => 0
                ],
                [
                    'url' => "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                    'position' => 1
                ],
                [
                    'url' => "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                    'position' => 2
                ]
            ],
            'video_embed_code' => 'zxczxczc',
            'bins' => [
                [
                    'bin_id' => 4,
                    'quantity' => 4
                ],
                [
                    'bin_id' => 3,
                    'quantity' => 4
                ],
            ]
        ];        
    }
}
