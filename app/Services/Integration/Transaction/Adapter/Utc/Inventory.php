<?php

namespace App\Services\Integration\Transaction\Adapter\Utc;

use App\Models\User\User;
use App\Services\Integration\Transaction\Adapter\UtcAdapter;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class Inventory
 * @package App\Services\Integration\Transaction\Adapter\Utc
 */
class Inventory extends UtcAdapter
{
    protected $_entityType = 'inventory';

    protected $_conversions = array(
        'status' => array(
            'available' => '1', // available
            'sold'      => '2', // sold
            'on order'  => '3', // on order
        ),
        'status_backwards'         => array(
            '1' => 'Available',
            '2' => 'Sold',
            '3' => 'On Order',
            '4' => 'Pending Sale',
        ),
        'pull_type'      => array(
            'BUMPER PULL' => 'bumper', // this is important .. not sure where this is coming from .. guess some new utc schema data?
            'bumper'      => 'bumper',
            'fifth_wheel' => 'fifth_wheel',
            'gooseneck'   => 'gooseneck',
            'pintle'      => 'pintle',
            'tag'         => 'bumper',
            '5th wheel'   => 'fifth_wheel'
        ),
        'nose_type'      => array(
            'round'  => 'round',
            'flat'   => 'flat',
            'v_front' => 'v_front',
        ),
        'roof_type'      => array(
            'round' => 'round',
            'flat'  => 'flat'
        ),
        'brand'          => array(
            'haulmark'       => 'Haulmark',
            'wells cargo'    => 'Wells Cargo',
            'tc trecker'     => 'Wells Cargo',
            'road force'     => 'Wells Cargo',
            'exiss trailers' => 'Exiss',
            'sooner'         => 'Sooner',
            'exiss'          => 'Exiss',

        ),
        'category_label' => array(
            // trailer (type=1)
            'atv'                  => "ATV Trailer",
            'camping_rv'           => 'Camping / RV Trailer',
            'cargo_enclosed'       => 'Enclosed Cargo Trailer',
            'car_racing'           => 'Car / Racing Trailer',
            'dump'                 => 'Dump Trailer',
            'equipment'            => 'Equipment Trailer',
            'flatbed'              => 'Flatbed Trailer',
            'motorcycle'           => 'Motorcycle Trailer',
            'snowmobile'           => 'Snowmobile Trailer',
            'stock_stock-combo'    => 'Stock / Stock Combo Trailer',
            'toy'                  => 'Toy Hauler',
            'tow_dolly'            => 'Tow Dolly',
            'bed_equipment'        => "Truck Bed / Equipment",
            'utility'              => 'Utility Trailer',
            'vending_concession'   => 'Vending / Concession Trailer',
            'watercraft'           => 'Watercraft Trailer',
            'other'                => 'Other Trailer',

            // horse (type=2)
            'horse'                => 'Horse Trailer',

            // rv (type=3)
            'class_a'              => 'Class A RV',
            'class_b'              => 'Class B RV',
            'class_c'              => 'Class C RV',

            // vehicle (type=4)
            'vehicle_atv'          => 'ATV',
            'vehicle_car'          => 'Car',
            'golf_cart'            => 'Golf Cart',
            'vehicle_motorcycle'   => 'Motorcycle',
            'vehicle_truck'        => 'Truck',
            'vehicle_suv'          => 'SUV',
            'sport_side-by-side'   => 'Sport Side-by-Side',
            'utility_side-by-side' => 'Utility Side-by-Side (UTV)',

            // watercraft (type=5)
            'personal_watercraft'  => 'PWC (Personal Watercraft)',
            'canoe-kayak'          => 'Canoe / Kayak',
            'inflatable'           => 'Inflatable',
            'powerboat'            => 'Power Boat',
            'sailboat'             => 'Sailboat',

            // equipment (type=6)
            'equip_tractor'        => 'Tractor',
            'equip_attachment'     => 'Attachment',
            'equip_farm-ranch'     => 'Farm / Ranch',
            'equip_lawn'           => 'Lawn',
        )
    );

    /**
     * This is our overriden convert() method that takes case sensitivity out of the equasion
     *
     * @param $attribute
     * @param $value
     *
     * @return mixed
     */
    public function convert($attribute, $value)
    {
        $attribute = strtolower($attribute);

        if(isset($this->_conversions[$attribute][$value])) {
            return $this->_conversions[$attribute][$value];
        } elseif(isset($this->_conversions[$attribute][strtolower($value)])) {
            return $this->_conversions[$attribute][strtolower($value)];
        } else {
            return $value;
        }
    }

    /**
     * @param array $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function add(array $data): bool
    {
        $now = new \DateTime('now', new \DateTimeZone('America/New_York')); // will use THIS timezone throughout for mySQL NOW()'s
        $lcCategory = strtolower($data['category']);

        // If we do not have a label for this category
        if($this->convert('category_label', $data['category']) == $data['category']) {
            return false;
        }

        $dealerID = $this->getEntityFromReference('dealer', $data['dealer_identifier']);
        $locationID = $this->getEntityFromReference('dealer_location', $data['location_identifier']);

        switch($lcCategory) {
            case 'coaches':
                $entityType = 3;
                break;
            case 'horse':
            case 'horse trailer':
                $entityType = 2;
                break;
            default:
                $entityType = 1;
        }

        /** @var User $dealer */
        $dealer = $this->userRepository->get(['dealer_id' => $dealerID]);

