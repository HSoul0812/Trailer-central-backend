<?php

namespace App\DTOs\User;

class TcApiResponseUserLocation
{
    public int $id;
    public string $identifier;
    public string $name;
    public string $contact;
    public string $address;
    public string $city;
    public string $county;
    public string $region;
    public string $country;
    public string $postalCode;
    public string $phone;
    public bool $is_default;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->identifier = $data['identifier'];
        $obj->name = $data['name'];
        $obj->contact = $data['contact'];
        $obj->address = $data['address'];
        $obj->city = $data['city'];
        $obj->county = $data['county'];
        $obj->region = $data['region'];
        $obj->country = $data['country'];
        $obj->postalCode = $data['postalCode'];
        $obj->phone = $data['phone'];
        $obj->is_default = $data['is_default'];

        return $obj;
    }
}
