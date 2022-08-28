<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

use App\Models\Showroom\Showroom;
use Illuminate\Support\Collection;

class ShowRoomRepository implements AvailableValuesRepositoryInterface
{
    /** @var Showroom */
    private $model;

    public function __construct(Showroom $model)
    {
        $this->model = $model;
    }

    /**
     * List of available filters
     *
     * @param int $websiteId
     * @return Collection
     */
    public function pull(int $websiteId): Collection
    {
        return $this->model->select('brand')
            ->distinct()
            ->where('brand', '!=', '')
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->get()
            ->pluck('brand');
    }
}
