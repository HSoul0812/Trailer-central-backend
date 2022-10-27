<?php

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcEsInventory;
use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Pagination\LengthAwarePaginator;
use TrailerCentral\Sdk\Handlers\Search\Collection;
use TrailerCentral\Sdk\Handlers\Search\Geolocation;
use TrailerCentral\Sdk\Handlers\Search\GeolocationInterface;
use TrailerCentral\Sdk\Handlers\Search\Pagination;
use TrailerCentral\Sdk\Handlers\Search\Range;
use TrailerCentral\Sdk\Handlers\Search\Request;
use TrailerCentral\Sdk\Handlers\Search\Response;
use TrailerCentral\Sdk\Resources\Search;
use TrailerCentral\Sdk\Sdk;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class InventorySDKService implements InventorySDKServiceInterface
{
    use Helpers;

    private Request $request;
    private Search $search;

    const PAGE_SIZE = 10;
    const INVENTORY_SOLD = 'sold';
    const INVENTORY_IMAGES = 'jpg;png;jpeg';
    const TILT_TRAILER_INVENTORY = 'Tilt Trailers';
    const TERM_SEARCH_KEY_MAP = [
        'dealer_id' => 'dealerId',
        'stalls' => 'stalls',
        'pull_type' => 'pullType',
        'manufacturer' => 'manufacturer',
        'condition' => 'condition',
        'construction' => 'construction',
        'year' => 'year',
        'slideouts' => 'slideouts',
        'configuration' => 'configuration',
        'axles' => 'axles',
        'color' => 'color',
        'availability' => 'availability'
    ];
    const RANGE_SEARCH_KEY_MAP = [
        'price',
        'length',
        'width',
        'height',
        'gvwr',
        'payload_capacity'
    ];

    public function __construct()
    {
        $this->request = new Request();

        $sdk = new Sdk(env('INVENTORY_SDK_API_URL'));
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
        $this->addCommonFilters();
        $this->addImages($params);
        $this->addCategories($params);
        $this->addSearchTerms($params);
        $this->addRangeQueries($params);
        $this->addPagination($params);

        $this->request->withGeolocation($this->getGeolocationInfo($params));
        $response = $this->search->execute($this->request);

        return $this->responseFromSDKResponse($response);
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
        $response->aggregations = $sdkResponse->aggregations();
        $response->inventories = new LengthAwarePaginator(
            $result,
            $sdkResponse->total(),
            10,
            1
        );
        return $response;
    }

    /**
     * @return void
     */
    protected function addCommonFilters()
    {
//        $this->request->add('isRental', true);
        //TODO: handle availability
//        $this->request->add('availability', new Collection([self::INVENTORY_SOLD], Collection::EXCLUSION));
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addImages(array $params)
    {
        if (isset($params['has_image']) && $params['has_image']) {
            $this->request->add('show_images', self::INVENTORY_IMAGES);
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
                $this->request->add($searchField, $value);
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addRangeQueries(array $params)
    {
        foreach (self::RANGE_SEARCH_KEY_MAP as $field) {
            $minFieldKey = "{$field}_min";
            $maxFieldKey = "{$field}_max";
            if (isset($params[$minFieldKey]) || isset($params[$maxFieldKey])) {
                $this->request->add($field, new Range($params[$minFieldKey] ?? null, $params[$maxFieldKey] ?? null));
            }
        }
    }

    /**
     * @param array $params
     * @return void
     */
    protected function addPagination(array $params)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $this->request->withPagination(new Pagination($currentPage, $params['per_page'] ?? self::PAGE_SIZE));
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
            throw new BadRequestException('No category was selected');
        }

        $this->request->add('category', new Collection($categories));

        if (isset($params['category']) && $params['category'] === self::TILT_TRAILER_INVENTORY) {
            $categories = $this->getMappedCategories(
                $params['type_id'] ?? null,
                null
            );
            $this->request->add('tilt', 1);
            $this->request->add('category', new Collection($categories));
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
                    $mapped_categories[] = $category->category_mappings->map_to;
                }
            }
        } else {
            foreach (CategoryMappings::all() as $mapping) {
                $mapped_categories[] = $mapping->map_to;
            }
        }

        return $mapped_categories;
    }

    /**
     * @param array $params
     * @return GeolocationInterface
     */
    protected function getGeolocationInfo(array $params): GeolocationInterface
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
        return new Geolocation(39.8090, -98.5550);
    }

    /**
     * @param string $location
     * @return GeolocationInterface|null
     */
    protected function getGeolocationInfoFromLocation(string $location): ?GeolocationInterface
    {
        $response = json_decode(
            $this->api->get('map_search/geocode', ['q' => $location]),
            true
        );
        if (count($response['data']) > 0) {
            $position = $response['data'][0]['position'];
            return new Geolocation((float)$position['lat'], (float)$position['lon']);
        }
        return null;
    }
}
