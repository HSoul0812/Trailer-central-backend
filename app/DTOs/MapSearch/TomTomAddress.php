<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomAddress
{
    public ?string $streetNumber;
    public ?string $streetName;
    public ?string $municipalitySubdivision;
    public ?string $municipality;
    public ?string $countrySecondarySubdivision;
    public ?string $countryTertiarySubdivision;
    public ?string $countrySubdivision;
    public ?string $countrySubdivisionName;
    public ?string $postalCode;
    public ?string $postalName;
    public ?string $extendedPostalCode;
    public string $countryCode;
    public string $country;
    public ?string $countryCodeISO3;
    public string $freeformAddress;
    public ?string $localName;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->streetNumber = $data['streetNumber'] ?? null;
        $obj->streetName = $data['streetName'] ?? null;
        $obj->municipalitySubdivision = $data['municipalitySubdivision'] ?? null;
        $obj->municipality = $data['municipality'] ?? null;
        $obj->countrySecondarySubdivision = $data['countrySecondarySubdivision'] ?? null;
        $obj->countryTertiarySubdivision = $data['countryTertiarySubdivision'] ?? null;
        $obj->countrySubdivision = $data['countrySubdivision'] ?? null;
        $obj->countrySubdivisionName = $data['countrySubdivisionName'] ?? null;
        $obj->postalCode = $data['postalCode'] ?? null;
        $obj->postalName = $data['postalName'] ?? null;
        $obj->extendedPostalCode = $data['extendedPostalCode'] ?? null;
        $obj->countryCode = $data['countryCode'];
        $obj->country = $data['country'];
        $obj->countryCodeISO3 = $data['countryCodeISO3'] ?? null;
        $obj->freeformAddress = $data['freeformAddress'];
        $obj->localName = $data['localName'] ?? null;

        return $obj;
    }
}
