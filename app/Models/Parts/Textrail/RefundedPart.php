<?php

declare(strict_types=1);

namespace App\Models\Parts\Textrail;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string $sku
 * @property-read float $amount
 */
class RefundedPart implements DTO
{
    use WithFactory;
    use WithGetter;

    /** @var int */
    private $id;

    /** @var float */
    private $amount;

    /** @var string */
    private $title;

    /** @var string */
    private $sku;

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'title' => $this->title,
            'amount' => $this->amount
        ];
    }
}
