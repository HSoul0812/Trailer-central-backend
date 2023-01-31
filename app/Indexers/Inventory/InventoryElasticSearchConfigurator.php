<?php

namespace App\Indexers\Inventory;

use App\Indexers\IndexConfigurator;
use App\Transformers\Inventory\InventoryElasticSearchInputTransformer;
use ElasticAdapter\Indices\Settings;

class InventoryElasticSearchConfigurator extends IndexConfigurator
{
    public const TEXT_TYPE_MAX_SIZE = 32766;

    public const PROPERTIES = [
        'id' => ['type' => 'long'],
        'dealerId' => ['type' => 'integer'],
        'dealerLocationId' => ['type' => 'integer'],
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
                'normal' => [
                    'type' => 'text',
                    'analyzer' => 'standard',
                ]
            ]
        ],
        'title'                 => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'year'                  => ['type' => 'integer'],
        'manufacturer'          => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal'
        ],
        'brand'                => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal'
        ],
        'model'                => [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'description'          => [
            'type' => 'keyword',
            'normalizer' => 'case_normal',
            'ignore_above' => self::TEXT_TYPE_MAX_SIZE,
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'description_html'     => [
            'type' => 'keyword',
            'ignore_above' => self::TEXT_TYPE_MAX_SIZE,
            'normalizer' => 'case_normal'
        ],
        'status'               => ['type' => 'integer'],
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
            'type'   => 'keyword',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
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
                'txt' => ['type' => 'text', 'analyzer' => 'english']
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
                'txt' => ['type' => 'text', 'analyzer' => 'english']
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
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'featureList.stallTack'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'featureList.lq'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'featureList.doorsWindowsRamps'=> [
            'type'       => 'keyword',
            'normalizer' => 'case_normal',
            'fields' => [
                'txt' => ['type' => 'text', 'analyzer' => 'english']
            ]
        ],
        'image'                => ['type' => 'keyword'],
        'images'               => ['type' => 'keyword'],
        'imagesSecondary'      => ['type' => 'keyword'],
        'numberOfImages'       => ['type' => 'integer'],
        'widthInches' => ['type' => 'float'],
        'heightInches' => ['type' => 'float'],
        'lengthInches' => ['type' => 'float'],
        'widthDisplayMode' => ['type' => 'keyword'],
        'heightDisplayMode' => ['type' => 'keyword'],
        'lengthDisplayMode' => ['type' => 'keyword'],
        'tilt' => ['type' => 'integer'],
        'entity_type_id' => ['type' => 'integer'],
        'paymentCalculator.apr' => ['type' => 'float'],
        'paymentCalculator.down' => ['type' => 'float'],
        'paymentCalculator.years' => ['type' => 'integer'],
        'paymentCalculator.month' => ['type' => 'integer'],
        'paymentCalculator.monthly_payment' => ['type' => 'float'],
        'paymentCalculator.down_percentage' => ['type' => 'float'],
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
            ]
        ]);
    }

    public function __construct()
    {
        $this->transformer = new InventoryElasticSearchInputTransformer();
    }
}
