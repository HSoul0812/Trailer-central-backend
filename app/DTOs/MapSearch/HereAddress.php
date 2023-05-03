<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HereAddress
{
    public string $label;
    public string $countryCode;
    public string $countryName;
    public ?string $stateCode;
    public ?string $state;
    public ?string $county;
    public ?string $city;
    public ?string $district;
    public ?string $street;
    public ?string $postalCode;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->label = $data['label'];
        $obj->countryCode = $data['countryCode'];
        $obj->countryName = $data['countryName'];
        $obj->stateCode = $data['stateCode'] ?? null;
        $obj->state = $data['state'] ?? null;
        $obj->county = $data['county'] ?? null;
        $obj->city = $data['city'] ?? null;
        $obj->district = $data['district'] ?? null;
        $obj->street = $data['street'] ?? null;
        $obj->postalCode = $data['postalCode'] ?? null;

        return $obj;
    }
}
