<?php

namespace App\Services\ElasticSearch\Inventory;

use App\Exceptions\ElasticSearch\FilterNotFoundException;
use App\Models\Inventory\InventoryFilter;
use App\Repositories\Inventory\InventoryFilterRepositoryInterface;
use App\Services\ElasticSearch\Inventory\Builders\CustomQueryBuilder;
use App\Services\ElasticSearch\Inventory\Builders\FieldQueryBuilderInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FieldMapperService implements InventoryFieldMapperServiceInterface
{
    /** @var Collection<string, InventoryFilter> */
    private $filters;

    /** @var string[] */
    private const EDGE_CASES_TO_GENERATE_QUERIES_FOR = [
        'show_images', //based on the show images config
        'clearance_special', // based on all-inventory/all-clearance-specials for `pandpsales` & `pandprvs`,
        'location_region', // based on addRegionToElastic on DW
        'location_city', // based on addRegionToElastic on DW
        'classifieds_site', // based on InventoryCommon class on DW
        'sale_price_script', // handle sale&price filtering
        'location_country', // filter by location.country for TT
        'empty_images', // handle exclusion of empty images
        'availability', // handle availability
        'isArchived',  // to be able handle some especial OEM like `Mill Trailers` and MO Great Dane
        'showOnWebsite', // to be able handle some especial OEM like `Mill Trailers` and MO Great Dane
    ];

    /**
     * @param Filter $field
     * @return FieldQueryBuilderInterface when the filter was not able to be handled
     */
    public function getBuilder(Filter $field): FieldQueryBuilderInterface
    {
        $repository = resolve(InventoryFilterRepositoryInterface::class);

        $this->filters = Cache::remember('inventory.filters',
            60 * 60 * 24,
            static function () use ($repository): Collection {
                return $repository->getAll()->keyBy('attribute');
            });

        /** @var ?InventoryFilter $filter */

        $filter = $this->filters->get($this->resolveName($field->getName()));

        if ($filter) {
            $className = __NAMESPACE__ . '\\Builders\\' . ucfirst($filter->type) . 'QueryBuilder';

            return new $className($field);
        }

        if (in_array($field->getName(), self::EDGE_CASES_TO_GENERATE_QUERIES_FOR)) {
            return new CustomQueryBuilder($field);
        }

        throw new FilterNotFoundException("`{$field->getName()}` was not able to be build");
    }

    /**
     * Resolves the names just like it is within the table `inventory_filter` to be able to get the filter type e.g:
     * - existingPrice -> price
     * - numSleep -> sleeping_capacity
     */
    private function resolveName(string $fieldName): string
    {
        $edgeCases = [
            'existingPrice' => 'price',
            'numSleeps' => 'sleeping_capacity',
            'numSleep' => 'sleeping_capacity',
            'basicPrice' => 'price',
            'numAxles' => 'axles',
            'frameMaterial' => 'construction',
            'hasRamps' => 'ramps',
            'numStalls' => 'stalls',
            'loadType' => 'configuration',
            'hasMidtack' => 'midtack',
            'updatedAtUser' => 'updated_at',
            'numAc' => 'air_conditioners',
            'hasLq' => 'livingquarters',
            'numSlideouts' => 'slideouts',
            'numPassengers' => 'passengers',
            'featureList.floorPlan' => 'floor_plans',
            'numBatteries' => 'number_batteries',
        ];

        if (array_key_exists($fieldName, $edgeCases)) {
            return $edgeCases[$fieldName];
        }

        return Str::snake($fieldName);
    }
}
