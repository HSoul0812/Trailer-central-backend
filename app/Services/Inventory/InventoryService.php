<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use App\Services\Inventory\ESQuery\ESBoolQueryBuilder;
use App\Services\Inventory\ESQuery\ESInventoryQueryBuilder;
use App\Services\Inventory\ESQuery\SortOrder;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\DTOs\Inventory\TcEsInventory;
use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\Models\Geolocation\Geolocation;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Pagination\LengthAwarePaginator;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class InventoryService implements InventoryServiceInterface
{
    const ES_INDEX = 'inventoryclsf';
    const HTTP_SUCCESS = 200;
    const ES_CACHE_EXPIRY = 300;
    const DEFAULT_SORT = '+distance';
    const DEFAULT_NO_LOCATION_SORT = '-createdAt';
    const DEFAULT_COUNTRY = 'USA';
    const PAGE_SIZE = 10;
    const TERM_SEARCH_KEY_MAP = [
        'stalls' => 'numStalls',
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'condition' => 'condition',
        'construction' => 'frameMaterial',
        'year' => 'year',
        'slideouts' => 'numSlideouts',
        'configuration' => 'loadType',
        'axles' => 'numAxles',
        'color' => 'color',
        'availability' => 'availability'
    ];

    const RANGE_SEARCH_KEY_MAP = [
        'price' => 'existingPrice',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'gvwr' => 'gvwr',
        'payload_capacity' => 'payloadCapacity'
    ];

    const DEFAULT_CATEGORY = [
      'name'      => 'Other',
      'type_id'   => 1,
      'type_label' => 'Equipment Trailers'
    ];

    const INVENTORY_SOLD = 'sold';
    const INVENTORY_AVAILABLE = 'available';

    public function __construct(
        private GuzzleHttpClient $httpClient,
        private SysConfigRepositoryInterface $sysConfigRepository,
    )
    {}

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function list(array $params): TcEsResponseInventoryList
    {
        $esSearchUrl = $this->esSearchUrl();

        $queryBuilder = $this->buildSearchQuery($params);
        $res = $this->httpClient->post($esSearchUrl, [
            'json' => $queryBuilder->build()
        ]);

        if($res->getStatusCode() == self::HTTP_SUCCESS) {
            $result = [];
            $resJson = json_decode($res->getBody()->getContents(), true);
            foreach($resJson['hits']['hits'] as $hit) {
                $result[] = TcEsInventory::fromData($hit['_source']);
            }

            $paginator = new LengthAwarePaginator(
                $result,
                $resJson['hits']['total'],
                $queryBuilder->getPageSize(),
                $queryBuilder->getPage()
            );

            $response = new TcEsResponseInventoryList();
            $response->aggregations = array_merge(
                $this->getCategorizedAggregations($params),
                $this->getTypedAggregations($params)
            );
            $response->inventories = $paginator;
            return $response;
        } else {
            throw new \Exception('Elastic search API responded with http code: ' . $res->getStatusCode());
        }
    }
    private function esSearchUrl(): string {
        $esIndex = self::ES_INDEX;
        return config('trailercentral.elasticsearch.url') . "/$esIndex/_search";
    }

    #[ArrayShape(["key" => "string", "type_id" => "int"])]
    private function mapOldCategoryToNew($oldCategory): array
    {
        return \Cache::remember('category/' . $oldCategory, self::ES_CACHE_EXPIRY, function() use ($oldCategory) {
            $value = [];
            $mappedCategories = CategoryMappings::where('map_to', 'like', '%' . $oldCategory . '%')->get();
            $mappedCategory = null;

            foreach ($mappedCategories as $currentCategory) {
              $mapToCategories = explode(';', $currentCategory->map_to);
              foreach ($mapToCategories as $mapToCategory) {
                if ($mapToCategory == $oldCategory) {
                  $mappedCategory = $currentCategory;
                }
              }
            }

            if ($mappedCategory && $mappedCategory->category) {
                $value['key'] = $mappedCategory->map_from;
                $value['type_id'] = $mappedCategory->category->types[0]->id;
                $value['type_label'] = $mappedCategory->category->types[0]->name;
            } else {
                $value['key'] = self::DEFAULT_CATEGORY['name'];
                $value['type_id'] = self::DEFAULT_CATEGORY['type_id'];
                $value['type_label'] = self::DEFAULT_CATEGORY['type_label'];
            }
            return $value;
        });
    }

    private function mapCategoryBuckets(array $buckets): array {
        foreach ($buckets as $key => &$value) {
            $oldCategory = $value['key'];
            if ($oldCategory) {
                $mappedCategory = $this->mapOldCategoryToNew($oldCategory);
                $value['key'] = $mappedCategory['key'];
                $value['type_id'] = $mappedCategory['type_id'];
            }
        }

        $newArr = [];
        $finalArr = [];
        foreach($buckets as $arr) {
            if (isset($newArr[$arr['key']])) {
                $newArr[$arr['key']] += $arr['doc_count'];
            } else {
                $newArr[$arr['key']] = $arr['doc_count'];
            }
        }

        foreach($buckets as $arr) {
            if (isset($newArr[$arr['key']])) {
                $finalArr[] = ['key' => $arr['key'], 'doc_count' => $newArr[$arr['key']], 'type_id' => $arr['type_id']];
            }
        }

        return array_values(array_unique($finalArr, SORT_REGULAR));
    }

    private function getTypedAggregations($params) {
        $esSearchUrl = $this->esSearchUrl();
        $queryBuilder = new ESInventoryQueryBuilder();
        $this->addTypeAggregationQuery($queryBuilder, $params);
        $query = $queryBuilder->build();

        return \Cache::remember(json_encode($query), self::ES_CACHE_EXPIRY, function () use($esSearchUrl, $query){
            $res = $this->httpClient->post($esSearchUrl, [
                'json' => $query
            ]);
            $resJson = json_decode($res->getBody()->getContents(), true);
            $resJson['aggregations']['category']['buckets'] = $this->mapCategoryBuckets(
                $resJson['aggregations']['category']['buckets']
            );
            return $resJson['aggregations'];
        });
    }

    private function getCategorizedAggregations($params) {
        $esSearchUrl = $this->esSearchUrl();
        $queryBuilder = new ESInventoryQueryBuilder();
        $this->addCategoryAggregationQuery($queryBuilder, $params);
        $query = $queryBuilder->build();

        return \Cache::remember(json_encode($query), self::ES_CACHE_EXPIRY, function () use($esSearchUrl, $query){
            $res = $this->httpClient->post($esSearchUrl, [
                'json' => $query
            ]);
            $resJson = json_decode($res->getBody()->getContents(), true);
            return $resJson['aggregations'];
        });
    }

    #[ArrayShape(['from' => "int", 'size' => "int", 'query' => "array[]", 'aggregations' => "array"])]
    private function buildSearchQuery(array $params): ESInventoryQueryBuilder {
        $queryBuilder = new ESInventoryQueryBuilder();
        $this->addCommonFilter($queryBuilder);
        $this->addCategoryQuery($queryBuilder, $params);
        $this->addTermSearchQueries($queryBuilder, $params);
        $this->addRangeQueries($queryBuilder, $params);
        $this->addPaginateQuery($queryBuilder, $params);
        $this->addScriptFilter($queryBuilder, $params);
        $this->addGeoFiltering($queryBuilder, $params);

        if(isset($params['sort'])) {
            $sort = $params['sort'];
        } else if($this->getGeolocation($params)) {
            $sort = self::DEFAULT_SORT;
        } else {
            $sort = self::DEFAULT_NO_LOCATION_SORT;
        }

        $sortObj = new SortOrder($sort);
        $queryBuilder->orderBy($sortObj->field, $sortObj->direction);
        return $queryBuilder;
    }

    private function addScriptFilter(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $priceDef = "double price;
                        if(doc[\'websitePrice\'] != null){ price = doc[\'websitePrice\'].value; }
                        if(0 < doc[\'salesPrice\'].value && doc[\'salesPrice\'].value < price) { price = doc[\'salesPrice\'].value; }";

        $filter = "doc['status'].value != 2 && doc['dealer.name'].value != 'Operate Beyond'";

        if(!empty($params['sale'])) {
            $filter .= " && doc['salesPrice'].value > 0.0 && doc['salesPrice'].value < doc['websitePrice'].value";
        }

        if(!empty($params['price_min']) && $params['price_min'] > 0 && !empty($params['price_max'])) {
            $filter = $priceDef . $filter;

            $filter .= " && price  > " . $params['price_min'] . "&& price < " . $params['price_max'];
        }

        $queryBuilder->setFilterScript([
            'source' => $filter,
            'lang' => 'painless'
        ]);
    }

    private function addGeoFiltering(
        ESInventoryQueryBuilder $queryBuilder,
        array $params,
    ) {
        $distance = null;
        if(isset($params['country'])) {
            $queryBuilder->addTermQuery('location.country', strtoupper($params['country']));
        } else {
            $distance = $params['distance'] ?? '300mi';
        }

        $location = $this->getGeolocation($params);
        if($location !== null) {
            $queryBuilder->setGeoDistance(['lat' => $location->latitude, 'lon' => $location->longitude], $distance);
        }

    }

    private function addCategoryAggregationQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $this->addCategoryQuery($queryBuilder, $params);
        $this->addCommonFilter($queryBuilder);
        $this->addScriptFilter($queryBuilder, []);
        $queryBuilder->setFilterAggregate([
            'manufacturer' => ['terms' => ['field' => 'manufacturer', 'size' => 10000]],
        ]);
    }

    private function addTypeAggregationQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $mappedCategories = $this->getMappedCategories(
            $params['type_id'],
            null
        );
        $queryBuilder->addTermInValuesQuery('category', $mappedCategories);
        $this->addCommonFilter($queryBuilder);
        $this->addScriptFilter($queryBuilder, []);
        $queryBuilder->setFilterAggregate([
            'pull_type' => ['terms' => ['field' => 'pullType']],
            'color' => ['terms' => ['field' => 'color']],
            'year' => ['terms' => ['field' => 'year', 'size' => 50]],
            'configuration' => ['terms' => ['field' => 'loadType']],
            'slideouts' => ['terms' => ['field' => 'numSlideouts']],
            'length' => ['stats' => ['field' => 'length']],
            'height_inches' => ['stats' => ['field' => 'heightInches']],
            'axles' => ['terms' => ['field' => 'numAxles']],
            'condition' => ['terms' => ['field' => 'condition']],
            'length_inches' => ['stats' => ['field' => 'lengthInches']],
            'price' => ['stats' => ['field' => 'existingPrice']],
            'width' => ['stats' => ['field' => 'width']],
            'width_inches' => ['stats' => ['field' => 'widthInches']],
            'dealer_location_id' => ['terms' => ['field' => 'dealerLocationId']],
            'construction' => ['terms' => ['field' => 'frameMaterial']],
            'category' => ['terms' => ['field' => 'category']],
            'stalls' => ['terms' => ['field' => 'numStalls']],
            'height' => ['stats' => ['field' => 'height']],
            'gvwr' => ['stats' => ['field' => 'gvwr']],
            'payload_capacity' => ['stats' => ['field' => 'payloadCapacity']],
        ]);
    }

    private function getMappedCategories(int $type_id, ?string $categories_string): string
    {
        $type = Type::find($type_id);
        $mapped_categories = "";
        if ($categories_string) {
            $categories_array = explode(';',$categories_string);
            $categories = $type->categories()->whereIn('name', $categories_array)->get();

        } else {
            $categories = $type->categories;
        }

        foreach ($categories as $category) {
            if ($category->category_mappings) {
                $mapped_categories = $mapped_categories . $category->category_mappings->map_to . ';';
            }
        }
        return rtrim($mapped_categories, ";");
    }

    private function addPaginateQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $queryBuilder->paginate($currentPage, $params['per_page'] ?? self::PAGE_SIZE);
    }

    private function addTermSearchQueries(ESInventoryQueryBuilder $queryBuilder, array $params) {
        foreach(self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
          $queryBuilder->addTermInValuesQuery($searchField, $params[$field] ?? null);
        }
    }

    private function addCommonFilter(ESInventoryQueryBuilder $queryBuilder) {
        $queryBuilder->addTermQuery('isRental', false);
        $queryBuilder->addTermQuery(
            'availability',
            self::INVENTORY_SOLD,
            ESInventoryQueryBuilder::OCCUR_MUST_NOT
        );
    }

    private function addCategoryQuery(ESInventoryQueryBuilder $queryBuilder, array $params)
    {
        $mappedCategories = $this->getMappedCategories(
            $params['type_id'],
            $params['category'] ?? null
        );

        $categoryQueries = $queryBuilder->buildTermInValuesQuery('category', $mappedCategories);
        if(isset($params['category']) && $params['category'] === 'Tilt') {
            $mappedTypeCategories = $this->getMappedCategories(
                $params['type_id'],
                null
            );

            $tiltQuery = (new ESBoolQueryBuilder())
                ->must($queryBuilder->buildTermQuery("tilt", 1))
                ->should($queryBuilder->buildTermInValuesQuery("category", $mappedTypeCategories))
                ->build();
            $categoryQueries[] = $tiltQuery;
        }
        $queryBuilder
            ->addQueryToContext(
                (new ESBoolQueryBuilder())->should($categoryQueries)->build()
            );
    }


    private function addRangeQueries(ESInventoryQueryBuilder $queryBuilder, array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            $queryBuilder->addRangeQuery($searchField, $params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null);
        }
    }

    private function getGeolocation(array $params): ?Geolocation {
        if(isset($params['lat']) && isset($params['lon'])) {
            return new Geolocation([
                'latitude' => (float)$params['lat'],
                'longitude' => (float)$params['lon']
            ]);
        }
        return null;
    }

    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id): TcApiResponseInventory
    {
        $url = config('services.trailercentral.api') . 'inventory/' . $id . '?include=features,attributes';
        $inventory = $this->handleHttpRequest('GET', $url);

        $respObj = TcApiResponseInventory::fromData($inventory['data']);
        $newCategory = $this->mapOldCategoryToNew($respObj->category);
        $respObj->category = $newCategory['key'];
        $respObj->type_id = $newCategory['type_id'];
        $respObj->type_label = $newCategory['type_label'];
        return $respObj;
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return array
     */
    #[ArrayShape([
        'data' => [[
            'title'   => 'string',
            'id'               => 'int',
            'payload_capacity' => 'float',
            'url'              => 'string',
            'description'      => 'string',
            'weight'           => 'float',
            'width'            => 'float',
            'height'           => 'float',
            'length'           => 'float',
            'manufacturer'     => 'string',
            'created_at'       => 'string',
            'price'            => 'float',
            'sales_price'      => 'float',
            'website_price'    => 'float',
            'images' => [
                'image_id'     => 'int',
                'is_default'   => 'int',
                'is_secondary' => 'int',
                'position'     => 'int',
                'url'          => 'string',
            ],
            'dealer' => [
                'id'            => 'int',
                'identifier'    => 'string',
                'created_at'    => 'string',
                'name'          => 'string',
                'email'         => 'string',
                'profile_image' => 'string',
            ],
            'features' => [
                'feature_list_id' => 'int',
                'value'           => 'string',
                'feature_name'    => 'string',
            ],
            'dealer_location' => [
                'id'         => 'int',
                'identifier' => 'string',
                'contact'    => 'string',
                'name'       => 'string',
                'website'    => 'string',
                'phone'      => 'string',
                'fax'        => 'string',
                'address'    => 'string',
                'city'       => 'string',
                'county'     => 'string',
                'region'     => 'string',
                'postal'     => 'string',
                'postalcode' => 'string',
                'country'    => 'string',
                'federal_id' => 'string',
                'sales_tax'  => 'array',
            ],
            'primary_image' => [
                'image_id'     => 'int',
                'is_default'   => 'int',
                'is_secondary' => 'int',
                'position'     => 'int',
                'url'          => 'string',
            ],
        ]],
    ])]
    private function handleHttpRequest(string $method, string $url): array
    {
        try {
            $response = $this->httpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}
