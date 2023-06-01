<?php

namespace App\DTOs\Inventory;

class TcApiResponseManufacturer
{
    public function __construct(public int $id, public string $name, public string $label)
    {
    }

    public static function fromData(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['label'],
        );
    }
}
