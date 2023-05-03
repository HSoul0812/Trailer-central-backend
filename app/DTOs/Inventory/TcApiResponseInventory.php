<?php

declare(strict_types=1);

namespace App\DTOs\Inventory;

use App\DTOs\Dealer\PrivateDealerCheck;
use App\Traits\TypedPropertyTrait;
use JetBrains\PhpStorm\Pure;

class TcApiResponseInventory
{
    use \App\DTOs\Arrayable;
    use TypedPropertyTrait;
    public const statusToAvailabilityMap = [
        1 => 'available',
        2 => 'sold',
        3 => 'on_order',
        4 => 'pending_sale',
        5 => 'special_order',
    ];

    public int $id;
    public ?string $url;
    public ?array $features;
    public ?array $attributes;
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
    public ?float $basic_price;
    public ?float $sales_price;
    public ?string $inventory_title;
    public ?array $photos;
    public ?int $type_id;
    public ?string $type_label;
    public ?string $category;
    public ?string $availability;
    public ?string $availability_label;
    public ?int $show_on_website;
    public ?int $year;
    public ?string $status;
    public ?string $axle_capacity;
    public ?string $tt_payment_expiration_date;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->identifier = $data['identifier'];
        $obj->active = $data['active'];
        $obj->archived_at = $data['archived_at'];
        $obj->brand = $data['brand'];
        $obj->category_label = $data['category_label'];
        $obj->entity_type_id = $data['entity_type_id'];
        $obj->url = $data['url'];
        $obj->features = $data['features'];
        $obj->attributes = $data['attributes'];
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

        if (!empty($obj->dealer)) {
            $obj->dealer['is_private'] = (new PrivateDealerCheck())->checkArray($obj->dealer);
        }

        $obj->listing_date = $data['created_at'];

        $obj->availability = self::statusToAvailabilityMap[$data['status_id']] ?? '';
        $obj->availability_label = $data['status'] ?? '';
        if ($obj->availability !== 'sold') {
            $obj->price = $data['price'];
            $obj->sales_price = $data['sales_price'];
            $obj->website_price = isset($data['use_website_price']) && $data['use_website_price']
                ? $data['website_price']
                : $data['price'];

            $obj->basic_price = $data['price'];
        } else {
            $obj->price = null;
            $obj->sales_price = null;
            $obj->website_price = null;
            $obj->basic_price = null;
        }

        $obj->inventory_title = $data['title'];
        $obj->photos = $data['images'];
        $obj->dealer_location = $data['dealer_location'];
        $obj->primary_image = $data['primary_image'];
        $obj->category = $data['category'];
        $obj->is_archived = $data['is_archived'];
        $obj->show_on_website = $data['show_on_website'];
        $obj->tt_payment_expiration_date = $data['tt_payment_expiration_date'] ?? null;
        $obj->times_viewed = $data['times_viewed'];
        $obj->sold_at = $data['sold_at'];
        $obj->is_featured = $data['is_featured'];
        $obj->is_special = $data['is_special'];
        $obj->use_website_price = $data['use_website_price'] ?? false;
        $obj->notes = $data['notes'];
        $obj->year = $data['year'];
        $obj->status = $data['status'];
        $obj->axle_capacity = $data['axle_capacity'];

        foreach ($data['attributes'] as $attribute) {
            $obj->setTypedProperty($attribute['code'], $attribute['value']);
        }

        return $obj;
    }
}
