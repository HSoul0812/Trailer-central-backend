<?php

declare(strict_types=1);

namespace App\DTOs\Inventory;

use JetBrains\PhpStorm\Pure;

class TcApiResponseInventoryDelete
{
    public ?string $status;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->status = $data['status'];

        return $obj;
    }
}
