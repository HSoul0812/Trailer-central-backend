<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Services\ElasticSearch\Inventory\Parameters\DealerId;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\Geolocation;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;

/**
 * @property int $page
 * @property int $per_page
 * @property int $offset
 * @property int $x_qa_req
 * @property int $in_random_order
 * @property string $geolocation
 * @property boolean $classifieds_site
 */
class SearchInventoryRequest extends Request
{
//    protected $rules = [
//        'pagination.per_page' => 'integer|min:1|max:100',
//        'pagination.page' => ['integer', 'min:0'],
//        'classifieds_site' => 'boolean',
//        'geolocation' => ['required', 'string'], // @todo we should add a regex validation here
//    ];

    public function terms(): array
    {
        return $this->filters;
    }

    public function dealerIds(): DealerId
    {
        return DealerId::fromString($this->dealerId ?? '');
    }

    public function sort(): array
    {
        return $this->sort ? collect($this->sort)->mapWithKeys(function ($term) {
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
        return (int)($this->json('pagination.per_page') ?? 15);
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
        return Geolocation::fromString($this->geolocation ?? '');
    }

    public function getESQuery(): bool
    {
        return $this->json('debug.x_qa_req');
    }

    public function inRandomOrder(): bool
    {
        return (int)$this->in_random_order === 1;
    }

    public function all($keys = null): array
    {
        $all = parent::all($keys);

        // default values got through `all` method
        if ($keys === null || in_array('classifieds_site', $keys)) {
            $all['classifieds_site'] = $this->input('classifieds_site', false);
        }

        return $all;
    }
}
