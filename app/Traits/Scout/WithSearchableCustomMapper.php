<?php

declare(strict_types=1);

namespace App\Traits\Scout;

use App\Contracts\Scout\SearchableMapper;

trait WithSearchableCustomMapper
{
    public function searchableMapper(): ?SearchableMapper
    {
        return null;
    }
}
