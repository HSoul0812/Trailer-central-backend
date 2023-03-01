<?php

namespace App\Domains\ViewedDealer\Actions;

use App\Domains\ViewedDealer\Exceptions\DealerIdExistsException;
use App\Domains\ViewedDealer\Exceptions\DuplicateDealerIdException;
use App\Models\Dealer\ViewedDealer;
use Arr;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Str;
use Throwable;

class CreateViewedDealerAction
{
    /**
     * A regular expression to capture the dealer id from the error message
     */
    const REGEX_CAPTURE_DEALER_ID = '/(Key \(dealer_id\)=\((?<dealerId>\d*)\))/m';

    /**
     * Upsert the given dealer_id & name pair using name as unique identifier
     * if name exists, we update dealer_id to the given one, if not we create
     * a new record
     *
     * @param array<int, array{dealer_id: int, name: string}> $viewedDealers
     * @return Collection
     * @throws DuplicateDealerIdException|DealerIdExistsException
     * @throws Throwable
     */
    public function execute(array $viewedDealers): Collection
    {
        $viewedDealers = $this->transformAndValidate($viewedDealers);

        DB::transaction(function () use ($viewedDealers) {
            foreach ($viewedDealers as $viewedDealer) {
                try {
                    /** @var ViewedDealer $viewedDealer */
                    $model = ViewedDealer::firstOrNew([
                        'name' => $viewedDealer['name'],
                    ]);

                    $model->fill([
                        'dealer_id' => $viewedDealer['dealer_id'],
                        'inventory_id' => $viewedDealer['inventory_id'],
                    ]);

                    $model->save();
                } catch (QueryException $exception) {
                    throw $this->captureQueryException($exception);
                }
            }
        });

        return ViewedDealer::query()
            ->whereIn('name', Arr::pluck($viewedDealers, 'name'))
            ->get();
    }

    /**
     * Transform into a unique names array and then validate that dealer_id
     * are unique across all array
     *
     * @param array $viewedDealers
     * @return array
     * @throws DuplicateDealerIdException
     */
    private function transformAndValidate(array $viewedDealers): array
    {
        $viewedDealers = $this->removeDuplicateNames($viewedDealers);

        // $this->validateUniqueDealerIds($viewedDealers);

        return $viewedDealers;
    }

    /**
     * Remove any duplicate name from the array, for example, if the API consumer
     * send 2 array with the name John, then only the 1st array will be in the final array
     *
     * @param array $viewedDealers
     * @return array
     */
    private function removeDuplicateNames(array $viewedDealers): array
    {
        $noDuplicateNameParams = [];
        $names = [];

        foreach ($viewedDealers as $viewedDealer) {
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
     * Make sure that we don't have duplicate dealer id in a different name
     *
     * @param array $viewedDealers
     * @return void
     * @throws DuplicateDealerIdException
     */
    private function validateUniqueDealerIds(array $viewedDealers): void
    {
        $dealerIds = [];

        foreach ($viewedDealers as $viewedDealer) {
            // We'll throw an exception if this viewedDealer array has the
            // dealer_id that we already found in one of the previous
            // viewedDealer array
            if (array_key_exists($viewedDealer['dealer_id'], $dealerIds)) {
                throw DuplicateDealerIdException::make(
                    name1: $dealerIds[$viewedDealer['dealer_id']],
                    name2: $viewedDealer['name'],
                    dealerId: $viewedDealer['dealer_id'],
                );
            }

            $dealerIds[$viewedDealer['dealer_id']] = $viewedDealer['name'];
        }
    }

    /**
     * Transform the query exception if it's the duplicate dealer id error
     * we want to do this, so we only have unique dealer id at one time in the viewed_dealers table
     *
     * @param QueryException $exception
     * @return QueryException|DealerIdExistsException
     */
    private function captureQueryException(QueryException $exception): QueryException|DealerIdExistsException
    {
        $message = Str::of($exception->getMessage());

        $isDuplicateDealerIdError = $message->contains('duplicate key value violates unique constraint "viewed_dealers_dealer_id_unique"');

        // We don't transform it if it's not the duplicate dealer id error
        if (!$isDuplicateDealerIdError) {
            return $exception;
        }

        preg_match(self::REGEX_CAPTURE_DEALER_ID, $message, $matches, PREG_OFFSET_CAPTURE);

        $dealerId = Arr::get($matches, 'dealerId.0');

        // We won't return custom error message if we can't find the dealer id from the error message
        if ($dealerId === null) {
            return $exception;
        }

        return DealerIdExistsException::make($dealerId);
    }
}
