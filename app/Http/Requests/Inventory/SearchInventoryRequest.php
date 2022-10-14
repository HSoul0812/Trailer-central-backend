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
 * @property string $geolocation
 * @property boolean $classifieds_site
 */
class SearchInventoryRequest extends Request
{
    private const DELIMITER = ';';
    private const SORT_DELIMITER = ':';

    protected $rules = [
        'per_page' => 'integer|min:1|max:100',
        'page' => ['integer', 'min:0'],
        'classifieds_site' => 'boolean',
        'geolocation' => ['required', 'string'], // @todo we should add a regex validation here
    ];

    public function terms(): array
    {
        return collect($this->all())->except([
            'sort',
            'page',
            'per_page',
            'offset',
            'dealerId',
            'geolocation',
            'x_qa_req'
        ])->toArray();
    }

    public function dealerIds(): DealerId
    {
        return DealerId::fromString($this->dealerId ?? '');
    }

    public function sort(): array
    {
        return $this->sort ? collect(explode(self::DELIMITER, $this->sort))->mapWithKeys(function ($sort) {
            [$sortTerm, $order] = explode(self::SORT_DELIMITER, $sort);
            return [$sortTerm => $order];
        })->toArray() : [];
    }

    public function pagination(): array
    {
        return ['page' => $this->page(), 'per_page' => $this->perPage(), 'offset' => $this->offSet()];
    }

    public function page(): int
    {
        return (int)($this->page ?? 1);
    }

    public function perPage(): int
    {
        return (int)($this->per_page ?? 15);
    }

    public function offSet(): int
    {
        if (!$this->offset) {
            return ($this->page() - 1) * $this->perPage();
        }

        return (int)$this->offset;
    }

    public function geolocation(): GeolocationInterface
    {
        return Geolocation::fromString($this->geolocation ?? '');
    }

    public function getESQuery(): bool
    {
        return (int)$this->x_qa_req === 1;
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
