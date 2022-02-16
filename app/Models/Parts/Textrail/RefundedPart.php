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
 * @property-read int $qty
 * @property-read string $status
 */
class RefundedPart implements DTO
{
    const FULLY_REFUND = 'refunded';
    CONST PARTY_REFUND = 'partially_refunded';
    CONST NON_REFUND = 'non-refunded';

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

    /** @var int */
    private $qty;

    /** @var string */
    private $status;

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'title' => $this->title,
            'amount' => $this->amount,
            'qty' => $this->qty,
            'status' => $this->status,
        ];
    }
}
