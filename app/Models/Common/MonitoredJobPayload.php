<?php

declare(strict_types=1);

namespace App\Models\Common;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

class MonitoredJobPayload implements DTO
{
    use WithGetter;
    use WithFactory;

    public function asArray(): array
    {
        return [];
    }
}
