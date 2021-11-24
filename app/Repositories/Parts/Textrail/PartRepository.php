<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    /**
     * @var \App\Models\Parts\Textrail\Part
     */
    protected $model;

    public function __construct(Part $model) {
        parent::__construct($model);

        $this->model = $model;
    }

    /**
     * @param  array  $ids
     * @return Collection|array<Part>
     */

    public function getAllExceptBySku(array $skus) : Collection
    {
      return $this->model->whereNotIn('sku', $skus)->get();
    }

    public function getById(int $id) : ?Part
    {
        return $this->model->find($id);
    }

    public function getBySkuWithTrashed(string $sku) : ?Part
    {
        return $this->model->withTrashed()->where('sku', $sku)->first();
    }

    public function createOrUpdateBySku(array $params) : Part
    {
        return $this->model->updateOrCreate(['sku' => $params['sku']], $params);
    }

    public function getBySku($sku) {
        return $this->model->where('sku', $sku)->first();
    }

    /**
     * @param  array  $ids
     * @return Collection|array<Part>
     */
    public function getAllByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
