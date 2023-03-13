<?php

namespace App\Indexers\Inventory;

use App\Indexers\IndexConfigurator;
use App\Transformers\Inventory\InventoryElasticSearchInputTransformer;
use ElasticAdapter\Indices\Settings;

class InventoryElasticSearchConfigurator extends IndexConfigurator
{
    public const TEXT_TYPE_MAX_SIZE = 32766;

    public const PROPERTIES = [
        'id' => ['type' => 'keyword'],
        'dealerId' => ['type' => 'keyword'],
        'dealerLocationId' => ['type' => 'keyword'],
        'createdAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        'updatedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        'isActive' => ['type' => 'boolean'],
        'isSpecial' => ['type' => 'boolean'],
        'isFeatured' => ['type' => 'boolean'],
        'isArchived' => ['type' => 'boolean'],
        'isClassified' => ['type' => 'boolean'],
        'archivedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        'updatedAtUser' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        'stock' => [
            'type' => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'title'                 => [
            'type' => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'year'                  => ['type' => 'integer'],
        'manufacturer'          => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'brand'                => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'model'                => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'description'          => [
            'type' => 'text',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'description_html'     => ['type' => 'text', 'index' => false],
        'status'               => ['type' => 'keyword'],
        'availability'         => ['type' => 'keyword'],
        'availabilityLabel'    => ['type' => 'keyword'],
        'typeLabel'            => ['type' => 'keyword'],
        'category'             => ['type' => 'keyword'],
        'categoryLabel'        => ['type' => 'keyword'],
        'vin'                  => ['type' => 'keyword'],
        'msrpMin'              => ['type' => 'float'],
        'msrp'                 => ['type' => 'float'],
        'useWebsitePrice'      => ['type' => 'boolean'],
        'websitePrice'         => ['type' => 'float'],
        'originalWebsitePrice' => ['type' => 'float'],
        'dealerPrice'          => ['type' => 'float'],
        'salesPrice'           => ['type' => 'float'],
        'basicPrice'           => ['type' => 'float'],
        'monthlyPrice'         => ['type' => 'float'],
        'monthlyRate'          => ['type' => 'float'],
        'existingPrice'        => ['type' => 'float'],
        'condition'            => ['type' => 'keyword'],
        'length'               => ['type' => 'float'],
        'width'                => ['type' => 'float'],
        'height'               => ['type' => 'float'],
        'weight'               => ['type' => 'float'],
        'gvwr'                 => ['type' => 'float'],
        'axleCapacity'         => ['type' => 'float'],
        'payloadCapacity'      => ['type' => 'float'],
        'costOfUnit'           => ['type' => 'keyword'],
        'costOfShipping'       => ['type' => 'keyword'],
        'costOfPrep'           => ['type' => 'keyword'],
        'totalOfCost'          => ['type' => 'keyword'],
        'minimumSellingPrice'  => ['type' => 'keyword'],
        'notes'                => [
            'type'   => 'text',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'showOnKsl'            => ['type' => 'boolean'],
        'showOnRacingjunk'     => ['type' => 'boolean'],
        'showOnWebsite'        => ['type' => 'boolean'],
        'ttPaymentExpirationDate' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
        'videoEmbedCode'       => ['type' => 'keyword'],
        'numAc'                => ['type' => 'integer'],
        'numAxles'             => ['type' => 'integer'],
        'numBatteries'         => ['type' => 'integer'],
        'numPassengers'        => ['type' => 'integer'],
        'numSleeps'            => ['type' => 'integer'],
        'numSlideouts'         => ['type' => 'integer'],
        'numStalls'            => ['type' => 'keyword'],
        'conversion'           => ['type' => 'keyword'],
        'customConversion'     => ['type' => 'keyword'],
        'shortwallFt'          => ['type' => 'float'],
        'color'                => ['type' => 'keyword'],
        'pullType'             => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'noseType'             => ['type' => 'keyword'],
        'roofType'             => ['type' => 'keyword'],
        'loadType'             => ['type' => 'keyword'],
        'fuelType'             => ['type' => 'keyword'],
        'frameMaterial'        => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'horsepower'           => ['type' => 'keyword'],
        'hasLq'                => ['type' => 'boolean'],
        'hasManger'            => ['type' => 'boolean'],
        'hasMidtack'           => ['type' => 'boolean'],
        'hasRamps'             => ['type' => 'boolean'],
        'mileage'              => ['type' => 'text'],
        'mileageMiles'         => ['type' => 'integer'],
        'mileageKilometres'    => ['type' => 'integer'],
        'isRental'             => ['type' => 'boolean'],
        'weeklyPrice'          => ['type' => 'float'],
        'dailyPrice'           => ['type' => 'float'],
        'floorplan'            => ['type' => 'keyword'],
        'cabType'              => ['type' => 'keyword'],
        'engineSize'           => ['type' => 'keyword'],
        'transmission'         => ['type' => 'keyword'],
        'driveTrail'           => ['type' => 'keyword'],
        'propulsion'           => ['type' => 'keyword'],
        'draft'                => ['type' => 'float'],
        'transom'              => ['type' => 'float'],
        'deadRise'             => ['type' => 'text'],
        'totalWeightCapacity'  => ['type' => 'text'],
        'wetWeight'            => ['type' => 'text'],
        'seatingCapacity'      => ['type' => 'text'],
        'hullType'             => ['type' => 'keyword'],
        'engineHours'          => ['type' => 'float'],
        'interiorColor'        => ['type' => 'keyword'],
        'hitchWeight'          => ['type' => 'text'],
        'cargoWeight'          => ['type' => 'text'],
        'freshWaterCapacity'   => ['type' => 'text'],
        'grayWaterCapacity'    => ['type' => 'text'],
        'blackWaterCapacity'   => ['type' => 'text'],
        'furnaceBtu'           => ['type' => 'text'],
        'acBtu'                => ['type' => 'text'],
        'electricalService'    => ['type' => 'text'],
        'availableBeds'        => ['type' => 'text'],
        'numberAwnings'        => ['type' => 'integer'],
        'awningSize'           => ['type' => 'text'],
        'axleWeight'           => ['type' => 'text'],
        'engine'               => ['type' => 'keyword'],
        'fuelCapacity'         => ['type' => 'text'],
        'sideWallHeight'       => ['type' => 'text'],
        'externalLink'         => ['type' => 'text'],
        'subtitle'             => ['type' => 'text'],
        'overallLength'        => ['type' => 'float'],
        'minWidth'             => ['type' => 'float'],
        'minHeight'            => ['type' => 'float'],
        'monthlyPrice2'        => ['type' => 'float'],
        'dealer.name'          => ['type' => 'keyword'],
        'dealer.email'         => ['type' => 'keyword'],
        'location.name'        => ['type' => 'keyword'],
        'location.contact'     => ['type' => 'keyword'],
        'location.website'     => ['type' => 'keyword'],
        'location.phone'       => ['type' => 'keyword'],
        'location.address'     => ['type' => 'keyword'],
        'location.city'        => ['type' => 'keyword'],
        'location.region'      => ['type' => 'keyword'],
        'location.postalCode'  => ['type' => 'keyword'],
        'location.country'     => ['type' => 'keyword'],
        'location.geo'         => ['type' => 'geo_point'],
        'keywords'             => ['type' => 'keyword'],
        'featureList.floorPlan'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'featureList.stallTack'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'featureList.lq'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'featureList.doorsWindowsRamps'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'tokens' => ['type' => 'text', 'analyzer' => 'shingle_analyzer']
            ]
        ],
        'image'                => ['type' => 'keyword'],
        'images'               => ['type' => 'keyword'],
        'imagesSecondary'      => ['type' => 'keyword'],
        'numberOfImages'       => ['type' => 'integer'],
        'widthInches'          => ['type' => 'float'],
        'heightInches'         => ['type' => 'float'],
        'lengthInches'         => ['type' => 'float'],
        'widthDisplayMode'     => ['type' => 'keyword'],
        'heightDisplayMode'    => ['type' => 'keyword'],
        'lengthDisplayMode'    => ['type' => 'keyword'],
        'tilt'                 => ['type' => 'integer'],
        'entity_type_id'       => ['type' => 'keyword'],
        'paymentCalculator.apr'     => ['type' => 'float', 'index' => false],
        'paymentCalculator.down'    => ['type' => 'float', 'index' => false],
        'paymentCalculator.years'   => ['type' => 'integer', 'index' => false],
        'paymentCalculator.month'   => ['type' => 'integer', 'index' => false],
        'paymentCalculator.monthly_payment' => ['type' => 'float', 'index' => false],
        'paymentCalculator.down_percentage' => ['type' => 'float', 'index' => false],
    ];

    public function name(): string
    {
        return $this->aliasName() . '_' . now()->format('Y_m_d_h_i_s');
    }

    public function aliasName(): string
    {
        return config('elastic.scout_driver.indices.inventory');
    }

    public function settings(): ?Settings
    {
        return (new Settings())->analysis([
            'normalizer' => [
                'case_normal' => [
                    'type' => 'custom',
                    'filter' => ['lowercase', 'asciifolding']
                ]
            ],
            'analyzer' => [
                'standard_analyzer'=>[ // it will generate standard tokens per word
                    'tokenizer' => 'standard_tokenizer',
                    'filter' => ['lowercase', 'asciifolding']
                ],
                'shingle_analyzer' => [ // it will generate standard 2-3 words
                    'tokenizer' => 'shingle_tokenizer',
                    'filter' => [
                        'lowercase',
                        'asciifolding',
                        'shingle_filter'
                    ]
                ]
            ],
            'tokenizer' => [
                'standard_tokenizer' => [
                    'type' => 'standard'
                ],
                'shingle_tokenizer' => [
                    'type' => 'standard'
                ]
            ],
            'filter' => [
                'shingle_filter' => [
                    'type' => 'shingle',
                    'min_shingle_size' => 2,
                    'max_shingle_size' => 3,
                    'output_unigrams' => true
                ]
            ]
        ])->index([
            'mapping.ignore_malformed' => true,
            'number_of_shards' => config('elastic.scout_driver.settings.inventory.number_of_shards'),
            'number_of_replicas' => config('elastic.scout_driver.settings.inventory.number_of_replicas'),
            'refresh_interval' => config('elastic.scout_driver.settings.inventory.refresh_interval')
       ]);
    }

    public function __construct()
    {
        $this->transformer = new InventoryElasticSearchInputTransformer();
    }
}
