<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcEsInventory;
use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use App\Services\Inventory\ESQuery\SortOrder;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use JsonException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TrailerCentral\Sdk\Handlers\Search\Filters\Filter;
use TrailerCentral\Sdk\Handlers\Search\Filters\FilterGroup;
use TrailerCentral\Sdk\Handlers\Search\Filters\Operator;
use TrailerCentral\Sdk\Handlers\Search\Filters\Type as FilterType;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\GeoCoordinates;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\Geolocation;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\GeolocationRange;
use TrailerCentral\Sdk\Handlers\Search\Pagination;
use TrailerCentral\Sdk\Handlers\Search\Request;
use TrailerCentral\Sdk\Handlers\Search\Sorting\Sorting;
use TrailerCentral\Sdk\Handlers\Search\Sorting\SortingField;
use TrailerCentral\Sdk\Handlers\Search\Terms\Collection;
use TrailerCentral\Sdk\Handlers\Search\Terms\Range;
use TrailerCentral\Sdk\Resources\Search;
use TrailerCentral\Sdk\Sdk;

class InventorySDKService implements InventorySDKServiceInterface
{
    use Helpers;
    use CategoryMappingHelpers;

    public const PAGE_SIZE = 10;

    public const SALE_SCRIPT_ATTRIBUTE = 'sale';
    public const PRICE_SCRIPT_ATTRIBUTE = 'price';
    public const TILT_TRAILER_INVENTORY = 'Tilt Trailers';
    public const TERM_SEARCH_KEY_MAP = [
        'dealer_location_id' => 'dealerLocationId',
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
        'availability' => 'availability',
    ];
    public const RANGE_SEARCH_KEY_MAP = [
        'price' => 'existingPrice',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'gvwr' => 'gvwr',
        'payload_capacity' => 'payloadCapacity',
    ];

    public const DEFAULT_CATEGORY = [
        'name' => 'Other',
        'type_id' => 1,
        'type_label' => 'General Trailers',
    ];

    public const INVENTORY_SOLD = 'sold';
    public const INVENTORY_AVAILABLE = 'available';

    public const DEFAULT_DISTANCE = 300;
    public const DEFAULT_SORT = '+distance';
    public const DEFAULT_NO_LOCATION_SORT = '-createdAt';

    // location set by DW as default lat/lon
    public const DEFAULT_LAT_ON_DW = 39.8090;
    public const DEFAULT_LON_ON_DW = -98.5550;

    private Request $request;
    private Search $search;
    private FilterGroup $queryFilterGroup;
    private FilterGroup $postFilterGroup;

    private int $currentPage = 1;
    private int $perPage = self::PAGE_SIZE;

    public function __construct()
    {
        $this->request = new Request();
        $this->queryFilterGroup = new FilterGroup([], FilterType::QUERY);
        $this->postFilterGroup = new FilterGroup();

        $this->request->addFilterGroup($this->queryFilterGroup);
        $this->request->addFilterGroup($this->postFilterGroup);
        $this->request->withAggregationSize(config('inventory-sdk.aggregation_size'));

        $sdk = new Sdk(config('inventory-sdk.url'), [
            'headers' => [
                'access-token' => '',
                'sample_key' => '',
                'User-Agent' => $this->getUserAgent(),
            ],
            'verify' => false,
        ]);

        $this->search = new Search($sdk);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function list(array $params): TcEsResponseInventoryList
    {
        $this->addCommonFilters($params);
        $this->addCategories($params);
        $this->addImages($params);
        $this->addSearchTerms($params);
        $this->addRangeQueries($params);
        $this->addPagination($params);
        $this->addDealerFilter($params);

        $location = $this->addGeolocation($params);

        $this->addSorting($params, $location);

        $showQuery = boolval(Arr::get($params, 'x-show-query', 0));

        if ($showQuery) {
            $this->request->withDebug(true);
        }

        return $this->responseFromSDKResponse($showQuery);
    }

    /**
     * @throws GuzzleException
     */
    protected function responseFromSDKResponse(bool $debugEnabled = false): TcEsResponseInventoryList
    {
        $sdkResponse = $this->search->execute($this->request);

        $result = [];
        $hits = $sdkResponse->hits();
        foreach ($hits as $hit) {
            $esInventory = TcEsInventory::fromData($hit);
            $esInventory->type_id = $this->mapOldCategoryToNew($esInventory->category)['type_id'];

            $result[] = $esInventory;
        }

        $response = new TcEsResponseInventoryList();
        // TODO: check if aggregations is correct.
        $response->aggregations = $sdkResponse->aggregations();

        $response->inventories = new LengthAwarePaginator(
            $result,
            $sdkResponse->total(),
            $this->perPage,
            $this->currentPage
        );

        if ($debugEnabled) {
            $response->sdkPayload = $this->request->serialize();
            $response->esQuery = $sdkResponse->qaQuery();
        }

        return $response;
    }

    protected function addGeolocation(array $params): GeoCoordinates
    {
        $location = $this->getGeolocationInfo($params);

        if (isset($params['country'])) {
            $this->request->add('location_country', strtoupper($params['country']));
        } elseif ($location->lat() !== self::DEFAULT_LAT_ON_DW && $location->lon() !== self::DEFAULT_LON_ON_DW) {
            $distance = $params['distance'] ? (float) $params['distance'] : self::DEFAULT_DISTANCE;
            $location = new GeolocationRange($location->lat(), $location->lon(), $distance, GeolocationRange::UNITS_MILES, true);
        }

        $this->request->withGeolocation($location);

        return $location;
    }

    protected function addSorting(array $params, GeoCoordinates $location)
    {
        if (isset($params['is_random']) && $params['is_random']) {
            $this->request->withSorting(new Sorting([], [], true));
        } else {
            if (isset($params['sort'])) {
                $sort = $params['sort'];
            } elseif ($location->lat() !== self::DEFAULT_LAT_ON_DW && $location->lon() !== self::DEFAULT_LON_ON_DW) {
                $sort = self::DEFAULT_SORT;
            } else {
                $sort = self::DEFAULT_NO_LOCATION_SORT;
            }

            $sorts = explode(';', $sort);
            $sorting = new Sorting([]);
            foreach ($sorts as $s) {
                if (empty($s)) {
                    continue;
                }
                $order = new SortOrder($s);
                $sorting->addField(new SortingField($order->field, $order->direction));
            }

            $this->request->withSorting($sorting);
        }
    }

    protected function addCommonFilters(array $params)
    {
        $attributes = [
            self::SALE_SCRIPT_ATTRIBUTE => boolval($params['sale'] ?? 0),
            self::PRICE_SCRIPT_ATTRIBUTE => [],
        ];

        if ((!empty($params['price_min']) && $params['price_min'] > 0) || (!empty($params['price_max']) && $params['price_max'] > 0)) {
            $attributes[self::PRICE_SCRIPT_ATTRIBUTE] = [$params['price_min'] ?? null, $params['price_max'] ?? null];
        }

        $this->postFilterGroup->add(new Filter('sale_price_script', new Collection($attributes)));

        if (!empty($params['exclude_stocks'])) {
            $this->queryFilterGroup->add(
                new Filter(
                    'stock',
                    new Collection($params['exclude_stocks'], Operator::NOT_EQUAL)
                )
            );
        }

        $this->postFilterGroup->add(new Filter('classifieds_site', new Collection([true])));
        $this->postFilterGroup->add(new Filter(
            'availability', new Collection([self::INVENTORY_SOLD], Operator::NOT_EQUAL
            )));
        $this->postFilterGroup->add(new Filter('isRental', new Collection([false])));
    }

    protected function addDealerFilter(array $params)
    {
        if (!empty($params['dealer_id'])) {
            $this->request->withDealerIds(new Filter(
                'dealer_id', new Collection([$params['dealer_id']])
            ));
        }
    }

    protected function addSearchTerms(array $params)
    {
        foreach (self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
            if ($value = $params[$field] ?? null) {
                $this->postFilterGroup->add(new Filter(
                    $searchField, new Collection(explode(';', $value))
                ));
            }
        }
    }

    protected function addRangeQueries(array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            if (isset($params[$minFieldKey]) || isset($params[$maxFieldKey])) {
                $this->postFilterGroup->add(new Filter(
                    $searchField,
                    new Range($params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null)
                ));
            }
        }
    }

