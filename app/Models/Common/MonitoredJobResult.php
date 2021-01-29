<?php

declare(strict_types=1);

namespace App\Models\Common;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read string $message the result message
 */
class MonitoredJobResult implements DTO
{
    use WithGetter;
    use WithFactory;

    /**
     * @var string
     */
    protected $message;

    public function asArray(): array
    {
        return [
            'message' => $this->message
        ];
    }
}
