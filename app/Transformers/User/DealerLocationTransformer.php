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
        'salesTaxItemsV1',
        'mileageFees',
        'user',
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
            'postal' => $dealerLocation->postalcode, // for backward compatibility
            'postalcode' => $dealerLocation->postalcode,
            'country' => $dealerLocation->country,
            'federal_id' => $dealerLocation->federal_id,
            'sales_tax' => $dealerLocation->salesTax,
            'dealer_location_no' => $dealerLocation->dealer_license_no,
            'location_id' => $dealerLocation->location_id,
            'dealer_location_id' => $dealerLocation->dealer_location_id,
            'sales_tax_item_column_titles' => (object) $this->getSalesTaxItemColumnTitles($dealerLocation),
            'dealer_id' => $dealerLocation->dealer_id,
            'is_default' => $dealerLocation->is_default,
            'is_default_for_invoice' => $dealerLocation->is_default_for_invoice,
            'latitude' => $dealerLocation->latitude,
            'longitude' => $dealerLocation->longitude,
            'coordinates_updated' => $dealerLocation->coordinates_updated,
            'sms' => $dealerLocation->sms,
            'sms_phone' => $dealerLocation->sms_phone,
            'permanent_phone' => $dealerLocation->permanent_phone,
            'show_on_website_locations' => $dealerLocation->show_on_website_locations,
            'county_issued' => $dealerLocation->county_issued,
            'state_issued' => $dealerLocation->state_issued,
            'dealer_license_no' => $dealerLocation->dealer_license_no,
            'pac_type' => $dealerLocation->pac_type,
            'pac_amount' => $dealerLocation->pac_amount,
            'meta' => [
                'number_of_inventories' => $dealerLocation->inventoryCount(),
                'number_of_references' => $dealerLocation->referenceCount()
            ],
            'google_business_store_code' => $dealerLocation->google_business_store_code
        ];
    }

    public function includeFees(DealerLocation $location): Primitive
    {
        if (empty($location->fees)) {
            return new Primitive([]);
        }

        return $this->primitive($location->fees->keyBy('fee_type'));
    }

    public function includeSalesTaxItems(DealerLocation $location): Primitive
    {
        if (empty($location->salesTaxItems)) {
            return new Primitive([]);
        }

        return $this->primitive($location->salesTaxItems);
    }

    public function includeMileageFees(DealerLocation  $location): Primitive
    {
        if (empty($location->mileageFees)) {
            return new Primitive([]);
        }

        return $this->primitive($location->mileageFees);
    }

    public function includeUser(DealerLocation  $location): Primitive
    {
        if (empty($location->user)) {
            return new Primitive([]);
        }

        return $this->primitive((new UserTransformer())->transform($location->user));
    }

    /**
     * This is for backward compatibility
     *
     * @param DealerLocation $location
     * @return Primitive
     */
    public function includeSalesTaxItemsV1(DealerLocation $location): Primitive
    {
        if (empty($location->salesTaxItemsV1)) {
            return new Primitive([]);
        }

        return $this->primitive($location->salesTaxItemsV1);
    }

    /**
     * @param DealerLocation $dealerLocation
     * @return array
     */
    private function getSalesTaxItemColumnTitles(DealerLocation $dealerLocation): array
    {
        // @note we are not using model mutator to avoid any unexpected change elsewhere
        if (!empty($dealerLocation->sales_tax_item_column_titles)) {
            return is_string($dealerLocation->sales_tax_item_column_titles) ?
                json_decode($dealerLocation->sales_tax_item_column_titles, true) :
                $dealerLocation->sales_tax_item_column_titles;
        }

        return [DealerLocation::DEFAULT_SALES_TAX_ITEM_COLUMN_TITLES];
    }
}