        $title = $data['year'] . ' ' . $this->convert('brand', $data['brand']) . ' ' . $data['model'] . ' ' .
            $this->convert('category_label', $data['category']);

        $inventoryParams =[
            'entity_type_id' => $entityType,
            'dealer_id' => $dealerID,
            'dealer_location_id' => $locationID,
            'active' => 1,
            'title' => $title,
            'manufacturer' => $this->convert('brand', $data['brand']),
            'price' => null,
            'model' => $data['model'],
            'notes' => '-- Auto imported via UTC: ' . $now->format("M j, Y g:i a T") . ' --',
            'status' => $this->convert('status', strtolower($data['status'])),
            'category' => $data['category'],
            'vin' => $data['vin'],
            'description' => $dealer->use_description_in_feed ? $dealer->default_description : $data['description'],
            'year' => $data['year'],
            'condition' => 'new',
            'length' => $data['length'],
            'gvwr' => $data['gvwr']
        ];

        switch($dealer->import_config) {
            case "model+vin":
                $inventoryParams['stock'] = $data['model'] . '-' . $data['vin'];
                break;
            case "last 4 of vin":
                $inventoryParams['stock'] = substr($data['vin'], -4);
                break;
            default:
            case "model+last 7 of vin (default)":
                $inventoryParams['stock'] = $data['model'] . '-' . substr($data['vin'], -7);
                break;
        }

        if(isset($data['cost'])) {
            $inventoryParams['total_of_cost'] = round($data['cost']);
        }

        $autoImportHide = $dealer->auto_import_hide;
        if($autoImportHide == 1) {
            $inventoryParams['show_on_website'] = 0;
        } elseif($autoImportHide == 2) {
            $inventoryParams['is_archived'] = 1;
            $inventoryParams['archived_at'] = $now->format('Y-m-d H:i:s');
        }

        if(isset($data['msrp'])) {
            $inventoryParams['msrp'] = $data['msrp'];
        }

        if(isset($data['width'])) {
            $inventoryParams['width'] = $data['width'];
        }

        if(isset($data['height'])) {
            $inventoryParams['height'] = $data['height'];
        }

        if(isset($data['axle_capacity'])) {
            $inventoryParams['axle_capacity'] = $data['axle_capacity'];
        }

        // If the inventory is sold, go ahead and archive it
        if($inventoryParams['status'] == 2) {
            $inventoryParams['is_archived'] = 1;
            $inventoryParams['archived_at'] = $now->format('Y-m-d H:i:s');
        }

        $attributes = array();

        if(!empty($data['axles'])) {
            $attributes['axles'] = $data['axles'];
        }
        if(!empty($data['color'])) {
            $attributes['color'] = strtolower($this->convert('color', $data['color']));
        }
        if(!empty($data['hitch_type'])) {
            $attributes['pull_type'] = $this->convert('pull_type', $data['hitch_type']);
        }
        if(!empty($data['roof_type'])) {
            $attributes['roof_type'] = $this->convert('roof_type', $data['roof_type']);
        }
        if(!empty($data['nose_type'])) {
            $attributes['nose_type'] = $this->convert('nose_type', $data['nose_type']);
        }
        if(!empty($data['stalls'])) {
            $attributes['stalls'] = $data['stalls'];
        }
        if(!empty($data['enclosed'])) {
            $attributes['enclosed'] = $data['enclosed'];
        }
        if(!empty($data['living_quarters'])) {
            $attributes['living_quarters'] = $data['living_quarters'];
        }
        if(!empty($data['construction'])) {
            $attributes['construction'] = $data['construction'];
        }

        $inventoryParams['attributes'] = $this->getInventoryAttributes($entityType, $attributes);

        $inventoryParams['new_images'] = [];

        foreach ($data['images'] ?? [] as $url) {
            $inventoryParams['new_images'][] = ['url' => $url];
        }

        $inventory = $this->inventoryService->create($inventoryParams);

        $this->saveReference($inventory->inventory_id, $data['vin']);

        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws BindingResolutionException
     */
    public function update(array $data): bool
    {
        $now = new \DateTime('now', new \DateTimeZone('America/New_York'));

        if(isset($data['status'])) {
            $invId = $this->getEntityFromReference('inventory', $data['vin']);

            if(!empty($invId)) {
                $newStatus = $this->convert('status', strtolower($data['status']));
                /** @var \App\Models\Inventory\Inventory $inventory */
                $inventory = $this->inventoryRepository->get(['id' => $invId]);
                $currentStatus = $inventory->status;

                if(!empty($currentStatus) && $currentStatus != $newStatus) { // has an existing status
                    $additionalNotes = "\r\n\r\n -- Updated to '" . $this->convert('status_backwards', $newStatus)
                        . "' status via UTC: " . $now->format("M j, Y g:i a T");

                    $inventoryParams = [
                        'inventory_id' => $invId,
                        'status' => $newStatus,
                        'notes' => $inventory->notes ? $inventory->notes . $additionalNotes : $additionalNotes
                    ];

                    if ($newStatus == 2) {
                        $inventoryParams['archived_at'] = $now->format('Y-m-d H:i:s');
                        $inventoryParams['is_archived'] = 1;
                    }

                    $this->inventoryService->update($inventoryParams);
                }
            }
            return true;
        } else {
            return false;
        }
    }
}

