<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\Geolocation;
use App\Services\ElasticSearch\Inventory\Parameters\Geolocation\GeolocationInterface;

/**
 * @property int $page
 * @property int $per_page
 * @property int $offset
 * @property int $x_qa_req
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
        return $this->json('filter_groups');
    }

    public function dealerIds(): array
    {
        return $this->json('dealers');
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
        return Geolocation::fromArray($this->json('geolocation'));
    }

    public function getESQuery(): bool
    {
        return $this->json('debug');
    }
}
