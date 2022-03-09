<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
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
    const FIELD_UPDATED_AT = 'updatedAt';
    const ORDER_DESC = 'desc';
    const ORDER_ASC = 'asc';

    const DEFAULT_COUNTRY = 'USA';
    const PAGE_SIZE = 10;
    const TERM_SEARCH_KEY_MAP = [
        'stalls' => 'numStalls',
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'category' => 'category',
        'condition' => 'condition',
        'construction' => 'frameMaterial',
        'year' => 'year',
        'slideouts' => 'numSlideouts',
        'configuration' => 'loadType',
        'axles' => 'numAxles',
        'color' => 'color',
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
    ];

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
            $response->limits = \Cache::remember('filter/limits', self::ES_CACHE_EXPIRY, function () {
                return $this->getFilterLimits();
            });
            return $response;
        } else {
            throw new \Exception('Elastic search API responded with http code: ' . $res->getStatusCode());
        }
    }
    private function esSearchUrl(): string {
        $esIndex = self::ES_INDEX;
        return config('trailercentral.elasticsearch.url') . "/$esIndex/_search";
    }
    private function mapCategoryBuckets(array $buckets): array {
        foreach ($buckets as $key => &$value) {
            $old_category = $value['key'];
            if ($old_category) {
                $mapped_category = CategoryMappings::where('map_to', 'like', '%' . $old_category . '%')->first();
                if ($mapped_category) {
                    $value['key'] = $mapped_category->map_from;
                    $value['type_id'] = $mapped_category->category->types[0]->id;
                } else {
                    $value['key'] = self::DEFAULT_CATEGORY['name'];
                    $value['type_id'] = self::DEFAULT_CATEGORY['type_id'];
                }
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
        $this->buildTypeQuery($queryBuilder, $params);
        $this->buildTypeAggregations($queryBuilder, $params);
        $query = $queryBuilder->build();

        return \Cache::remember(json_encode($query), self::ES_CACHE_EXPIRY, function () use($esSearchUrl, $query){
            $res = $this->httpClient->post($esSearchUrl, [
                'json' => $query
            ]);
            $resJson = json_decode($res->getBody()->getContents(), true);
            return $resJson['aggregations'];
        });
    }

    private function getCategorizedAggregations($params) {
        $esSearchUrl = $this->esSearchUrl();
        $queryBuilder = new ESInventoryQueryBuilder();
        $this->buildCategoryQuery($queryBuilder, $params);
        $this->buildCategoryAggregations($queryBuilder, $params);
        $query = $queryBuilder->build();

        return \Cache::remember(json_encode($query), self::ES_CACHE_EXPIRY, function () use($esSearchUrl, $query){
            $res = $this->httpClient->post($esSearchUrl, [
                'json' => $query
            ]);
            $resJson = json_decode($res->getBody()->getContents(), true);
            return $resJson['aggregations'];
        });
    }

    private function getFilterLimits(): array {
        $configs = $this->sysConfigRepository->getAll(['key' => 'filter/']);
        $filter = [];
        foreach($configs as $config) {
            $keyParts = explode('/', $config['key']);
            array_shift($keyParts);
            $end = array_pop($keyParts);
            if(!in_array($end, ['min', 'max'])) {
                continue;
            }

            $subProp = &$filter;
            foreach($keyParts as $part) {
                if(!isset($subProp[$part])) {
                    $subProp[$part] = [];
                }
                $subProp = &$subProp[$part];
            }
            $subProp[$end] = intval($config['value']);
        }
        return $filter;
    }

    #[ArrayShape(['from' => "int", 'size' => "int", 'query' => "array[]", 'aggregations' => "array"])]
    private function buildSearchQuery(array $params): ESInventoryQueryBuilder {
        $queryBuilder = new ESInventoryQueryBuilder();

        $this->buildTermQueries($queryBuilder, $params);
        $this->buildRangeQueries($queryBuilder, $params);
        $this->buildPaginateQuery($queryBuilder, $params);
        $this->buildFilter($queryBuilder, $params);

        $location = $this->getGeolocation($params);
        if($location) {
            $this->buildGeoFiltering($queryBuilder, $location, $params['distance']);
        }

        $queryBuilder->orderBy(self::FIELD_UPDATED_AT, self::ORDER_DESC);
        return $queryBuilder;
    }

    private function buildFilter(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $filter = "doc['location.address'].value != '---' && doc['websitePrice'].value != 0";
        if(!empty($params['is_sale'])) {
            $filter .= "
            && doc['salesPrice'].value > 0.0 && doc['salesPrice'].value < doc['websitePrice'].value
            ";
        }
        $queryBuilder->setFilterScript([
            'source' => $filter,
            'lang' => 'painless'
        ]);
    }

    private function buildGeoFiltering(
        ESInventoryQueryBuilder $queryBuilder,
        Geolocation             $location,
        string                  $distance
    ) {
        $queryBuilder->geoFiltering(['lat' => $location->latitude, 'lon' => $location->longitude], $distance);
    }

    private function buildTypeAggregations(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $queryBuilder->filterAggregate([
            'pull_type' => ['terms' => ['field' => 'pullType']],
            'color' => ['terms' => ['field' => 'color']],
            'year' => ['terms' => ['field' => 'year']],
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
    private function buildCategoryAggregations(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $queryBuilder->filterAggregate([
            'manufacturer' => ['terms' => ['field' => 'manufacturer']],
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

    private function buildPaginateQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $queryBuilder->paginate($currentPage, $params['per_page'] ?? self::PAGE_SIZE);
    }

    private function buildTermQueries(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $queryBuilder->termQuery('isRental', false);
        foreach(self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
            if (isset($params['type_id']) && $searchField == 'category') {
              $mapped_categories = $this->getMappedCategories(
                  $params['type_id'],
                  $params[$field] ?? null
              );
              $queryBuilder->termQueries($searchField, $mapped_categories);
            } else {
              $queryBuilder->termQueries($searchField, $params[$field] ?? null);
            }
        }
    }

    private function buildCategoryQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $mapped_categories = $this->getMappedCategories(
            $params['type_id'],
            $params['category'] ?? null
        );
        $queryBuilder->termQueries('category', $mapped_categories);
    }

    private function buildTypeQuery(ESInventoryQueryBuilder $queryBuilder, array $params) {
        $mapped_categories = $this->getMappedCategories(
            $params['type_id'],
            null
        );
        $queryBuilder->termQueries('category', $mapped_categories);
    }

    private function buildRangeQueries(ESInventoryQueryBuilder $queryBuilder, array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            $queryBuilder->rangeQuery($searchField, $params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null);
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
        $url = config('services.trailercentral.api') . 'inventory/' . $id . '?include=features';
        $inventory = $this->handleHttpRequest('GET', $url);

        return TcApiResponseInventory::fromData($inventory['data']);
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
