<?php

namespace App\Repositories\User;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocationQuoteFee;
use App\Models\User\DealerLocationSalesTax;
use App\Models\User\DealerLocationSalesTaxItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use DomainException;
use DB;

class DealerLocationRepository implements DealerLocationRepositoryInterface
{
    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_id` has not been provided
     * @throws InvalidArgumentException when `sales_tax_items` is not an array
     * @throws InvalidArgumentException when `fees` is not an array
     */
    public function create($params): DealerLocation
    {
        if (!isset($params['dealer_id'])) {
            throw new InvalidArgumentException('"dealer_id" is required');
        }

        return DB::transaction(function () use ($params): DealerLocation {

            if (!empty($params['is_default_for_invoice'])) {
                // remove any default location for invoice if exists
                DealerLocation::where('dealer_id', $params['dealer_id'])->update(['is_default_for_invoice' => 0]);
            }
            $salesTaxItemColumnTitles = $this->encodeTaxColumnTitles($params['sales_tax_item_column_titles'] ?? []);

            $location = new DealerLocation();
            $location->fill($params + ['sales_tax_item_column_titles' => $salesTaxItemColumnTitles])->save();

            $locationRelDefinition = ['dealer_location_id' => $location->dealer_location_id];

            $taxSettings = new DealerLocationSalesTax();
            $taxSettings->fill($params + $locationRelDefinition)->save();

            if (!empty($params['sales_tax_items'])) {
                if (!is_array($params['sales_tax_items'])) {
                    throw new InvalidArgumentException('"sales_tax_items" must be an array');
                }

                foreach ($params['sales_tax_items'] as $item) {
                    $taxItem = new DealerLocationSalesTaxItem();
                    $taxItem->fill($item + $locationRelDefinition)->save();
                }
            }

            if (!empty($params['fees'])) {
                if (!is_array($params['fees'])) {
                    throw new InvalidArgumentException('"fees" must be an array');
                }

                foreach ($params['fees'] as $item) {
                    $fee = new DealerLocationQuoteFee();
                    $fee->fill($item + $locationRelDefinition)->save();
                }
            }

            return $location;
        });
    }

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function delete($params): int
    {
        if (!isset($params['dealer_id'])) {
            throw new InvalidArgumentException('"dealer_id" is required');
        }

        $dealerId = $params['dealer_id'];
        $id = $this->getDealerLocationIdFromParams($params);
        $moveTo = $params['move_references_to_location_id'] ?? null;

        $location = DealerLocation::findOrFail($id);

        return DB::transaction(function () use ($location, $dealerId, $moveTo): int {
            if ($location->hasRelatedRecords()) {
                if(!$moveTo){
                    /** @var DealerLocation $default */
                    $default = DealerLocation::where(['dealer_id' => $dealerId, 'is_default' => 1])
                        ->where('dealer_location_id', '!=', $location->dealer_location_id)
                        ->first();

                    // if there is not a provided `move_references_to_location_id`, then it'll assign the default dealer location,
                    // otherwise the first location
                    if ($default) {
                        $moveTo = $default->dealer_location_id;
                    } else {
                        /** @var DealerLocation $first */
                        $first = DealerLocation::where(['dealer_id' => $dealerId])
                            ->where('dealer_location_id', '!=', $location->dealer_location_id)
                            ->first();

                        if ($first) {
                            $moveTo = $first->dealer_location_id;
                        } else {
                            throw new DomainException("There isn't a possible location to move those related ".
                                "records of DealerLocation{dealer_location_id={$location->dealer_location_id}}");
                        }
                    }
                }

                Inventory::where('dealer_location_id', $location->dealer_location_id)->update([
                    'dealer_location_id' => $moveTo
                ]);

                ApiEntityReference::where([
                    'entity_id' => $location->dealer_location_id,
                    'entity_type' => ApiEntityReference::TYPE_LOCATION
                ])->update([
                    'entity_id' => $moveTo
                ]);
            }

            return (int)$location->delete();
        });
    }

    /**
     * @param array $params
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function get($params): DealerLocation
    {
        return DealerLocation::findOrFail($this->getDealerLocationIdFromParams($params));
    }

    /**
     * @param array $params
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = DealerLocation::select('*');

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['search_term'])) {
            $search_term = '%' . $params['search_term'] . '%';
            $query = $query->where(function (Builder $subQuery) use ($search_term): void {
                $subQuery->where('name', 'LIKE', $search_term)
                    ->orWhere('contact', 'LIKE', $search_term)
                    ->orWhere('phone', 'LIKE', $search_term)
                    ->orWhere('website', 'LIKE', $search_term)
                    ->orWhere('email', 'LIKE', $search_term)
                    ->orWhere('city', 'LIKE', $search_term)
                    ->orWhere('county', 'LIKE', $search_term)
                    ->orWhere('region', 'LIKE', $search_term);
            });
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->with('salesTax')->paginate($params['per_page'])->appends($params);
    }

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_id` has not been provided
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     * @throws InvalidArgumentException when `sales_tax_items` is not an array
     * @throws InvalidArgumentException when `fees` is not an array
     * @throws ModelNotFoundException
     */
    public function update($params): bool
    {
        if (!isset($params['dealer_id'])) {
            throw new InvalidArgumentException('"dealer_id" is required');
        }

        return DB::transaction(function () use ($params): bool {

            if (!empty($params['is_default_for_invoice'])) {
                // remove any default location for invoice if exists
                DealerLocation::where('dealer_id', $params['dealer_id'])->update(['is_default_for_invoice' => 0]);
            }

            $id = $this->getDealerLocationIdFromParams($params);
            $locationRelDefinition = ['dealer_location_id' => $id];

            $salesTaxItemColumnTitles = $this->encodeTaxColumnTitles($params['sales_tax_item_column_titles'] ?? []);

            $location = DealerLocation::findOrFail($id);
            $location->fill($params + ['sales_tax_item_column_titles' => $salesTaxItemColumnTitles])->save();

            DealerLocationSalesTax::updateOrCreate($params);

            if (!empty($params['sales_tax_items'])) {
                if (!is_array($params['sales_tax_items'])) {
                    throw new InvalidArgumentException('"sales_tax_items" must be an array');
                }

                DealerLocationSalesTaxItem::where('dealer_location_id', $id)->delete();

                foreach ($params['sales_tax_items'] as $item) {
                    $taxItem = new DealerLocationSalesTaxItem();
                    $taxItem->fill($item + $locationRelDefinition)->save();
                }
            }

            if (!empty($params['fees'])) {
                if (!is_array($params['fees'])) {
                    throw new InvalidArgumentException('"fees" must be an array');
                }

                DealerLocationQuoteFee::where('dealer_location_id', $id)->delete();

                foreach ($params['fees'] as $item) {
                    $fee = new DealerLocationQuoteFee();
                    $fee->fill($item + $locationRelDefinition)->save();
                }
            }

            return true;
        });
    }

