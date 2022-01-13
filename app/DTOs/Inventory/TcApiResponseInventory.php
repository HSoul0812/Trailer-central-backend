<?php

declare(strict_types=1);

namespace App\DTOs\Inventory;

use JetBrains\PhpStorm\Pure;

class TcApiResponseInventory
{
    public int $id;
    public ?string $url;
    public ?array $features;
    public ?string $description;
    public ?float $payload_capacity;
    public ?float $gvwr;
    public ?float $weight;
    public ?float $width;
    public ?float $height;
    public ?float $length;
    public ?string $manufacturer;
    public array $dealer;
    public string $listing_date;
    public ?float $price;
    public ?float $sales_price;
    public ?string $inventory_title;
    public ?array $photos;

    #[Pure]
 public static function fromData(array $data): self
 {
     $obj = new self();
     $obj->id = $data['id'];
     $obj->url = $data['url'];
     $obj->features = $data['features'];
     $obj->description = $data['description'];
     $obj->payload_capacity = $data['payload_capacity'];
     $obj->gvwr = $data['gvwr'];
     $obj->weight = $data['weight'];
     $obj->width = $data['width'];
     $obj->height = $data['height'];
     $obj->length = $data['length'];
     $obj->manufacturer = $data['manufacturer'];
     $obj->dealer = $data['dealer'];
     $obj->listing_date = $data['created_at'];
     $obj->price = $data['price'];
     $obj->sales_price = $data['sales_price'];
     $obj->inventory_title = $data['title'];
     $obj->photos = $data['images'];
     $obj->dealer_location = $data['dealer_location'];

     return $obj;
 }
}
