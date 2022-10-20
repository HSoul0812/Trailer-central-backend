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
use App\Models\Parts\Part;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Illuminate\Support\Str;

class PartsCreateTest extends TestCase
{

    private $vendor;
    private $manufacturer;
    private $category;
    private $type;
    private $brand;
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

        $this->putJson('/api/parts', $data, ['access-token' => $authToken->access_token])
            ->assertJsonFragment([
                'dealer_id' => $data['dealer_id'],
                'subcategory' => 'Test',
                'qty' => 3,
            ])
            ->assertJsonStructure([
                'data' => [
                    'bins' => [
                        '*' => [
                            'id',
                            'bin_id',
                            'part_id',
                            'qty',
                        ],
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

        $this->putJson('/api/parts', $data, ['access-token' => $authToken->access_token])
            ->assertStatus(422)
            ->assertJsonPath('errors', [
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
        $partsRepository = new PartRepository(new Part());
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
            "sku" => Str::random(),
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

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     * @throws \Exception
     */
    public function testIsActiveAndIsTaxableHasDefault()
    {
        $this->initializeTestData();
        $data = $this->createPartTestData();

        $data = Arr::except($data, ['bins', 'images']);

        $partsRepository = new PartRepository(new Part());
        $part = $partsRepository->create($data);

        $this->assertDatabaseHas(Part::getTableName(), [
            'id' => $part->getKey(),
            'dealer_id' => $part->dealer_id,
            'is_active' => 0,
            'is_taxable' => 1,
        ]);
    }
}
