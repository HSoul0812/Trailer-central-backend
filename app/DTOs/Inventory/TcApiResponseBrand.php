<?php

namespace App\DTOs\Inventory;

class TcApiResponseBrand
{
    public int $id;
    public string $name;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->name = $data['name'];

        return $obj;
    }
}
