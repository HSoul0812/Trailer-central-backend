<?php

namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class InventoryLocation implements Arrayable
{
    use \App\DTOs\Arrayable;

    public string $name;
    public string $email;
    public string $contact;
    public string $website;
    public string $phone;
    public string $address;
    public string $city;
    public string $region;
    public string $postalCode;
    public string $country;
    public GeoLocation $geo;

    #[Pure] public static function fromData(array $data):self
    {
        $obj = new self();
        $obj->name = $data['name'];
        $obj->email = $data['email'];
        $obj->contact = $data['contact'];
        $obj->website = $data['website'];
        $obj->phone = $data['phone'];
        $obj->address = $data['address'];
        $obj->city = $data['city'];
        $obj->region = $data['region'];
        $obj->postalCode = $data['postalCode'];
        $obj->country = $data['country'];
        $obj->geo = GeoLocation::fromData($data['geo']);
        return $obj;
    }
}
