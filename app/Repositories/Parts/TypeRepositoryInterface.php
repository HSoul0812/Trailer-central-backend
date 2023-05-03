<?php

declare(strict_types=1);

namespace App\Repositories\Parts;

use Illuminate\Database\Eloquent\Collection;

interface TypeRepositoryInterface
{
    public function getAll(): Collection;
}
