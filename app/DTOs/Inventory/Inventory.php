<?php
namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;

class Inventory implements Arrayable {
    use \App\DTOs\Arrayable;

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
    public string $year;
    public string $manufacturer;
    public string $model;
    public string $description;
    public string $status;
    public string $category;
    public bool $useWebsitePrice;
    public string $condition;
    public string $length;
    public string $width;
    public string $height;
    public bool $showOnKsl;
    public bool $showOnRacingjunk;
    public bool $showOnWebsite;
    public InventoryDealer $dealer;
    public InventoryLocation $location;
    public string $widthInches;
    public string $heightInches;
    public string $lengthInches;
    public string $widthDisplayMode;
    public string $heightDisplayMode;
    public string $lengthDisplayMode;
    public array $keywords;
    public string $availability;
    public string $availabilityLabel;
    public string $typeLabel;
    public string $categoryLabel;
    public ?string $basicPrice;
    public ?string $originalWebsitePrice;
    public ?string $websitePrice;
    public ?string $existingPrice;
    public ?string $numAxles;
    public ?string $frameMaterial;
    public ?string $pullType;
    public ?string $numStalls;
    public ?string $loadType;
    public ?string $roofType;
    public ?string $noseType;
    public ?string $color;
    public ?string $numSleeps;
    public ?string $numAc;
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

    public static function fromData(array $data):self {
        $obj = new self();

        $dealerData = [];
        $locationData = [];
        foreach($data as $key => $value) {
            if(str_starts_with($key, 'dealer.')) {
                $dealerData[substr($key, 7)] = $value;
            } else if(str_starts_with($key, 'location.')) {
                $locationData[substr($key, 9)] = $value;
            } else {
                $obj->$key = $value;
            }
        }

        $obj->dealer = InventoryDealer::fromData($dealerData);
        $obj->location = InventoryLocation::fromData($locationData);
        return $obj;
    }
}
