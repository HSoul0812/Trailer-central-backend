<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\Marketing\Craigslist\InvalidDealerIdException;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\Craigslist\Transaction;
use Illuminate\Support\Collection;

/**
 * Class BillingRepository
 * @package App\Repositories\Marketing\Craigslist
 */
class BillingRepository implements BillingRepositoryInterface
{

    /**
     * Get all Billing transactions within a given range
     *
     * @param $params
     *
     * @throws InvalidDealerIdException
     *
     * @return Collection
     */
    public function calendar($params): Collection
    {
        // Dealer id is always required
        if (empty($params['dealer_id'])) {
            throw new InvalidDealerIdException();
        }

        // Start the query
        $query = Transaction::query();

        // Get the needed fields
        $query->select(
            Transaction::getTableName() . '.clapp_txn_id',
            Transaction::getTableName() . '.session_id',
            Transaction::getTableName() . '.queue_id',
            Transaction::getTableName() . '.inventory_id',
            Transaction::getTableName() . '.amount',
            Transaction::getTableName() . '.balance',
            Transaction::getTableName() . '.type',
            Queue::getTableName() . '.time',
            Session::getTableName() . '.session_scheduled',
            Session::getTableName() . '.session_started'
        );

        // Joins
        $query->leftJoin(Session::getTableName(), function ($join) {
            $join->on(Transaction::getTableName() . '.session_id', '=', Session::getTableName() . '.session_id');
        });

        $query->leftJoin(Queue::getTableName(), function ($join) {
            $join->on(Session::getTableName() . '.session_id', '=', Queue::getTableName() . '.session_id');
            $join->on(Session::getTableName() . '.session_dealer_id', '=', Queue::getTableName() . '.dealer_id');
            $join->on(Session::getTableName() . '.session_profile_id', '=', Queue::getTableName() . '.profile_id');
        });

        // Conditions
        $query->where(Transaction::getTableName() . '.dealer_id', '=', $params['dealer_id']);
        $query->where(function ($qb) use ($params) {
            $qb->where(Queue::getTableName() . '.profile_id', '=', $params['profile_id']);
            $qb->orWhere(Session::getTableName() . '.session_profile_id', '=', $params['profile_id']);
        });
        $query->where(Transaction::getTableName() . '.type', '=', 'post');
        $query->where(Transaction::getTableName() . '.amount', '<>', '0.00');
        $query->whereNotNull(Session::getTableName() . '.session_scheduled');


        // Limit within a certain range of dates
        if (isset($params['start'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '>=', $params['start']);
        }
        if (isset($params['end'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '<=', $params['end']);
        }

        // Group by session
        $query->groupBy(Transaction::getTableName() . '.clapp_txn_id');

        // Also order chronologically
        $query->orderBy(Queue::getTableName() . '.time');

        return $query->get();
    }

    public function create($params)
    {
        // TODO: Implement create() method.
    }

    public function update($params)
    {
        // TODO: Implement update() method.
    }

    public function get($params)
    {
        // TODO: Implement get() method.
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($params)
    {
        // TODO: Implement getAll() method.
    }
}
