<?php

declare(strict_types=1);

namespace App\DTOs\Inventory;

use App\Traits\TypedPropertyTrait;
use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class TcApiResponseInventory
{
    const statusToAvailabilityMap = [
        1 => 'available',
        2 => 'sold',
        3 => 'on_order',
        4 => 'pending_sale',
        5 => 'special_order',
    ];

    use \App\DTOs\Arrayable;
    use TypedPropertyTrait;

    public int $id;
    public ?string $url;
    public ?array $features;
    public ?string $description;
    public ?float $payload_capacity;
    public ?float $gvwr;
    public ?string $condition;
    public ?float $weight;
    public ?float $width;
    public ?float $height;
    public ?float $length;
    public ?string $stock;
    public ?string $vin;
    public ?string $pull_type;
    public ?string $manufacturer;
    public array $dealer;
    public string $listing_date;
    public ?float $price;
    public ?float $sales_price;
    public ?string $inventory_title;
    public ?array $photos;
    public ?int $type_id;
    public ?string $category;
    public ?string $availability;
    public ?string $availability_label;

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
     $obj->condition = $data['condition'];
     $obj->weight = $data['weight'];
     $obj->width = $data['width'];
     $obj->height = $data['height'];
     $obj->length = $data['length'];
     $obj->stock = $data['stock'];
     $obj->vin = $data['vin'];
     $obj->pull_type = '';
     $obj->manufacturer = $data['manufacturer'];
     $obj->dealer = $data['dealer'];
     $obj->listing_date = $data['created_at'];
     $obj->price = $data['price'];
     $obj->sales_price = $data['sales_price'];
     $obj->website_price = $data['website_price'];
     $obj->inventory_title = $data['title'];
     $obj->photos = $data['images'];
     $obj->dealer_location = $data['dealer_location'];
     $obj->primary_image = $data['primary_image'];
     $obj->category = $data['category'];
     $obj->availability = self::statusToAvailabilityMap[$data['status_id']] ?? '';
     $obj->availability_label = $data['status'] ?? '';
     foreach($data['attributes'] as $attribute) {
       $obj->setTypedProperty($attribute['code'], $attribute['value']);
     }

     return $obj;
 }
}
