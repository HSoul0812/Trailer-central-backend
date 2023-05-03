<?php

namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class TcEsInventoryLocation implements Arrayable
{
    use \App\DTOs\Arrayable;

    public ?string $name;
    public ?string $email;
    public ?string $contact;
    public ?string $website;
    public ?string $phone;
    public ?string $address;
    public ?string $city;
    public ?string $region;
    public ?string $postal_code;
    public ?string $country;
    public ?TcEsGeolocation $geo;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->name = $data['name'] ?? null;
        $obj->email = $data['email'] ?? null;
        $obj->contact = $data['contact'] ?? null;
        $obj->website = $data['website'] ?? null;
        $obj->phone = $data['phone'] ?? null;
        $obj->address = $data['address'] ?? null;
        $obj->city = $data['city'] ?? null;
        $obj->region = $data['region'] ?? null;
        $obj->postal_code = $data['postalCode'] ?? null;
        $obj->country = $data['country'] ?? null;
        $obj->geo = isset($data['geo']) ? TcEsGeolocation::fromData($data['geo']) : null;

        return $obj;
    }
}
