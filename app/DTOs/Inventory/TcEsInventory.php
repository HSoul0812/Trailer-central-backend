<?php
namespace App\DTOs\Inventory;

use App\Traits\TypedPropertyTrait;
use Illuminate\Contracts\Support\Arrayable;

class TcEsInventory implements Arrayable {
    use \App\DTOs\Arrayable;
    use TypedPropertyTrait;

    const IMAGE_BASE_URL = 'https://dealer-cdn.com';

    public string $id;
    public bool $isActive;
    public string $dealerId;
    public string $dealerLocationId;
    public string $createdAt;
    public string $updatedAt;
    public string $updatedAtUser;
    public bool $isSpecial;
    public bool $isFeatured;
    public bool $isArchived;
    public string $stock;
    public string $title;
    public int $year;
    public string $manufacturer;
    public string $model;
    public string $description;
    public int $status;
    public string $category;
    public bool $useWebsitePrice;
    public string $condition;
    public float $length;
    public float $width;
    public float $height;
    public bool $showOnKsl;
    public bool $showOnRacingjunk;
    public bool $showOnWebsite;
    public ?TcEsInventoryDealer $dealer = null;
    public ?TcEsInventoryLocation $location = null;
    public float $widthInches;
    public float $heightInches;
    public float $lengthInches;
    public string $widthDisplayMode;
    public string $heightDisplayMode;
    public string $lengthDisplayMode;
    public array $keywords;
    public string $availability;
    public string $availabilityLabel;
    public string $typeLabel;
    public string $categoryLabel;
    public ?float $basicPrice;
    public ?string $originalWebsitePrice;
    public ?float $websitePrice;
    public ?float $existingPrice;
    public ?int $numAxles;
    public ?string $frameMaterial;
    public ?string $pullType;
    public ?int $numStalls;
    public ?string $loadType;
    public ?string $roofType;
    public ?string $noseType;
    public ?string $color;
    public ?int $numSleeps;
    public ?int $numAc;
    public ?string $fuelType;
    public bool $isRental;
    public ?string $numSlideouts;
    public ?string $numBatteries;
    public ?string $horsepower;
    public ?string $numPassengers;
    public ?string $conversion;
    public ?string $cabType;
    public ?string $engineSize;
    public ?string $transmission;
    public ?string $driveTrail;
    public ?string $floorplan;
    public ?string $propulsion;
    public array $featureList;
    public string $image;
    public array $images;
    public array $imagesSecondary;
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
            if(str_starts_with($key, 'dealer.')) {
                $dealerData[substr($key, 7)] = $value;
            } else if(str_starts_with($key, 'location.')) {
                $locationData[substr($key, 9)] = $value;
            } else if($key === 'image') {
                $obj->image = self::imageToAbsoluteUrl($value);
            } else if($key === 'images' || $key === 'imagesSecondary') {
                $obj->$key = [];
                foreach ($value as $image) {
                    ($obj->$key)[] = self::imageToAbsoluteUrl($image);
                }
            } else {
                $obj->setTypedProperty($key, $value);
            }
        }

        $obj->dealer = TcEsInventoryDealer::fromData($dealerData);
        $obj->location = TcEsInventoryLocation::fromData($locationData);
        return $obj;
    }
}
