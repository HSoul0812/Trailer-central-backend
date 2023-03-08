<?php

namespace App\Repositories\User;

use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Arr;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DealerLocationRepository implements DealerLocationRepositoryInterface
{
    use Transaction, SortTrait;

    private $sortOrders = [
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_id` has not been provided
     */
    public function create($params): DealerLocation
    {
        if (!isset($params['dealer_id'])) {
            throw new InvalidArgumentException('"dealer_id" is required');
        }

        $location = new DealerLocation();
        $location->fill($params)->save();

        return $location;
    }

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function delete($params): int
    {
        $location = DealerLocation::find($this->getDealerLocationIdFromParams($params));

        if ($location) {
            return (int)$location->delete();
        }

        return 0;
    }

    /**
     * @param array $params
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function get($params): DealerLocation
    {
        if(isset($params['dealer_id'])) {
            return DealerLocation::withTrashed()->where('dealer_id', $params['dealer_id'])->firstOrFail();
        } else {
            return DealerLocation::withTrashed()->findOrFail($this->getDealerLocationIdFromParams($params));
        }
    }

    public function getDefaultByDealerId(int $dealerId): ?DealerLocation
    {
        return DealerLocation::where(['dealer_id' => $dealerId, 'is_default' => 1])->first();
    }

    /**
     * @param array $params
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = $this->getQueryBuilder($params);

        if (isset($params['search_term'])) {
            $query = $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->with('salesTax')->paginate($params['per_page'])->appends($params);
    }

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Collection<DealerLocation>
     */
    public function findAll(array $params): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getQueryBuilder($params)->with('salesTax')->get();
    }

    public function dealerHasLocationWithId(int $dealerId, int $locationId): bool
    {
        return DealerLocation::where(['dealer_id' => $dealerId, 'dealer_location_id' => $locationId])->exists();
    }

    /**
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function update($params): DealerLocation
    {
        $dealerLocationId = $this->getDealerLocationIdFromParams($params);

        $updateParams = Arr::except($params, ['id']);

        DealerLocation::query()
            ->lockForUpdate()
            ->where('dealer_location_id', $dealerLocationId)
            ->update($updateParams);

        return DealerLocation::find($dealerLocationId);
    }

    public function turnOffDefaultLocationByDealerId(int $dealerId): bool
    {
        return DealerLocation::where('dealer_id', $dealerId)->update(['is_default' => 0]);
    }

    public function turnOffDefaultLocationForInvoicingByDealerId(int $dealerId): bool
    {
        return DealerLocation::where('dealer_id', $dealerId)->update(['is_default_for_invoice' => 0]);
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

        $select = '*';
        if (!empty($params[self::SELECT])) {
            if (is_array($params[self::SELECT])) {
                $select = $params[self::SELECT];
            } elseif (is_string($params[self::SELECT])) {
                $select = explode(',', $params[self::SELECT]);
            }
        }

        $query->select($select);

        // Match Name
        if (isset($params['name'])) {
            $query->where('name', $params['name']);
        }

        // Match Phone
        if (isset($params['phone'])) {
            $query->where('phone', $params['phone']);
        }

        // Match Email
        if (isset($params['email'])) {
            $query->where('email', $params['email']);
        }

        // Match City
        if (isset($params['city'])) {
            $query->where('city', $params['city']);
        }

        // Match State
        if (isset($params['region'])) {
            $query->where('region', $params['region']);
        }

        // Match Zip
        if (isset($params['zip'])) {
            $query->where('postalcode', $params['zip']);
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

    /**
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    private function getDealerLocationIdFromParams(array $params): int
    {
        $id = $params['dealer_location_id'] ?? $params['id'] ?? null;

        if (empty($id)) {
            throw new InvalidArgumentException('"dealer_location_id" is required');
        }

        return $id;
    }

    private function getQueryBuilder(array $params): Builder
    {
        $query = DealerLocation::select('*');

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        } elseif (isset($params['dealer_ids']) && is_array($params['dealer_ids'])) {
            $query = $query->whereIn('dealer_id', $params['dealer_ids']);
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

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query = $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE_IN]) && is_array($params[self::CONDITION_AND_WHERE_IN])) {
            foreach ($params[self::CONDITION_AND_WHERE_IN] as $field => $values) {
                $query = $query->whereIn($field, $values);
            }
        }

        return $query;
    }

    /**
     * @param string $name
     * @param int $dealerId
     * @param int|null $dealerLocationId
     * @return bool true if exists
     */
    public function existByName(string $name, int $dealerId, ?int $dealerLocationId): bool
    {
        $query = DealerLocation::select('*');

        $query->where('dealer_id', '=', $dealerId);

        if ($dealerLocationId) {
            $query->where('dealer_location_id', '!=', $dealerLocationId);
        }

        $query->whereRaw('LOWER(name) = ?', ['name' => Str::lower($name)]);

        return $query->exists();
    }

    /**
     * @param int $dealerLocationId
     * @return string country
     */
    public function getCountryById($dealerLocationId)
    {
        return DealerLocation::find($dealerLocationId)->country;
    }

    /**
     * @return string[][]
     */
    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }
}
