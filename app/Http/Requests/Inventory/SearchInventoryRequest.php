<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Services\ElasticSearch\Inventory\Parameters\FilterGroup;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\Geolocation;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationRange;
use Illuminate\Validation\Rule;

/**
 * @property int $page
 * @property int $limit
 * @property int $offset
 * @property int $x_qa_req
 * @property boolean $classifieds_site
 */
class SearchInventoryRequest extends Request
{
    private const AGGREGATION_SIZE = 200;

    public function terms(): array
    {
        return $this->json('filter_groups');
    }

    public function dealerIds(): array
    {
        return $this->json('dealers', []);
    }

    public function sort(): array
    {
        $sort = $this->json('sort');
        return $sort ? collect($sort)->mapWithKeys(function ($term) {
            return [$term['field'] => $term['order']];
        })->toArray() : [];
    }

    public function pagination(): array
    {
        return ['page' => $this->page(), 'per_page' => $this->perPage(), 'offset' => $this->offSet()];
    }

    public function page(): int
    {
        return (int)($this->json('pagination.page') ?? 1);
    }

    public function perPage(): int
    {
        return (int)($this->json('pagination.limit') ?? 15);
    }

    public function offSet(): int
    {
        if (!$this->json('pagination.offset')) {
            return ($this->page() - 1) * $this->perPage();
        }

        return (int)$this->json('pagination.offset');
    }

    public function geolocation(): GeolocationInterface
    {
        return Geolocation::fromArray($this->json('geolocation'));
    }

    public function getESQuery(): bool
    {
        return $this->json('debug');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function getRules(): array
    {
        return [
            'sort' => ['present', 'array'],
            'sort.*.field' => ['required'],
            'sort.*.order' => ['required'],
            'pagination' => ['required'],
            'pagination.page' => ['nullable', 'integer', 'min:1'],
            'pagination.limit' => ['nullable', 'integer', 'min:1', 'max:350'],
            'filter_groups' => ['present', 'array'],
            'filter_groups.*.filters' => ['required', 'array'],
            'filter_groups.*.filters.*.name' => ['required'],
            'filter_groups.*.filters.*.terms' => ['present', 'array'],
            'filter_groups.*.filters.*.terms.*.operator' => ['required', Rule::in([Term::OPERATOR_EQ, Term::OPERATOR_NEQ])],
            'filter_groups.*.filters.*.terms.*.values' => ['present'],
            'filter_groups.*.append_to' => ['required', Rule::in([FilterGroup::APPEND_TO_POST_FILTERS, FilterGroup::APPEND_TO_QUERY])],
            'filter_groups.*.operator' => ['required', Rule::in([FilterGroup::OPERATOR_AND, FilterGroup::OPERATOR_OR])],
            'geolocation' => ['required'],
            'geolocation.lat' => ['required', 'numeric'],
            'geolocation.lon' => ['required', 'numeric'],
            'geolocation.range' => ['nullable', 'numeric'],
            'geolocation.units' => ['nullable', Rule::in([GeolocationRange::UNITS_MILES, GeolocationRange::UNITS_KILOMETERS])],
            'geolocation.grouping' => ['nullable', 'numeric'],
            'dealers' => ['nullable', 'array'],
            'dealers.*.operator' => ['required', Rule::in([Term::OPERATOR_EQ, Term::OPERATOR_NEQ])],
            'dealers.*.values' => ['required', 'array'],
            'debug' => ['required', 'boolean']
        ];
    }

    public function requestId()
    {
        return $this->json('request_id');
    }

    public function aggregationSize(): int
    {
        return (int)$this->json('aggregation_size',self::AGGREGATION_SIZE);
    }
}
