<?php
namespace App\DTOs\Inventory;

use App\Traits\TypedPropertyTrait;
use Illuminate\Contracts\Support\Arrayable;

class TcEsInventory implements Arrayable {
    use \App\DTOs\Arrayable;
    use TypedPropertyTrait;

    const IMAGE_BASE_URL = 'https://dealer-cdn.com';

    public string $id;
    public ?bool $is_active;
    public ?string $dealer_id;
    public ?string $dealer_location_id;
    public ?string $created_at;
    public ?string $updated_at;
    public ?string $updated_at_user;
    public ?bool $is_special;
    public ?bool $is_featured;
    public ?bool $is_archived;
    public ?string $stock;
    public ?string $title;
    public ?int $year;
    public ?string $manufacturer;
    public ?string $model;
    public ?string $description;
    public ?int $status;
    public ?string $category;
    public ?bool $use_website_price;
    public ?string $condition;
    public ?float $length;
    public ?float $width;
    public ?float $height;
    public ?bool $show_on_ksl;
    public ?bool $show_on_racingjunk;
    public ?bool $show_on_website;
    public ?TcEsInventoryDealer $dealer = null;
    public ?TcEsInventoryLocation $location = null;
    public ?float $width_inches;
    public ?float $height_inches;
    public ?float $length_inches;
    public ?string $width_display_mode;
    public ?string $height_display_mode;
    public ?string $length_display_mode;
    public ?array $keywords;
    public ?string $availability;
    public ?string $availability_label;
    public ?string $type_label;
    public ?string $category_label;
    public ?float $basic_price;
    public ?string $original_website_price;
    public ?float $websitePrice;
    public ?float $existing_price;
    public ?int $num_axles;
    public ?string $frame_material;
    public ?string $pull_type;
    public ?int $num_stalls;
    public ?string $load_type;
    public ?string $roof_type;
    public ?string $nose_type;
    public ?string $color;
    public ?int $num_sleeps;
    public ?int $num_ac;
    public ?string $fuel_type;
    public ?bool $is_rental;
    public ?string $num_slideouts;
    public ?string $num_batteries;
    public ?string $horsepower;
    public ?string $num_passengers;
    public ?string $conversion;
    public ?string $cab_type;
    public ?string $engine_size;
    public ?string $transmission;
    public ?string $drive_trail;
    public ?string $floorplan;
    public ?string $propulsion;
    public ?array $feature_list;
    public ?string $image;
    public ?array $images;
    public ?array $images_secondary;
    public ?float $gvwr;


    public static function imageToAbsoluteUrl($image) {
        if(str_starts_with($image, '/')) {
            return self::IMAGE_BASE_URL . $image;
        } else {
            return $image;
        }
    }

    public static function fromData(array $data):self {
        $obj = new self();
        $dealerData = [];
        $locationData = [];
        foreach($data as $key => $value) {
            $uKey = camel_case_2_underscore($key);
            if(str_starts_with($uKey, 'dealer.')) {
                $dealerData[substr($uKey, 7)] = $value;
            } else if(str_starts_with($uKey, 'location.')) {
                $locationData[substr($uKey, 9)] = $value;
            } else if($uKey === 'image') {
                $obj->image = self::imageToAbsoluteUrl($value);
            } else if($uKey === 'images' || $uKey === 'images_secondary') {
                $obj->$uKey = [];
                foreach ($value as $image) {
                    ($obj->$uKey)[] = self::imageToAbsoluteUrl($image);
                }
            } else {
                $obj->setTypedProperty($uKey, $value);
            }
        }

        $obj->dealer = TcEsInventoryDealer::fromData($dealerData);
        $obj->location = TcEsInventoryLocation::fromData($locationData);
        return $obj;
    }
}
