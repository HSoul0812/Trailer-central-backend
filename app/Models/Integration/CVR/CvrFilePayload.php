<?php

declare(strict_types=1);

namespace App\Models\Integration\CVR;

use App\Models\Common\MonitoredJobPayload;

/**
 * @property-read int $unit_sale_id
 */
class CvrFilePayload extends MonitoredJobPayload
{
    /**
     * @var string
     */
    protected $unit_sale_id;

    public function asArray(): array
    {
        return [
            'unit_sale_id' => $this->unit_sale_id
        ];
    }
}
