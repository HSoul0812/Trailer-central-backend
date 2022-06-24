<?php

namespace App\Domains\QuickBooks\Actions;

use App\Models\CRM\User\Customer;
use App\Models\CRM\User\Employee;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use Illuminate\Support\Collection;

class GetQuickBooksDuplicatedDisplayNamesAction
{
    /** @var Collection */
    private $customers;

    /** @var Collection */
    private $employees;

    /** @var Collection */
    private $vendors;

    /**
     * Get the stat of duplicated display names
     *
     * @param int $dealerId
     * @return Collection
     */
    public function execute(int $dealerId): Collection
    {
        $dealer = User::findOrFail($dealerId);

        $this->populateGroupedDisplayNames($dealer->dealer_id);

        $displayNames = $this->customers->keys()
            ->merge($this->employees->keys())
            ->merge($this->vendors->keys());

        $duplicatedDisplayNames = collect(array_count_values($displayNames->toArray()))->filter(function (int $count) {
            return $count > 1;
        });

        return $this->getStatsFromDuplicatedDisplayNames($duplicatedDisplayNames);
    }

    /**
     * Populate the important data for the action
     *
     * @param int $dealerId
     * @return void
     */
    public function populateGroupedDisplayNames(int $dealerId): void
    {
        $this->customers = Customer::query()
            ->where('dealer_id', $dealerId)
            ->get(['id', 'dealer_id', 'display_name'])
            ->groupBy('display_name');

        $this->employees = Employee::query()
            ->where('dealer_id', $dealerId)
            ->get(['id', 'dealer_id', 'display_name'])
            ->groupBy('display_name');

        $this->vendors = Vendor::query()
            ->where('dealer_id', $dealerId)
            ->get(['id', 'dealer_id', 'name'])
            ->groupBy('name');
    }

    /**
     * Get the stats from the given duplicate display names array
     *
     * @param Collection $duplicatedDisplayNames
     * @return Collection
     */
    private function getStatsFromDuplicatedDisplayNames(Collection $duplicatedDisplayNames): Collection
    {
        return $duplicatedDisplayNames
            ->map(function (int $dupTypeCount, string $displayName) {
                $customersList = $this->customers->get($displayName, collect([]));
                $employeesList = $this->employees->get($displayName, collect([]));
                $vendorsList = $this->vendors->get($displayName, collect());

                return collect([
                    'display_name' => $displayName,
                    'customers' => $customersList->pluck('id'),
                    'employees' => $employeesList->pluck('id'),
                    'vendors' => $vendorsList->pluck('id'),
                    'duplicated_count' => $customersList->count() + $employeesList->count() + $vendorsList->count(),
                ]);
            })
            ->sortByDesc('duplicated_count')
            ->values();
    }
}