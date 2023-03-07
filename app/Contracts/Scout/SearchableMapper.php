<?php

declare(strict_types=1);

namespace App\Contracts\Scout;

use ElasticAdapter\Indices\Mapping;

interface SearchableMapper
{
    public function mapping(): ?Mapping;
}
