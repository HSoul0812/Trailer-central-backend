<?php

namespace App\Transformers\User;

use App\Traits\CompactHelper;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use App\Models\User\DealerLocation;

class DealerLocationTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'fees',
        'salesTaxItems',
        'salesTaxItemsV1'
    ];

    public function transform(DealerLocation $dealerLocation): array
    {
        return [
            'id' => $dealerLocation->dealer_location_id,
            'identifier' => CompactHelper::shorten($dealerLocation->dealer_location_id), // for backward compatibility
            'name' => $dealerLocation->name,
            'contact' => $dealerLocation->contact,
            'website' => $dealerLocation->website,
            'phone' => $dealerLocation->phone,
            'fax' => $dealerLocation->fax,
            'email' => $dealerLocation->email,
            'address' => $dealerLocation->address,
            'city' => $dealerLocation->city,
            'county' => $dealerLocation->county,
            'region' => $dealerLocation->region,
            'postal' => $dealerLocation->postalcode,
            'country' => $dealerLocation->country,
            'federal_id' => $dealerLocation->federal_id,
            'sales_tax' => $dealerLocation->salesTax,
            'dealer_location_no' => $dealerLocation->dealer_license_no,
            'dealer_location_id' => $dealerLocation->dealer_location_id,
            'sales_tax_item_column_titles' => $dealerLocation->sales_tax_item_column_titles ?? [$dealerLocation::DEFAULT_SALES_TAX_ITEM_COLUMN_TITLES],
            'meta' => [
                'number_of_inventories' => $dealerLocation->inventoryCount(),
                'number_of_references' => $dealerLocation->referenceCount()
            ]
        ];
    }

    public function includeFees(DealerLocation $location): Primitive
    {
        if (empty($location->fees)) {
            return new Primitive([]);
        }

        return $this->primitive($location->fees);
    }

    public function includeSalesTaxItems(DealerLocation $location): Primitive
    {
        if (empty($location->salesTaxItems)) {
            return new Primitive([]);
        }

        return $this->primitive($location->salesTaxItems);
    }

    public function includeSalesTaxItemsV1(DealerLocation $location): Primitive
    {
        if (empty($location->salesTaxItemsV1)) {
            return new Primitive([]);
        }

        return $this->primitive($location->salesTaxItemsV1);
    }
}
