<?php

declare(strict_types=1);

namespace App\DTOs\Inventory;

use JetBrains\PhpStorm\Pure;

class TcApiResponseInventoryCreate
{
    public int $id;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];

        return $obj;
    }
}
