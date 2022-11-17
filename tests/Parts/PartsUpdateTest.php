<?php

namespace Tests\Parts;

use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Category;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Part;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;
use App\Models\User\AuthToken;
use Tests\TestCase;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

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
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testUpdateAllFields()
    {
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $authToken = AuthToken::where('user_id', 1001)->first();

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
            "sku" => Str::random(),
            "price" => 3,
            "dealer_cost" => 11,
            "msrp" => 21,
            "weight" => 22,
            "weight_rating" => "45 lb" ,
            "description" => "zxczxc",
            "qty" => 4,
            "show_on_website" => 0,
            "is_vehicle_specific" => 1,
            "title" => "ASDASD",
            'vehicle_make' => 'test',
            'vehicle_model' => 'test',
            'vehicle_year_from' => 1990,
            'vehicle_year_to' => 2019,
            'bins' => [
                [
                    'bin_id' => 4,
                    'quantity' => 4
                ],
                [
                    'bin_id' => 3,
                    'quantity' => 4
                ],
            ],
            "alternative_part_number" => 'Test Alternative Part Number',
        ];

        $this->postJson('/api/parts/'.$data['part']->id, $updateData, ['access-token' => $authToken->access_token])
            ->assertJsonFragment([
                'dealer_id' => $authToken->user_id,
                'subcategory' => 'Testff',
                'qty' => 4,
            ])
            ->assertJsonStructure([
                'data' => [
                    'bins' => [
                        '*' => [
                            'id',
                            'bin_id',
                            'part_id',
                            'qty',
                        ]
                    ],
                    'vehicle_specific' => [
                        'id',
                        'make',
                        'model',
                        'year_from',
                        'year_to',
                    ],
                    'category' => [
                        'id',
                        'name',
                    ],
                    'type' => [
                        'id',
                        'name'
                    ],
                    'brand' => [
                        'id',
                        'name'
                    ],
                    'manufacturer' => [
                        'id',
                        'name'
                    ],
                    'vendor' => [
                        'id',
                        'dealer_id',
                        'name',
                        'show_on_part',
                        'show_on_inventory',
                        'show_on_floorplan',
                    ],
                ],
            ]);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testUpdateImages()
    {
        $this->initializeTestData();
        $data = $this->createPartTestData();
        $partsRepository = new PartRepository(new Part());

        $data['data']['images'] = [
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
            ],
            [
                'url' => "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                'position' => 3
            ],
            [
                'url' => "https://s3.amazonaws.com/distillery-trailercentral/c51ce410c124a10e0db5e4b97fc2af39/5da6675f8b1cd.jpg",
                'position' => 4
            ]
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
            "alternative_part_number" => 'Test Alternative Part Number',
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
                    'bin_id' => 7,
                    'quantity' => 2
                ],
                [
                    'bin_id' => 2,
                    'quantity' => 2
                ],
            ]
        ];

        $partsRepository = new PartRepository(new Part());
        $part = $partsRepository->create($originalData);

        return [
            'data' => $originalData,
            'part' => $part
        ];
    }
}
