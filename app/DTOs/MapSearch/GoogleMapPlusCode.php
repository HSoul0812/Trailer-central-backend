<?php

namespace App\DTOs\MapSearch;

class GoogleMapPlusCode
{
    public ?string $compound_code;
    public ?string $global_code;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->compound_code = $data['compound_code'] ?? null;
        $obj->global_code = $data['global_code'] ?? null;

        return $obj;
    }
}
