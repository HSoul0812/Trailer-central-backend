<?php

namespace App\Repositories\Parts;

use App\Models\Parts\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TypeRepository implements TypeRepositoryInterface
{
    /**     
     * @var App\Models\Parts\Type
     */
    protected $model;

    public function __construct(Type $model) {
        $this->model = $model;
    }

    public function getAll($params): Collection
    {
        return $this->model->all();
    }

}