    protected function addPagination(array $params)
    {
        $this->currentPage = LengthAwarePaginator::resolveCurrentPage();
        $this->perPage = $params['per_page'] ?? self::PAGE_SIZE;

        $this->request->withPagination(new Pagination($this->perPage, $this->currentPage));
    }

    protected function addCategories(array $params)
    {
        $categories = $this->getMappedCategories(
            $params['type_id'] ?? null,
            $params['category'] ?? null
        );

        if (empty($categories)) {
            throw new HttpException(400, 'No category was selected');
        }

        $this->postFilterGroup->add(new Filter(
            'category', new Collection($categories)
        ));

        if (isset($params['category']) && $params['category'] === self::TILT_TRAILER_INVENTORY) {
            $categories = $this->getMappedCategories(
                $params['type_id'] ?? null,
                null
            );
            $this->postFilterGroup->add(new Filter(
                'tilt', new Collection([1])
            ));
            $this->postFilterGroup->add(new Filter(
                'category', new Collection($categories)
            ));
        }
    }

    protected function addImages(array $params)
    {
        if (isset($params['has_image']) && $params['has_image']) {
            $this->postFilterGroup->add(new Filter('empty_images', new Collection([false])));
        }
    }

    protected function getMappedCategories(?int $type_id, ?string $categories_string): array
    {
        $mapped_categories = [];
        if (isset($type_id)) {
            $type = Type::find($type_id);
            if ($categories_string) {
                $categories_array = explode(';', $categories_string);
                $categories = $type->categories()->whereIn('name', $categories_array)->get();
            } else {
                $categories = $type->categories;
            }

            foreach ($categories as $category) {
                if ($category->category_mappings) {
                    $mapped_categories = array_merge($mapped_categories, explode(';', $category->category_mappings->map_to));
                }
            }
        } else {
            foreach (CategoryMappings::all() as $mapping) {
                $mapped_categories = array_merge($mapped_categories, explode(';', $mapping->map_to));
            }
        }

        return array_unique($mapped_categories);
    }

    protected function getGeolocationInfo(array $params): GeoCoordinates
    {
        if (isset($params['lat']) && isset($params['lon'])) {
            return new Geolocation((float) $params['lat'], (float) $params['lon']);
        } elseif (
            isset($params['location']) &&
            $geolocation = $this->getGeolocationInfoFromLocation($params['location'])
        ) {
            return $geolocation;
        }
        // use coordinates provided by DW as the center of the US
        return new Geolocation(self::DEFAULT_LAT_ON_DW, self::DEFAULT_LON_ON_DW);
    }

    protected function getGeolocationInfoFromLocation(string $location): ?GeoCoordinates
    {
        $response = json_decode(
            $this->api->get('map_search/geocode', ['q' => $location]),
            true
        );
        if (count($response['data']) > 0) {
            $position = $response['data'][0]['position'];

            return new Geolocation((float) $position['lat'], (float) $position['lng']);
        }

        return null;
    }

    protected function getUserAgent(): string
    {
        return 'trailertrader-backend' . (request()->header('User-Agent') ? ';'.request()->header('User-Agent') : '');
    }
}
