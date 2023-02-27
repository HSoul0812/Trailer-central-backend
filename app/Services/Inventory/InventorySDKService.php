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
use Symfony\Component\HttpKernel\Exception\HttpException;
use TrailerCentral\Sdk\Handlers\Search\Filters\Operator;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\GeoCoordinates;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\Geolocation;
use TrailerCentral\Sdk\Handlers\Search\Geolocation\GeolocationRange;
use TrailerCentral\Sdk\Handlers\Search\Pagination;
use TrailerCentral\Sdk\Handlers\Search\Request;
use TrailerCentral\Sdk\Handlers\Search\Response;

use TrailerCentral\Sdk\Handlers\Search\Sorting\Sorting;
use TrailerCentral\Sdk\Handlers\Search\Sorting\SortingField;
use TrailerCentral\Sdk\Handlers\Search\Terms\Collection;
use TrailerCentral\Sdk\Handlers\Search\Terms\Range;
use TrailerCentral\Sdk\Handlers\Search\Terms\Searchable;
use TrailerCentral\Sdk\Handlers\Search\Terms\Term;
use TrailerCentral\Sdk\Handlers\Search\Filters\FilterGroup;
use TrailerCentral\Sdk\Handlers\Search\Filters\Filter;
use TrailerCentral\Sdk\Resources\Search;
use TrailerCentral\Sdk\Sdk;

class InventorySDKService implements InventorySDKServiceInterface
{
    use Helpers;

    private Request $request;
    private Search $search;
    private FilterGroup $mainFilterGroup;

    const PAGE_SIZE = 10;

    private int $currentPage = 1;
    private int $perPage = self::PAGE_SIZE;

    const SALE_SCRIPT_ATTRIBUTE = 'sale';
    const PRICE_SCRIPT_ATTRIBUTE = 'price';
    const TILT_TRAILER_INVENTORY = 'Tilt Trailers';
    const TERM_SEARCH_KEY_MAP = [
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
        'availability' => 'availability'
    ];
    const RANGE_SEARCH_KEY_MAP = [
        'price' => 'existingPrice',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'gvwr' => 'gvwr',
        'payload_capacity' => 'payloadCapacity',
    ];

    const DEFAULT_CATEGORY = [
        'name' => 'Other',
        'type_id' => 1,
        'type_label' => 'General Trailers'
    ];

    const INVENTORY_SOLD = 'sold';
    const INVENTORY_AVAILABLE = 'available';

    const DEFAULT_DISTANCE = 300;
    const DEFAULT_SORT = '+distance';
    const DEFAULT_NO_LOCATION_SORT = '-createdAt';

    // location set by DW as default lat/lon
    const DEFAULT_LAT_ON_DW = 39.8090;
    const DEFAULT_LON_ON_DW = -98.5550;

    public function __construct()
    {
        $this->request = new Request();
        $this->request->add('request_id', config('inventory-sdk.request_id'));
        $this->mainFilterGroup = new FilterGroup();

        $this->request->addFilterGroup($this->mainFilterGroup);

        $sdk = new Sdk(config('inventory-sdk.url'), [
            'headers' => [
                'access-token' => '',
                'sample_key' => ''
            ],
            'verify' => false
        ]);

        $this->search = new Search($sdk);
    }

