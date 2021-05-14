<?php

declare(strict_types=1);

namespace App\Models\CRM\Dms\ServiceOrder;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read string $month_name
 * @property-read string $type
 * @property-read numeric $unit_price
 * @property-read string $created_at
 */
class MonthlyServiceHours implements DTO
{
    use WithGetter;
    use WithFactory;

    /**
     * @var string
     */
    private $month_name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var numeric
     */
    private $unit_price;

    /**
     * @var string
     */
    private $created_at;

    /**
     * @var int
     */
    private $aggregate;

    /**
     * @var int
     */
    private $dealer_id;

    /**
     * @return array{month_name: string, type: string, unit_price: numeric, created_at: string}
     */
    public function asArray(): array
    {
        return [
            'month_name' => $this->month_name,
            'type' => $this->type,
            'unit_price' => $this->unit_price,
            'created_at' => $this->created_at
        ];
    }
}
