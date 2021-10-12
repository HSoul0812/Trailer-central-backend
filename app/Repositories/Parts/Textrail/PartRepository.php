<?php

namespace App\Repositories\Parts\Textrail;

use App\Models\Parts\Textrail\Part;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\PartRepository as BaseRepository;
use Illuminate\Support\Facades\DB;

class PartRepository extends BaseRepository implements PartRepositoryInterface
{
    protected $model;

    public function __construct(Part $model) {
        $this->model = $model;
    }

    public function getById($id) {
        return Part::findOrFail($id);
    }

    public function createOrUpdateBySku($params) {
        return $this->model->updateOrCreate(['sku' => $params['sku']], $params);
    }

}
