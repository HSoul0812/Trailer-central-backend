<?php

declare(strict_types=1);

namespace App\Repositories\Parts;

use App\Models\Parts\Type;
use Illuminate\Database\Eloquent\Collection;

class TypeRepository implements TypeRepositoryInterface
{
    /**
     * @var App\Models\Parts\Type
     */
    protected $model;

    public function __construct(Type $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all()->sortBy('id');
    }
}