    /**
     * Find Dealer Location By Various Options
     *
     * @param array $params
     * @return Collection<DealerLocation>
     */
    public function find($params)
    {
        // Get First Dealer Location SMS Numbers
        $query = DealerLocation::where('dealer_id', $params['dealer_id']);

        // Match Name
        if(isset($params['name'])) {
            $query->where('name', $params['name']);
        }

        // Match Phone
        if(isset($params['phone'])) {
            $query->where('phone', $params['phone']);
        }

        // Match Email
        if(isset($params['email'])) {
            $query->where('email', $params['email']);
        }

        // Match City
        if(isset($params['city'])) {
            $query->where('city', $params['city']);
        }

        // Match State
        if(isset($params['region'])) {
            $query->where('region', $params['region']);
        }

        // Match Zip
        if(isset($params['zip'])) {
            $query->where('zip', $params['zip']);
        }

        // Return Locations Found
        return $query->get();
    }


    /**
     * Get First Dealer SMS Number
     *
     * @param int $dealerId
     * @return type
     */
    public function findDealerSmsNumber($dealerId)
    {
        // Get First Dealer Location SMS Numbers
        return DealerLocation::where('dealer_id', $dealerId)
                                ->whereNotNull('sms_phone')
                                ->pluck('sms_phone')
                                ->first();
    }

    /**
     * Get All Dealer SMS Numbers
     *
     * @param int $dealerId
     * @return type
     */
    public function findAllDealerSmsNumbers($dealerId)
    {
        // Get All Dealer Location SMS Numbers
        return DealerLocation::where('dealer_id', $dealerId)
                                ->whereNotNull('sms_phone')
                                ->get();
    }

    /**
     * Get Dealer Number for Location or Default
     *
     * @param int $dealerId
     * @param int $locationId
     * @return type
     */
    public function findDealerNumber($dealerId, $locationId) {
        // Get Dealer Location
        $location = DealerLocation::find($locationId);
        if(!empty($location->sms_phone)) {
            return $location->sms_phone;
        }

        // Get Numbers By Dealer ID
        if(!empty($location->dealer_id)) {
            $numbers = $this->findAllDealerSmsNumbers($location->dealer_id);
        } else {
            $numbers = $this->findAllDealerSmsNumbers($dealerId);
        }

        // Loop Numbers
        $phoneNumber = '';
        if(!empty($numbers)) {
            // Get First Valid Number!
            foreach($numbers as $number) {
                if(!empty($number->sms_phone)) {
                    $phoneNumber = $number->sms_phone;
                    break;
                }
            }
        }

        // Return Phone Number
        return $phoneNumber;
    }

    private function getDealerLocationIdFromParams(array $params): int
    {
        $id = $params['dealer_location_id'] ?? $params['id'] ?? null;

        if (empty($id)) {
            throw new InvalidArgumentException('"dealer_location_id" is required');
        }

        return $id;
    }

    /**
     * Forces a value to be an array, if it is a json it will be encoded as array
     *
     * @param array|string $titles
     * @return array
     */
    private function encodeTaxColumnTitles($titles): array
    {
        $salesTaxItemColumnTitles = [];

        if (!empty($titles)) {
            $salesTaxItemColumnTitles = $titles;

            if (is_string($titles)) {
                $salesTaxItemColumnTitles = json_decode($titles, true);
            }
        }

        return $salesTaxItemColumnTitles;
    }

    private function locationsHasRelatedRecords()
    {
        $numberOfInventories = Inventory::where('dealer_location_id', $id)->count();
        $numberOfReferences = ApiEntityReference::where([
            'entity_id' => $id,
            'entity_type' => ApiEntityReference::TYPE_LOCATION
        ])->count();
    }
}
