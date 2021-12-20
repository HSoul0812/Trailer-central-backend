<?php

declare(strict_types=1);

namespace App\Repositories\Parts;

use App\Http\Requests\Parts\Type\IndexTypeRequest;
use Illuminate\Database\Eloquent\Collection;

interface TypeRepositoryInterface
{
    public function getAll(IndexTypeRequest $params): Collection;
}
