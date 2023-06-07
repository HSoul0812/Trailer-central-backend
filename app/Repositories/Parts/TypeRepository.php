<?php

declare(strict_types=1);

namespace App\Repositories\Parts;

use App\Models\Parts\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->model
            ->with(['categories' => function (BelongsToMany $query) {
                $query->orderBy('id');
            }])
            ->orderBy('id')
            ->get();
    }
}
