<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\Inventory\Geolocation\Point;

class SearchInventoryRequest extends Request
{
    private const DELIMITER = ';';

    protected $rules = [
        'per_page' => 'integer|min:1|max:100',
        'page' => ['integer', 'min:0'],
        'lat' => ['required', 'numeric'],
        'lon' => ['required', 'numeric']
    ];

    public function terms(): array
    {
        return collect($this->all())->except(['sort', 'page', 'per_page', 'offset', 'dealerId', 'lat', 'lon'])->toArray();
    }

    public function dealerIds(): array
    {
        return array_filter(explode(self::DELIMITER, $this->dealerId ?? ''));
    }

    public function sort(): array
    {
        return ['sort' => $this->sort];
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
        return (int)($this->offset ?? 0);
    }

    public function location(): \App\Models\Inventory\Geolocation\Point
    {
        return new Point((float)$this->lat, (float)$this->lon);
    }
}