    /**
     * @param array $params
     * @return TcEsResponseInventoryList
     * @throws GuzzleException
     * @throws \JsonException
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
        \Log::info($this->request->serialize());
        return $this->responseFromSDKResponse($this->search->execute($this->request));
    }

    /**
     * @param Response $sdkResponse
     * @return TcEsResponseInventoryList
     */
    protected function responseFromSDKResponse(Response $sdkResponse): TcEsResponseInventoryList
    {
        $result = [];
        $hits = $sdkResponse->hits();
        foreach ($hits as $hit) {
            $result[] = TcEsInventory::fromData($hit);
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
        return $response;
    }

    /**
     * @param array $params
     * @return GeoCoordinates
     */
    protected function addGeolocation(array $params): GeoCoordinates
    {
        $location = $this->getGeolocationInfo($params);

        if (isset($params['country'])) {
            $this->request->add('location_country', strtoupper($params['country']));
        } elseif ($location->lat() !== self::DEFAULT_LAT_ON_DW && $location->lon() !== self::DEFAULT_LON_ON_DW) {
            $distance = $params['distance'] ? (float)$params['distance'] : self::DEFAULT_DISTANCE;
            $location = new GeolocationRange($location->lat(), $location->lon(), $distance);
        }

        $this->request->withGeolocation($location);

        return $location;
    }

    /**
     * @param array $params
     * @param GeoCoordinates $location
     * @return void
     */
    protected function addSorting(array $params, GeoCoordinates $location)
    {
        if (isset($params['is_random']) && $params['is_random']) {
            $this->request->withSorting(new Sorting([], [], true));
        } else {
            if (isset($params['sort'])) {
                $sort = $params['sort'];
            } else if ($location->lat() !== self::DEFAULT_LAT_ON_DW && $location->lon() !== self::DEFAULT_LON_ON_DW) {
                $sort = self::DEFAULT_SORT;
            } else {
                $sort = self::DEFAULT_NO_LOCATION_SORT;
            }

            $order = new SortOrder($sort);
            $this->request->withSorting(new Sorting([
                new SortingField($order->field, $order->direction)
            ]));
        }
    }

    /**
     * @return void
     */
    protected function addCommonFilters(array $params)
    {
        $attributes = [];
        if (!empty($params['sale'])) {
            $attributes[self::SALE_SCRIPT_ATTRIBUTE] = true;

            $attributes[self::PRICE_SCRIPT_ATTRIBUTE] = [];
            if (!empty($params['price_min']) && $params['price_min'] > 0 && !empty($params['price_max'])) {
                $attributes[self::PRICE_SCRIPT_ATTRIBUTE] = [$params['price_min'], $params['price_max']];
            }

            $this->mainFilterGroup->add(new Filter('sale_price_script', new Collection($attributes)));
        }

        $this->mainFilterGroup->add(new Filter('classifieds_site', new Collection([true])));
        $this->mainFilterGroup->add(new Filter(
            'availability', new Collection([self::INVENTORY_SOLD], Operator::NOT_EQUAL
        )));
        $this->mainFilterGroup->add(new Filter('isRental', new Collection([false])));
    }

    protected function addDealerFilter(array $params) {
        if(!empty($params['dealer_id'])) {
            $this->request->withDealerIds(new Filter(
                'dealer_id', new Collection([$params['dealer_id']])
            ));
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addSearchTerms(array $params)
    {
        foreach (self::TERM_SEARCH_KEY_MAP as $field => $searchField) {
            if ($value = $params[$field] ?? null) {
                $this->mainFilterGroup->add(new Filter(
                    $searchField, new Collection(explode(';', $value))
                ));
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addRangeQueries(array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field => $searchField) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            if (isset($params[$minFieldKey]) || isset($params[$maxFieldKey])) {
                $this->mainFilterGroup->add(new Filter(
                    $searchField,
                    new Range($params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null)
                ));
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addPagination(array $params)
    {
        $this->currentPage = LengthAwarePaginator::resolveCurrentPage();
        $this->perPage = $params['per_page'] ?? self::PAGE_SIZE;

        $this->request->withPagination(new Pagination($this->perPage, $this->currentPage));
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addCategories(array $params)
    {
        $categories = $this->getMappedCategories(
            $params['type_id'] ?? null,
            $params['category'] ?? null
        );

        if (empty($categories)) {
            throw new HttpException(400, 'No category was selected');
        }

        $this->mainFilterGroup->add(new Filter(
            'category', new Collection($categories)
        ));

        if (isset($params['category']) && $params['category'] === self::TILT_TRAILER_INVENTORY) {
            $categories = $this->getMappedCategories(
                $params['type_id'] ?? null,
                null
            );
            $this->mainFilterGroup->add(new Filter(
                'tilt', new Collection([1])
            ));
            $this->mainFilterGroup->add(new Filter(
                'category', new Collection($categories)
            ));
        }
    }

    protected function addImages(array $params)
    {
        if (isset($params['has_image']) && $params['has_image']) {
            $this->mainFilterGroup->add(new Filter('empty_images', new Collection([false])));
        }
    }

    /**
     * @param int|null $type_id
     * @param string|null $categories_string
     * @return array
     */
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

    /**
     * @param array $params
     * @return GeoCoordinates
     */
    protected function getGeolocationInfo(array $params): GeoCoordinates
    {
        if (isset($params['lat']) && isset($params['lon'])) {
            return new Geolocation((float)$params['lat'], (float)$params['lon']);
        } else if (
            isset($params['location']) &&
            $geolocation = $this->getGeolocationInfoFromLocation($params['location'])
        ) {
            return $geolocation;
        }
        //use coordinates provided by DW as the center of the US
        return new Geolocation(self::DEFAULT_LAT_ON_DW, self::DEFAULT_LON_ON_DW);
    }

    /**
     * @param string $location
     * @return GeoCoordinates|null
     */
    protected function getGeolocationInfoFromLocation(string $location): ?GeoCoordinates
    {
        $response = json_decode(
            $this->api->get('map_search/geocode', ['q' => $location]),
            true
        );
        if (count($response['data']) > 0) {
            $position = $response['data'][0]['position'];
            return new Geolocation((float)$position['lat'], (float)$position['lng']);
        }
        return null;
    }
}
