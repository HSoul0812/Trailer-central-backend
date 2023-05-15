<?php

namespace App\Domains\ViewedDealer\Actions;

use App\Models\Dealer\ViewedDealer;
use Illuminate\Support\Facades\Cache;
use Str;
use Throwable;

class CreateViewedDealerAction
{
    public const REQUIRED_FIELDS = ['name', 'dealer_id', 'inventory_id'];

    public const VIEWED_DEALER_CACHE_SECONDS = 86_400;

    /**
     * Upsert the given dealer_id & name pair using name as unique identifier
     * if name exists, we update dealer_id to the given one, if not we create
     * a new record.
     *
     * @param array<int, array{dealer_id: int, name: string}> $viewedDealers
     *
     * @throws Throwable
     */
    public function execute(array $viewedDealers): array
    {
        $viewedDealers = $this->transformAndValidate($viewedDealers);

        $returnValues = collect([]);

        foreach ($viewedDealers as $viewedDealer) {
            $keys = array_keys($viewedDealer);

            $hasAllRequiredKeys = true;

            // Do not create a record if one of the required field isn't in the request
            foreach (self::REQUIRED_FIELDS as $requiredField) {
                if (!in_array($requiredField, $keys)) {
                    $hasAllRequiredKeys = false;

                    break;
                }
            }

            if (!$hasAllRequiredKeys) {
                continue;
            }

            $nameSlug = Str::of($viewedDealer['name'])->slug();

            $cachedModel = Cache::remember(
                key: "viewed-dealer.$nameSlug",
                ttl: self::VIEWED_DEALER_CACHE_SECONDS,
                callback: function () use ($viewedDealer) {
                    /** @var ViewedDealer $viewedDealer */
                    $model = ViewedDealer::firstOrNew([
                        'name' => $viewedDealer['name'],
                    ]);

                    $model->fill([
                        'dealer_id' => $viewedDealer['dealer_id'],
                        'inventory_id' => $viewedDealer['inventory_id'],
                    ]);

                    try {
                        $model->save();
                    } catch (Throwable) {
                        return null;
                    }

                    return $model->toArray();
                });

            if ($cachedModel !== null) {
                $returnValues->push($cachedModel);
            }
        }

        return $returnValues->toArray();
    }

    /**
     * Transform into a unique names array and then validate that dealer_id
     * are unique across all array.
     */
    private function transformAndValidate(array $viewedDealers): array
    {
        $viewedDealers = $this->removeDuplicateNames($viewedDealers);

        return $this->removeDuplicateDealerIds($viewedDealers);
    }

    /**
     * Remove any duplicate name from the array, for example, if the API consumer
     * send 2 array with the name John, then only the 1st array will be in the final array.
     */
    private function removeDuplicateNames(array $viewedDealers): array
    {
        $noDuplicateNameParams = [];
        $names = [];

        foreach ($viewedDealers as $viewedDealer) {
            if (!array_key_exists('name', $viewedDealer)) {
                continue;
            }

            // Use key for best performance
            if (array_key_exists($viewedDealer['name'], $names)) {
                continue;
            }

            $names[$viewedDealer['name']] = true;

            $noDuplicateNameParams[] = $viewedDealer;
        }

        return $noDuplicateNameParams;
    }

    /**
     * Make sure that we don't have duplicate dealer id in a different name.
     */
    private function removeDuplicateDealerIds(array $viewedDealers): array
    {
        $dealerIds = [];

        foreach ($viewedDealers as $viewedDealer) {
            if (array_key_exists($viewedDealer['dealer_id'], $dealerIds)) {
                continue;
            }

            $dealerIds[$viewedDealer['dealer_id']] = $viewedDealer;
        }

        return array_values($dealerIds);
    }
}
